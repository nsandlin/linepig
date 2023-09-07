<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use MongoDB\Client;
use Carbon\Carbon;
use App\Models\Taxonomy;
use App\Models\Narrative;
use App\Models\Catalog;

class Multimedia extends Model
{
    use HasFactory;

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
     * The associated narrative record (reverse-attached to the taxonomy record)
     *
     * @var array $narrative
     */
    protected $narrative;

    /**
     * The associated catalog record
     *
     * @var array $catalog
     */
    protected $catalog;

    /**
     * Retrieves the individual Multimedia record.
     *
     * @param string $irn
     *   The IRN of the Multimedia record to return
     *
     * @param bool $isImport
     *   Is the function being called for the MultimediaImport command
     *
     * @return array
     *   Returns an array of the Multimedia record
     */
    public function getRecord($irn, $isImport = false): array
    {
        $environment = App::environment();
        $mongo = null;
        $multimediaCollection = null;

        if ($isImport) {
            $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
            $multimediaCollection = $mongo->emu->emultimedia;
        } else {
            $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));

            if ($environment === "production") {
                $multimediaCollection = $mongo->linepig->multimedia;
            } else {
                $multimediaCollection = $mongo->linepig->multimedia_dev;
            }
        }

        $document = $multimediaCollection->findOne(['irn' => (string) $irn]);
        $this->record = $document;
        if (empty($this->record)) {
            return [];
        }

        // Get taxonomy record info
        $taxonomy = new Taxonomy();
        $this->record['taxonomy_irn'] = $taxonomy->getTaxonomyIRN($this->record);
        $this->taxonomy = $taxonomy->getRecord($this->record['taxonomy_irn']);

        // Get "old", "wrong" multimedia info
        $this->record['wrong_multimedia'] = $this->getOldWrongMultimediaInfo();

        // Get annotations
        $this->record['annotation'] = $this->getAnnotation();

        // Get catalog record info
        $catalog = new Catalog();
        $this->catalog = $catalog->getRecordFromMultimediaIRN($this->record['irn']);
        $this->record['collection_record_url'] = "";
        $this->record['catirn'] = "";
        $this->record['guid'] = "";

        if (!empty($this->catalog)) {
            $this->record['collection_record_url'] = "/catalogue/" . $this->catalog['irn'];
            $this->record['catirn'] = $this->catalog['irn'];
            $this->record['guid'] = $this->catalog['DarGlobalUniqueIdentifier'];
        } else {
            // If there is no reverse-attached catalog record, then there
            // should be a related multimedia record that includes a link
            // to boldsystems.org.
            // 
            // Query multimedia using the IRN in the RelRelatedMediaRef field.
            //
            // Each multimedia detail page should have a link to view a collection record.
            if (isset($this->record['RelRelatedMediaRef'])) {
                $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
                $multimediaCollection = $mongo->emu->emultimedia;
                $relatedMediaDoc = $multimediaCollection->findOne(['irn' => $this->record['RelRelatedMediaRef']]);
                $this->record['collection_record_url'] = $relatedMediaDoc['MulIdentifier'] ?? "";
            }
        }

        $this->record['species_name'] = self::fixSpeciesTitle($this->record);
        $this->record['image_url'] = $this->record['AudAccessURI'];
        $this->record['genus_species'] = $this->getTaxonomyGenusSpecies();
        $this->record['author'] = $this->taxonomy['AutAuthorString'] ?? "";
        $this->record['rights'] = $this->getCopyright($this->record);
        $this->record['bold_url'] = $this->getBOLD($this->record);
        $this->record['world_spider_catalog_url'] = $this->getWSCLink($this->taxonomy);
        $this->record['notes'] = $this->record['NteText0'] ?? "";
        $this->record['subsets'] = $this->checkSubsets($this->record['taxonomy_irn']);

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
     * Retrieves most recently updated/added Multimedia records.
     *
     * @return array
     *   Returns Multimedia records
     */
    public function getMostRecentRecords(): array
    {
        $documents = [];
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));

        if (App::environment() === "production") {
            $searchCollection = $mongo->linepig->search;
        } else {
            $searchCollection = $mongo->linepig->search_dev;
        }

        $daysAgoCarbon = Carbon::now('UTC')->subDays(config('emuconfig.homepage_days_ago_for_recent_records'));
        $utcDaysAgo = new \MongoDB\BSON\UTCDateTime($daysAgoCarbon);
        $filter = [
            'keywords' => ['$in' => ['primary']],
            'date_created' => ['$gte' => $utcDaysAgo],
        ];

        $cursor = $searchCollection->find($filter);
        if (is_null($cursor)) {
            return [];
        }

        foreach ($cursor as $document) {
            $documents[] = $document;
        }

        // Sort the results
        usort($documents, function($a, $b) {
            $genusComp = $a['genus'] <=> $b['genus'];
            if ($genusComp !== 0) {
                return $genusComp;
            }

            return $a['species'] <=> $b['species'];
        });

        $records = [];
        foreach ($documents as $document) {
            $record = [];
            $genus = $document['genus'];
            $species = $document['species'];
            $text = "$genus $species";
            $link = env('APP_URL', "https://linepig.fieldmuseum.org") .
                    "/search-results/genus/$genus/species/$species/keywords/none";

            $record['text'] = $text;
            $record['link']= $link;
            $records[] = $record;
        }

        return $records;
    }

    /**
     * Retrieves all "primary" Multimedia records.
     *
     * @return array
     *   Returns an array of all of the "primary" Multimedia records
     */
    public function getPrimaryRecords(): array
    {
        $records = [];
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $searchCollection = $mongo->linepig->search;
        $cursor = $searchCollection->find(
            ['search.DetSubject' => 'primary'],
            ['sort' => ['genus' => 1, 'species' => 1]]
        );

        foreach ($cursor as $record) {
            $records[] = $record;
        }

        return $records;
    }

    /**
     * Retrieves the previous/next links for a multimedia detail page.
     *
     * @param array $records
     *   All records on the LinEpig website
     * @param string $irn
     *   IRN of current multimedia detail page
     *
     * @return array
     *   Array of the previous/next hyperlinks
     */
    public function getDetailPrevNextLinks(array $records, string $irn): array
    {
        $links = [];

        foreach ($records as $k => $record) {
            if ($record['irn'] == $irn) {
                if (isset($records[$k-1])) {
                    $links['prev'] = "/multimedia/" . $records[$k-1]['irn'];
                }
                if (isset($records[$k+1])) {
                    $links['next'] = "/multimedia/" . $records[$k+1]['irn'];
                }
            }
        }

        return $links;
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
        $records = [];
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $taxonomyCollection = $mongo->linepig->taxonomy;
        $taxonomy = $taxonomyCollection->findOne(['irn' => $taxonomyIRN]);
        $multimediaCollection = $mongo->linepig->multimedia;

        if (empty($taxonomy)) {
            abort(404);
        }

        $multimediaRefs = Arr::wrap($taxonomy['MulMultiMediaRef']);

        foreach ($multimediaRefs as $irn) {
            $record = $multimediaCollection->findOne(['irn' => $irn]);
            if (is_null($record)) {
                continue;
            }

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

        // The file extension should actually ALWAYS be .jpg
        // So we shouldn't use the original file extension for the thumbnail
        $fileWithoutExtension = str_replace([".jpg", ".JPG", ".png", ".PNG"], "", $accessURI);
        $thumbWithExtension = ".thumb.jpg";
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

        if (!isset($taxonomy['ClaSpecies'])) {
            $url = "http://www.wsc.nmbe.ch/search?sFamily=&fMt=begin&sGenus=$genus&gMt=exact" .
                    "&multiPurpose=slsid&mMt=begin&searchSpec=s";

            return $url;
        }

        $species = $taxonomy['ClaSpecies'];
        $url = "http://www.wsc.nmbe.ch/search?sFamily=&fMt=begin&sGenus=$genus&gMt=exact" .
                    "&sSpecies=" . $species .
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
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));

        foreach ($subsets as $key => $value) {
            $multimedia = $mongo->linepig->multimedia;
            $count = $multimedia->count(
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

    /**
     * Returns the Genus species of a taxonomy.
     *
     * @return string
     *   Returns a string of the genus and species combined
     */
    public function getTaxonomyGenusSpecies(): string
    {
        $genusSpecies = "";

        if (isset($this->taxonomy['ClaGenus'])) {
            $genusSpecies .= $this->taxonomy['ClaGenus'];
        }

        if (isset($this->taxonomy['ClaSpecies'])) {
            $genusSpecies .= " " . $this->taxonomy['ClaSpecies'];
        }

        return $genusSpecies;
    }

    /**
     * Gets the "old", "wrong" multimedia for the detail page.
     *
     * @return array
     */
    public function getOldWrongMultimediaInfo(): array
    {
        $wrongMultimedia = [];

        // Get narrative record info -- (Corrections)
        if ($this->record['taxonomy_irn']) {
            $narrativeModel = new Narrative();
            $narrative = $narrativeModel->getRecordByTaxonomyIRN($this->record['taxonomy_irn']);
            if (empty($narrative)) {
                return [];
            }

            // Set up "wrong multimedia" for detail page
            if (isset($narrative['MulMultiMediaRef'])) {
                $narrativeMultimedia = (array) $narrative['MulMultiMediaRef'];
                $narrativeMultimediaIRN = $narrativeMultimedia[0] ?? "";

                // Get the old/wrong multimedia
                $mongoEMu = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
                $emultimedia = $mongoEMu->emu->emultimedia;
                $multimedia = $emultimedia->findOne(['irn' => $narrativeMultimediaIRN]);

                $wrongMultimedia['narrative'] = implode(" ", (array) $narrative['NarNarrative']);
                $wrongMultimedia['thumbnail_url'] = $multimedia['AudAccessURI'] ?? "#";
                $wrongMultimedia['taxon_to_display'] = $multimedia['MulDescription'] ?? "";
            }
        }

        return $wrongMultimedia;
    }

    /**
     * Gets the annotation for the multimedia record.
     *
     * @return string
     */
    public function getAnnotation(): string
    {
        $narrativeModel = new Narrative();
        $narrativeRecord = $narrativeModel->getRecordByTaxonomyIRN($this->record['taxonomy_irn']);
        if (empty($narrativeRecord)) {
            return "";
        }

        if (isset($narrativeRecord['NarNarrative'])) {
            return $narrativeRecord['NarNarrative'];
        }

        return "";
    }
}
