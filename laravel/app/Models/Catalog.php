<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Multimedia;
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
        $this->mongo = new Client(env('MONGO_COLLECTIONS_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $ecatalogue = $this->mongo->collections->ecatalogue;
        $document = $ecatalogue->findOne(['MulMultiMediaRef' => $multimediaIRN]);
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
     * @return array
     *   Returns an array of the Catalog record.
     */
    public function getRecord($irn): array
    {
        // Retrieve MongoDB document
        $this->mongo = new Client(env('MONGO_COLLECTIONS_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $ecatalogue = $this->mongo->collections->ecatalogue;
        $document = $ecatalogue->findOne(['irn' => $irn]);
        $record = $document;

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

        // Attached Multimedia processing.
        if (!empty($record['MulMultiMediaRef'])) {
            $emultimedia = $this->mongo->collections->emultimedia;

            foreach ($record['MulMultiMediaRef'] as $multimediaIRN) {
                $document = $emultimedia->findOne(['irn' => $multimediaIRN]);
                $document['thumbnail_url'] = Multimedia::fixThumbnailURL($document['AudAccessURI']);
                $record['multimedia'][] = $document;
            }
        }

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

        $ecollectionevents = $this->mongo->collections->ecollectionevents;
        $document = $ecollectionevents->findOne(['irn' => $irn]);
        $record = $document;

        return $record;
    }
}
