<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\BacklinkImage;
use MongoDB\Client;
use App\Models\Taxonomy;
use App\Models\Catalog;

class Multimedia extends Model
{
    /**
     * The Multimedia record array, for an individual record.
     *
     * @var array $record
     */
    protected $record;

    /**
     * The Multimedia record array, for ALL Multimedia.
     *
     * @var array $records
     */
    protected $records;

    /**
     * The count of all of the Multimedia records.
     *
     * @var int $count
     */
    protected $count;

    /**
     * The associated taxonomy record
     *
     * @var array $taxonomy
     */
    protected $taxonomy;

    /**
     * The associated catalog record
     *
     * @var array $catalog
     */
    protected $catalog;

    /**
     * Retrieves the individual Multimedia record.
     *
     * @param int $irn
     *   The IRN of the Multimedia record to return.
     *
     * @return array
     *   Returns an array of the Multimedia record.
     */
    public function getRecord($irn): array
    {
        // Retrieve MongoDB document
        $mongo = new Client(env('MONGO_COLLECTIONS_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $emultimedia = $mongo->collections->emultimedia;
        $document = $emultimedia->findOne(['irn' => $irn]);
        $record = $document;

        // Get taxonomy record info
        $taxonomy = new Taxonomy();
        $record['taxonomy_irn'] = $taxonomy->getTaxonomyIRN($record);
        $this->taxonomy = $taxonomy->getRecord($record['taxonomy_irn']);

        // Get catalog record info
        $catalog = new Catalog();
        $this->catalog = $catalog->getRecordFromMultimediaIRN($record['irn']);
        $record['collection_record_url'] = "";
        $record['catirn'] = "";
        $record['guid'] = "";

        if (!empty($this->catalog)) {
            $record['collection_record_url'] = "/catalogue/" . $this->catalog['irn'];
            $record['catirn'] = $this->catalog['irn'];
            $record['guid'] = $this->catalog['DarGlobalUniqueIdentifier'];
        } else {
            // If there is no reverse-attached catalog record, then there
            // should be a related multimedia record that includes a link
            // to boldsystems.org.
            // 
            // Query multimedia using the IRN in the RelRelatedMediaRef field.
            //
            // Each multimedia detail page should have a link to view a collection record.
            $relatedMediaDoc = $emultimedia->findOne(['irn' => $record['RelRelatedMediaRef']]);
            $record['collection_record_url'] = $relatedMediaDoc['MulIdentifier'] ?? "";
        }

        $record['species_name'] = self::fixSpeciesTitle($record);
        $record['image_url'] = $record['AudAccessURI'];
        $record['genus_species'] = $this->taxonomy['ClaGenus'] . " " . $this->taxonomy['ClaSpecies'];
        $record['author'] = $this->taxonomy['AutAuthorString'];
        $record['rights'] = $this->getCopyright($record);
        $record['bold_url'] = $this->getBOLD($record);
        $record['world_spider_catalog_url'] = $this->getWSCLink($this->taxonomy);
        $record['notes'] = $record['NteText0'] ?? "";
        $record['subsets'] = $this->checkSubsets($record['taxonomy_irn']);

        // Set the individual Multimedia record.
        $this->record = $record;

        return $this->record;
    }

    /**
     * Retrieves ALL Multimedia records for the homepage.
     *
     * @return array
     *   Returns an array of all of the Multimedia records.
     */
    public function getHomepageRecords(): array
    {
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $searchCollection = $mongo->linepig->search;
        $cursor = $searchCollection->find([], [
            'sort' => ['genus' => 1, 'species' => 1]
        ]);

        foreach ($cursor as $record) {
            $this->records[] = $record;
        }

        return $this->records;
    }

    /**
     * Retrieves Multimedia records for a type of subset.
     *
     * @param string $type
     *   The subset type we're querying for.
     *
     * @param int $taxonomyIRN
     *   The Taxonomy IRN for the Multimedia records.
     *
     * @return array
     *   Returns an array of records for the subset.
     */
    public function getSubset($type, $taxonomyIRN): array
    {
        $mongo = new Client(env('MONGO_COLLECTIONS_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $emultimedia = $mongo->collections->emultimedia;
        $cursor = $emultimedia->find(
            [
                'MulOtherNumber' => $taxonomyIRN,
                'MulMimeType' => 'image'
            ]
        );
        $records = [];

        foreach ($cursor as $record) {
            if ($type === "all") {
                $records[] = $record;
            } else {
                foreach ($record['DetSubject'] as $subject) {
                    if ($subject == $type) {
                        $records[] = $record;
                    }
                }
            }
        }

        if (empty($records)) {
            abort(404);
        }

        // Additional processing for each record.
        foreach ($records as $key => $value) {
            $records[$key]['thumbnail_url'] = self::fixThumbnailURL($value['AudAccessURI']);
            $records[$key]['species_name'] = self::fixSpeciesTitle($value);
        }

        return $records;
    }

    /**
     * Retrieves the count of all records for the homepage.
     *
     * @return int
     *   An integer of the count.
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Processes and fixes the Multimedia record title for our purposes.
     *
     * @param array $record
     *   Multimedia record array
     *
     * @return string
     *  Returns a string of the new title
     */
    public static function fixSpeciesTitle($record): string
    {
        $title = $record['MulTitle'];
        $newTitle = str_replace(" female epigynum", "", $title);
        $newTitle = str_replace(" male epigynum", "", $newTitle);

        return $newTitle;
    }

    /**
     * Alters the thumbnail Multimedia URL so we have a proper URL reference to the file
     * on the Multimedia server.
     *
     * @param string $accessURI
     *   Multimedia accessURI
     *
     * @return string
     *   Returns string with the URL to the thumbnail image
     */
    public static function fixThumbnailURL($accessURI): string
    {
        if (empty($accessURI)) {
            return "";
        }

        $fileExtension = substr($accessURI, -4);
        $fileWithoutExtension = str_replace($fileExtension, "", $accessURI);
        $thumbWithExtension = ".thumb" . $fileExtension;
        $thumbURL = $fileWithoutExtension . $thumbWithExtension;

        return $thumbURL;
    }

    /**
     * Sets up the copyright info and link
     *
     * @param array $record
     *   The multimedia record data
     *
     * @return string
     */
    public function getCopyright($record): string
    {
        $rights = $record['RightsSummaryDataLocal'];

        if (!Str::contains($rights, "CC BY-NC")) {
            return $rights;
        }

        $copyrightWithLink = Str::replace(
            "CC BY-NC",
            '<a href="https://creativecommons.org/licenses/by-nc/2.0/" target="_blank">CC BY-NC</a> (Attribution-NonCommercial) ',
            $rights
        );

        return $copyrightWithLink;
    }

    /**
     * Determines if we have a BOLD link to add to the page and returns a
     * string of the URL if we have a URL for BOLD.
     *
     * @param array $record
     *   The Multimedia record.
     *
     * @return string
     *   The BOLD URL.
     */
    public function getBOLD($record): string
    {
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $boldCollection = $mongo->linepig->bold;
        $document = $boldCollection->findOne(['genus_species' => $record['genus_species']]);

        if (is_null($document)) {
            return "";
        }

        $boldGS = str_replace(" ", "+", $document['genus_species']);
        $url = "http://www.boldsystems.org/index.php/TaxBrowser_TaxonPage?taxon=" . $boldGS;

        return $url;
    }

    /**
     * Retrieves the World Spider Catalog URL for Multimedia page.
     *
     * @param array $taxonomy
     *   The Taxonomy record.
     *
     * @return string
     *   Returns a string of HTML containing the URL.
     */
    public function getWSCLink($taxonomy): string
    {
        if (empty($taxonomy)) {
            return "";
        }

        $genus = $taxonomy['ClaGenus'];
        $species = $taxonomy['ClaSpecies'];
        $url = "http://www.wsc.nmbe.ch/search?sFamily=&fMt=begin&sGenus=" . 
                    $genus .
                    "&gMt=exact&sSpecies=" .
                    $species .
                    "&sMt=exact&multiPurpose=slsid&mMt=begin&searchSpec=s";

        return $url;
    }

    /**
     * Checks each Multimedia detail page's subset categories to ensure
     * we have links for a category before we display them on the detail page.
     *
     * @param int $taxonomyIRN
     *   The IRN of the Taxonomy record.
     *
     * @return array
     *   Returns an array of subset items checked values (true|false)
     */
    public function checkSubsets($taxonomyIRN): array
    {
        $subsets = config('emuconfig.subsets_to_check');
        $mongo = new Client(env('MONGO_COLLECTIONS_CONN'), [], config('emuconfig.mongodb_conn_options'));

        foreach ($subsets as $key => $value) {
            $emultimedia = $mongo->collections->emultimedia;
            $count = $emultimedia->count(
                [
                    'MulOtherNumber' => $taxonomyIRN,
                    'DetSubject' => $key
                ]
            );

            if ($count > 0) {
                $subsets[$key] = true;
            }
            else {
                $subsets[$key] = false;
            }
        }

        return $subsets;
    }
}
