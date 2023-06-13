<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MongoDB\Client;

class Catalog extends Model
{
    /**
     * The Catalogue record array, for an individual record.
     *
     * @var array $record
     */
    protected $record;

    /**
     * Retrieves the Catalog record via the Multimedia IRN.
     *
     * @param int $multimediaIRN
     *   The multimedia record IRN
     *
     * @return array
     *   Returns an array of the catalog record
     */
    public function getRecordFromMultimediaIRN(int $multimediaIRN): array
    {
        // Retrieve MongoDB document
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $catalog = $mongo->emu->ecatalogue;
        $document = $catalog->findOne(['MulMultiMediaRef' => (string) $multimediaIRN]);
        $record = $document;

        if (is_null($record)) {
            return [];
        }

        return $record;
    }

    /**
     * Retrieves the individual Catalog record.
     *
     * @param int $irn
     *   The IRN of the Catalog record to return.
     *
     * @param bool $isImport
     *   Imports get access to full EMu ecatalogue module,
     *   vs. a regular Catalog get which should be limited to the linepig collection
     *
     * @return array
     *   Returns an array of the Catalog record.
     */
    public function getRecord($irn, $isImport = false): array
    {
        if ($isImport) {
            $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
            $catalog = $mongo->emu->ecatalogue;
        } else {
            $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
            $catalog = $mongo->linepig->catalog;
        }

        $document = $catalog->findOne(['irn' => $irn]);
        $record = $document;

        if (empty($record)) {
            return [];
        }

        $record['genus_species'] = $record['DarGenus'] . " " . $record['DarSpecies'];
        $record['collection_data'] = $record['ExtendedData'][2];
        $record['total_count'] = $record['LotTotalCount'];
        $record['semaphoronts'] = $this->getSemaphoronts($record);

        // Collection event
        $collectionEventIRN = $record['ColCollectionEventRef'] ?? "";
        $collectionEvent = $this->getCollectionEvent($collectionEventIRN);

        if (!empty($collectionEvent)) {
            $record['collection_event'] = $collectionEvent['ExtendedData'][2] ?? null;
            $record['collection_method'] = $collectionEvent['ColCollectionMethod'] ?? null;
            $record['collection_event_code'] = $collectionEvent['ColCollectionEventCode'] ?? null;
            $record['date_visited_from'] = $collectionEvent['ColDateVisitedFrom'] ?? null;
            $record['date_visited_to'] = $collectionEvent['ColDateVisitedTo'] ?? null;
            $record['collected_by'] = $collectionEvent['ColPrimaryParticipantLocal'] ?? null;
            $record['habitat'] = $collectionEvent['HabHabitat'] ?? null;
        }

        $record['identified_by'] = $record['IdeIdentifiedByLocal'] ?? null;
        $record['date_identified'] = $record['IdeDateIdentified0'][0] ?? null;
        $record['lat'] = $record['DarLatitude'] ?? null;
        $record['lng'] = $record['DarLongitude'] ?? null;
        $record['elevation'] = $record['DarMinimumElevation'] ?? null;
        $record['guid'] = $record['AdmGUIDValue'] ?? null;

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
        if (empty($record['LotSemaphoront']) || empty($record['LotWetCount'])) {
            return [];
        }

        $semaphoronts = [];

        if (!is_array($record['LotSemaphoront'])) {
            $semaphoronts[$record['LotSemaphoront'][0]] = $record['LotWetCount'][0];

            return $semaphoronts;
        }

        $total = count($record['LotSemaphoront']);
        for ($i = 0; $i < $total; $i++) {
            $semaphoronts[$record['LotSemaphoront'][$i]] = $record['LotWetCount'][$i];
        }

        return $semaphoronts;
    }

    /**
     * Gets the attached collection event (ecollectionevents)
     *
     * @param string $irn
     *   The collection event IRN
     *
     * @return array
     */
    public function getCollectionEvent(string $irn): array
    {
        if (empty($irn)) {
            return [];
        }

        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $ecollectionevents = $mongo->emu->ecollectionevents;
        $document = $ecollectionevents->findOne(['irn' => $irn]);
        $record = $document;

        return $record;
    }
}
