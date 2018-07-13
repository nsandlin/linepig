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
    public function getRecord($irn): array
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
        $record['wrong_multimedia'] = $this->getOldMultimedia($record);
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
     * Alters the Multimedia URL so we have a proper URL reference to the file
     * on the Multimedia server.
     *
     * @param array $record
     *   Multimedia record array
     *
     * @return string
     *   Returns string with the corrected URL
     */
    public static function fixImageURL($record): string
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
    public static function fixThumbnailURL($record): string
    {
        $filename = $record['thumbnail']['identifier'];

        // Let's check if we have a thumbnail filename first.
        if (empty($filename)) {
            return "";
        }

        $irn = $record['irn'];
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
    public function getGenusSpecies($record): string
    {
        if (empty($record['etaxonomy:MulMultiMediaRef_tab'][0])) {
            return "";
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
    public function getAuthor($record): string
    {
        if (empty($record['etaxonomy:MulMultiMediaRef_tab'][0]['AutAuthorString'])) {
            return "";
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
    public function getRights($record): string
    {
        if (empty($record['DetMediaRightsRef']['SummaryData'])) {
            return "";
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
     * Retrieves old Multimedia classification info.
     * This function retrieves the old, "incorrectly" used Multimedia image record info from
     * the Narrative module.
     *
     * @param array $record
     *   Array of the Multimedia record.
     *
     * @return array $record
     *   Returns an array of the old multimedia information.
     */
    public function getOldMultimedia($record): array
    {
        $taxonomyIRN = "";
        $oldMultimedia = null;

        // Basic check to see if we have a Taxonomy IRN in the record.
        if (empty($record['MulOtherNumberSource_tab'])) {
            return array();
        }

        // Loop through the Other Number Source table to get the etaxonomy IRN.
        foreach ($record['MulOtherNumberSource_tab'] as $key => $value) {
            if ($value == 'etaxonomy irn') {
                $sourceKey = $key;
            }
        }

        // If we don't have an etaxonomy irn source key, return.
        if (is_null($sourceKey)) {
            return array();
        }
        
        // Let's now grab the Taxonomy record IRN.
        if (!empty($record['MulOtherNumber_tab'][$sourceKey])) {
            $taxonomyIRN = $record['MulOtherNumber_tab'][$sourceKey];
        } else {
            return array();
        }

        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('etaxonomy', $session);
        $terms = new \IMuTerms();
        $terms->add('irn', $taxonomyIRN);
        $hits = $module->findTerms($terms);
        $columns = array(
            'irn',
            '<enarratives:TaxTaxaRef_tab>.(
                irn, NarNarrative,
                MulMultiMediaRef_tab.(
                    irn, MulIdentifier, thumbnail,
                    <etaxonomy:MulMultiMediaRef_tab>.(ClaFamily, ClaGenus, ClaSpecies),
                ),
             )',
        );
        $results = $module->fetch('start', 0, 1, $columns);

        // Important: if we do NOT have a reverse-attached Narrative, then return empty array.
        if (empty($results->rows[0]['enarratives:TaxTaxaRef_tab'])) {
            return array();
        }

        // Process results.
        if (!empty($results->rows)) {
            $record = $results->rows[0];

            // Add the previous image if we have it.
            if (!empty($record['enarratives:TaxTaxaRef_tab'][0]['MulMultiMediaRef_tab'][0])) {
                $record['thumbnail_url'] = self::fixThumbnailURL(
                    $record['enarratives:TaxTaxaRef_tab'][0]['MulMultiMediaRef_tab'][0]
                );
            }

            // Let's set the previous taxonomy.
            if (!empty($record['enarratives:TaxTaxaRef_tab'][0]['MulMultiMediaRef_tab'][0]['etaxonomy:MulMultiMediaRef_tab'][0])) {
                $previousTaxonomy =
                        $record['enarratives:TaxTaxaRef_tab'][0]['MulMultiMediaRef_tab'][0]['etaxonomy:MulMultiMediaRef_tab'][0];
            }

            // Logic for which classification to display.
            if (empty($previousTaxonomy['ClaSpecies'])) {
                $record['taxon_to_display'] = $previousTaxonomy['ClaGenus'] . " sp.";
            } elseif (empty($previousTaxonomy['ClaGenus'])) {
                $record['taxon_to_display'] = $previousTaxonomy['ClaFamily'] . " sp.";
            } else {
                $record['taxon_to_display'] = $previousTaxonomy['ClaGenus'] . " " . $previousTaxonomy['ClaSpecies'];
            }

            return $record;
        } else {
            return array();
        }
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
     * @param array $record
     *   The Multimedia record.
     *
     * @return string
     *   Returns a string of HTML containing the URL.
     */
    public function getWSCLink($record): string
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
    public function getCollectionRecordURL($record): string
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

        return "";
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
    public function getNotes($record): string
    {
        if (empty($record['NteText0'][0])) {
            return "";
        } else {
            return $record['NteText0'][0];
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
    public function checkSubsets($taxonomyIRN): array
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
