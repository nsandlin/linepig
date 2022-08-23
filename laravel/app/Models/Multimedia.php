<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
     * The MongoDB Client
     *
     * @var MongoDB\Client $mongo
     */
    protected $mongo;

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
        $this->mongo = new Client(env('MONGO_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $emultimedia = $this->mongo->collections->emultimedia;
        $document = $emultimedia->findOne(['irn' => $irn]);
        $record = $document;

        // Additional record processing.
        $record['taxonomy_irn'] = $record['MulOtherNumber'] ?? null;

        // Get taxonomy record info
        $taxonomy = new Taxonomy();
        $this->taxonomy = $taxonomy->getRecord($record['taxonomy_irn']);

        // Get catalog record info
        $catalog = new Catalog();
        $this->catalog = $catalog->getRecordFromMultimediaIRN($irn);

        $record['species_name'] = self::fixSpeciesTitle($record);
        $record['image_url'] = $record['AudAccessURI'];
        $record['genus_species'] = $this->taxonomy['ClaGenus'] . " " . $this->taxonomy['ClaSpecies'];
        $record['author'] = $this->taxonomy['AutAuthorString'];
        $record['rights'] = $record['RightsSummaryDataLocal'];
        $record['bold_url'] = $this->getBOLD($record);
        $record['world_spider_catalog_url'] = $this->getWSCLink($this->taxonomy);
        $record['collection_record_url'] = $this->getCollectionRecordURL($record);
        $record['notes'] = $record['NteText0'] ?? "";
        $record['subsets'] = $this->checkSubsets($record['taxonomy_irn']);
        $record['catirn'] = str_replace("/catalogue/", "", $record['collection_record_url']);
        $record['guid'] = $this->catalog['DarGlobalUniqueIdentifier'] ?? "";

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
    public function getRecords(): \Illuminate\Support\Collection
    {
        $records = DB::table('search')
                           ->orderBy('genus', 'asc')
                           ->orderBy('species', 'asc')
                           ->get();
        $this->records = $records;
        $this->count = DB::table('search')->count();

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
        $rows = array();
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        $terms = new \IMuTerms();
        $terms->add('AdmPublishWebNoPassword', 'Yes');
        $terms->add('MulOtherNumber_tab', $taxonomyIRN);
        $terms->add('MulMimeType', 'image');

        // If we have a type that's not "all", query for that subset.
        if ($type !== "all") {
            $terms->add('DetSubject_tab', $type);
        }

        $hits = $module->findTerms($terms);
        $columns = config('emuconfig.subset_fields');
        $results = $module->fetch('start', 0, -1, $columns);
        $rows = $results->rows;

        // If there's no records, abort.
        if (empty($results->rows)) {
            abort(404);
        }

        // Additional processing for each record.
        foreach ($rows as $key => $value) {
            $rows[$key]['thumbnail_url'] = self::fixThumbnailURL($value);
            $rows[$key]['species_name'] = self::fixSpeciesTitle($value);
        }

        return $rows;
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
     * @param array $record
     *   Multimedia record array
     *
     * @return string
     *   Returns string with the corrected URL
     */
    public static function fixThumbnailURL($record): string
    {
        if (!isset($record['thumbnail']['identifier'])) {
            return "";
        }

        $filename = $record['thumbnail']['identifier'];

        $irn = $record['irn'];
        $url = "";
        $url = "/" . substr($irn, -3, 3) . $url;
        $irn = substr_replace($irn, '', -3, 3);
        $url = "/" . $irn . $url;

        $url = "https://" . config('emuconfig.multimedia_server') . $url .
                 "/" . $record['thumbnail']['identifier'];

        return $url;
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
        $genusSpecies = DB::table('bold')->where('genus_species', $record['genus_species'])->value('genus_species');

        if (is_null($genusSpecies)) {
            return "";
        }

        $boldGS = str_replace(" ", "+", $genusSpecies);
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
     * Retrieves the collection record link (Catalogue).
     *
     * @param array $record
     *   The EMu Multimedia record.
     *
     * @return string $url
     *   Returns a string of the URL of the collection record, either external or internal
     *   Catalogue record.
     */
    public function getCollectionRecordURL($record): string
    {
        $firstfour = "";
        // If the attached Multimedia record is an external link, return that URL.
        if (!empty($record['RelRelatedMediaRef_tab'][0])) {
            $firstfour = substr($record['RelRelatedMediaRef_tab'][0]['MulIdentifier'], 0, 4);
            if ($firstfour == "http") {
                return $record['RelRelatedMediaRef_tab'][0]['MulIdentifier'];
            }
        } elseif (!empty($record['ecatalogue:MulMultiMediaRef_tab'][0]['irn'])) {
            return "/catalogue/" . $record['ecatalogue:MulMultiMediaRef_tab'][0]['irn'];
        } else {
            return "";
        }
        return "";
    }

    /**
     * Retrieves the guid (Catalogue).
     *
     * @param array $record
     *   The EMu Multimedia record.
     *
     * @return string $guid
     *   Returns a string of the guid of the Catalog collection record.
     */
    public function getGUID($record): string
    {
    if (!empty($record['ecatalogue:MulMultiMediaRef_tab'][0]['irn'])) {
            return  $record['ecatalogue:MulMultiMediaRef_tab'][0]['DarGlobalUniqueIdentifier'];
        } else {
            return "";
        }
        return "";
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

        foreach ($subsets as $key => $value) {
            $emultimedia = $this->mongo->collections->emultimedia;
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
