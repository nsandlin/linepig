<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\BacklinkImage;

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
     * Retrieves the individual Multimedia record.
     *
     * @param int $irn
     *   The IRN of the Multimedia record to return.
     *
     * @return array
     *   Returns an array of the Multimedia record.
     */
    public function getRecord($irn) : array
    {
        // Create a Session and selecting the module we want to query.
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('irn', $irn);

        // Fetching results.
        $module->findTerms($terms);
        $columns = config('emuconfig.multimedia_fields');
        $result = $module->fetch('start', 0, 1, $columns);

        // If there's no record, abort.
        if (empty($result->rows)) {
            abort(404);
        }

        $record = $result->rows[0];

        // Additional record processing.
        $record['species_name'] = self::fixSpeciesTitle($record);
        $record['image_url'] = self::fixImageURL($record);
        $record['genus_species'] = $this->getGenusSpecies($record);
        $record['author'] = $this->getAuthor($record);
        $record['rights'] = $this->getRights($record);
        $record['backlinked_image'] = $this->getBacklinkedImage($irn);
        $record['bold_url'] = $this->getBOLD($record);
        $record['world_spider_catalog_url'] = $this->getWSCLink($record);
        $record['collection_record_url'] = $this->getCollectionRecordURL($record);
        $record['notes'] = $this->getNotes($record);
        $record['taxonomy_irn'] = empty($record['MulOtherNumber_tab'][0]) ? "" :
                                        $record['MulOtherNumber_tab'][0];

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
    public function getRecords() : \Illuminate\Support\Collection
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
    public function getSubset($type, $taxonomyIRN) : array
    {
        $rows = array();
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        $terms = new \IMuTerms();
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
    public function getCount() : int
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
    public static function fixSpeciesTitle($record) : string
    {
        $title = $record['MulTitle'];
        $newTitle = str_replace(" female epigynum", "", $title);
        $newTitle = str_replace(" male epigynum", "", $newTitle);

        return $newTitle;
    }

    /**
     * Alters the Multimedia URL so we have a proper URL reference to the file
     * on the Multimedia server.
     *
     * @param array $record
     *   Multimedia record array
     *
     * @return string
     *   Returns string with the corrected URL
     */
    public static function fixImageURL($record) : string
    {
        $irn = $record['irn'];
        $filename = $record['MulIdentifier'];
        $url = "";
        $url = "/" . substr($irn, -3, 3) . $url;
        $irn = substr_replace($irn, '', -3, 3);
        $url = "/" . $irn . $url;

        $url = "http://" . config('emuconfig.multimedia_server') . $url .
                 "/" . $record['MulIdentifier'];

        return $url;
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
    public static function fixThumbnailURL($record) : string
    {
        $irn = $record['irn'];
        $filename = $record['thumbnail']['identifier'];
        $url = "";
        $url = "/" . substr($irn, -3, 3) . $url;
        $irn = substr_replace($irn, '', -3, 3);
        $url = "/" . $irn . $url;

        $url = "http://" . config('emuconfig.multimedia_server') . $url .
                 "/" . $record['thumbnail']['identifier'];

        return $url;
    }

    /**
     * Retrieves individual record's Taxonomy Genus/Species name.
     *
     * @param array $record
     *  The Multimedia record.
     *
     * @return string
     *   Returns a string of the Genus, Species name.
     */
    public function getGenusSpecies($record) : string
    {
        if (empty($record['etaxonomy:MulMultiMediaRef_tab'][0])) {
            return null;
        }

        $taxonomyRecord = $record['etaxonomy:MulMultiMediaRef_tab'][0];

        $genus = $taxonomyRecord['ClaGenus'];
        $species = $taxonomyRecord['ClaSpecies'];
        $genusSpecies = $genus . " " . $species;

        return $genusSpecies;
    }

    /**
     * Retrieves the author for an individual Multimedia record.
     *
     * @param array $record
     *   The Multimedia record.
     *
     * @return string
     *   Returns a string of the author for the record.
     */
    public function getAuthor($record) : string
    {
        if (empty($record['etaxonomy:MulMultiMediaRef_tab'][0]['AutAuthorString'])) {
            return null;
        } else {
            return $record['etaxonomy:MulMultiMediaRef_tab'][0]['AutAuthorString'];
        }
    }

    /**
     * Gets the Rights for an individual Multimedia record.
     *
     * @param array $record
     *   The Multimedia record.
     *
     * @return string
     *   Returns a string of the rights.
     */
    public function getRights($record) : string
    {
        if (empty($record['DetMediaRightsRef']['SummaryData'])) {
            return null;
        }

        $emuRights = $record['DetMediaRightsRef']['SummaryData'];

        // Custom formatting to the Rights.
        $rights = str_replace("CC", config('emuconfig.rights_cc'), $emuRights);
        $rights = str_replace("NC", config('emuconfig.rights_nc'), $rights);
        $rights = str_replace('[(c)', '[c]', $rights);
        $rights = str_replace('] - Usage, Current', '', $rights);

        return $rights;
    }

    /**
     * Retrieves the Back-linked image for the Multimedia record.
     *
     * @param int $irn
     *   The IRN of the Multimedia record.
     *
     * @return string
     *   Returns a formatted string of the URL of the back-linked image.
     */
    public function getBacklinkedImage($irn) : string
    {
        $bli = new BacklinkImage($irn);
        $url = $bli->getFormattedImageURL();

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
    public function getBOLD($record) : string
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
     * @param array $record
     *   The Multimedia record.
     *
     * @return string
     *   Returns a string of HTML containing the URL.
     */
    public function getWSCLink($record) : string
    {
        if (empty($record['etaxonomy:MulMultiMediaRef_tab'][0])) {
            return "";
        }

        $genus = $record['etaxonomy:MulMultiMediaRef_tab'][0]['ClaGenus'];
        $species = $record['etaxonomy:MulMultiMediaRef_tab'][0]['ClaSpecies'];
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
    public function getCollectionRecordURL($record) : string
    {
        // If the attached Multimedia record is an external link, return that URL.
        if (!empty($record['RelRelatedMediaRef_tab'][0])) {
            if ($record['RelRelatedMediaRef_tab'][0]['MulMimeType'] == "x-url") {
                return $record['RelRelatedMediaRef_tab'][0]['MulIdentifier'];
            }
        } elseif (!empty($record['ecatalogue:MulMultiMediaRef_tab'][0]['irn'])) {
            return "/catalogue/" . $record['ecatalogue:MulMultiMediaRef_tab'][0]['irn'];
        } else {
            return "";
        }
    }

    /**
     * Retrieves the Multimedia notes for a record.
     *
     * @param array $record
     *   The Multimedia record.
     *
     * @return string $notes
     *   Returns the notes field.
     */
    public function getNotes($record) : string
    {
        if (empty($record['NteText0'])) {
            return "";
        } else {
            return $record['NteText0'];
        }
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
    public function checkSubsets($taxonomyIRN) : array
    {
        $subsets = config('emuconfig.subsets_to_check');
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        foreach ($subsets as $key => $value) {
            $terms = new \IMuTerms();
            $terms->add('MulOtherNumber_tab', $taxonomyIRN);
            $terms->add('DetSubject_tab', $key);

            // Fetching results.
            $hits = $module->findTerms($terms);
            $results = $module->fetch('start', 0, 1, 'irn');
            $count = $results->count;
            
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
