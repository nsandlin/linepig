<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Multimedia;

class Catalog extends Model
{
    /**
     * The Catalogue record array, for an individual record.
     *
     * @var array $record
     */
    protected $record;

    /**
     * Retrieves the individual Catalog record.
     *
     * @param int $irn
     *   The IRN of the Catalog record to return.
     *
     * @return array
     *   Returns an array of the Catalog record.
     */
    public function getRecord($irn) : array
    {
        // Create a Session and selecting the module we want to query.
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('ecatalogue', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('irn', $irn);

        // Fetching results.
        $module->findTerms($terms);
        $columns = config('emuconfig.catalog_fields');
        $result = $module->fetch('start', 0, 1, $columns);

        // If there's no record, abort.
        if (empty($result->rows)) {
            abort(404);
        }

        $record = $result->rows[0];

        // Additional record processing.
        $record['genus_species'] = $record['DarGenus'] . " " . $record['DarSpecies'];
        $record['collection_data'] = $record['SummaryData'];
        $record['total_count'] = $record['LotTotalCount'];
        $record['semaphoronts'] = $this->getSemaphoronts($record);
        $record['identified_by'] = $record['IdeIdentifiedByRef_nesttab'][0][0]['SummaryData'] ?? null;
        $record['date_identified'] = $record['IdeDateIdentified0'][0] ?? null;
        $record['collection_event'] = $record['ColCollectionEventRef']['SummaryData'] ?? null;
        $record['collection_method'] = $record['ColCollectionEventRef']['ColCollectionMethod'] ?? null;
        $record['collection_event_code'] = $record['ColCollectionEventRef']['ColCollectionEventCode'] ?? null;
        $record['date_visited_from'] = $record['ColCollectionEventRef']['ColDateVisitedFrom'] ?? null;
        $record['date_visited_to'] = $record['ColCollectionEventRef']['ColDateVisitedTo'] ?? null;
        $record['collected_by'] =
                $record['ColCollectionEventRef']['ColParticipantRef_tab'][0]['SummaryData'] ?? null;
        $record['lat'] = $record['DarLatitude'] ?? null;
        $record['lng'] = $record['DarLongitude'] ?? null;
        $record['elevation'] = $record['DarMinimumElevation'] ?? null;
        $record['habitat'] = $this->getHabitat($record) ?? null;

        // Attached Multimedia processing.
        if (!empty($record['MulMultiMediaRef_tab'])) {
            foreach ($record['MulMultiMediaRef_tab'] as $multimedia) {
                $multimedia['thumbnail_url'] = Multimedia::fixThumbnailURL($multimedia);
                $record['multimedia'][] = $multimedia; 
            }
        }

        // Set the individual Multimedia record.
        $this->record = $record;

        return $this->record;
    }

    /**
     * Returns an array of Semaphoronts.
     *
     * @param array $record
     *   The catalogue record array.
     *
     * @return array $semaphoronts
     *   Returns a processed array of semaphoronts.
     */
    public function getSemaphoronts($record) : array
    {
        if (empty($record['LotSemaphoront_tab']) || empty($record['LotWetCount_tab'])) {
            return array();
        }

        $semaphoronts = array();
        $total = count($record['LotSemaphoront_tab']);

        for ($i = 0; $i < $total; $i++) {
            $semaphoronts[$record['LotSemaphoront_tab'][$i]] = $record['LotWetCount_tab'][$i];
        }

        return $semaphoronts;
    }

    /**
     * Retrieves the Habitat information from the Sites record attached via Collection Events.
     *
     * @param array $record
     *   The Catalogue EMu record.
     *
     * @return string $habitat
     *   Returns a string of the Habitat value.
     */
    public function getHabitat($record)
    {
        // First, we need to verify that we have a Collection Record attached to the Catalogue.
        if (empty($record['ColCollectionEventRef']['ColSiteRef']['irn'])) {
            return "";
        }

        // Now, let's get the Site record info from EMu.
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('esites', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('irn', $record['ColCollectionEventRef']['ColSiteRef']['irn']);

        // Fetching results.
        $module->findTerms($terms);
        $columns = config('emuconfig.site_fields');
        $result = $module->fetch('start', 0, 1, $columns);

        // Return the Habitat if we have it.
        if (empty($result->rows[0]['AquHabitat_tab'][0])) {
            return "";
        } else {
            return $result->rows[0]['AquHabitat_tab'][0];
        }
    }
}
