<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Client;

class Narrative extends Model
{
    use HasFactory;

    /**
     * The Taxonomy record array, for an individual record.
     *
     * @var array|null $record
     */
    protected $record;

    /**
     * Retrieves the individual Taxonomy record.
     *
     * @param string $taxonomyIRN
     *   The IRN of the Taxonomy record
     *
     * @return array|null
     *   Returns the narrative record
     */
    public function getRecordByTaxonomyIRN(string $taxonomyIRN)
    {
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $narrative = $mongo->emu->enarratives;
        $this->record = $narrative->findOne(['TaxTaxaRef' => $taxonomyIRN]);

        if (is_null($this->record)) {
            return null;
        }

        return $this->record;
    }

    /**
     * Retrieves the Narrative record associated with the multimedia (IRN)
     *
     * @param string $multimediaIRN
     *   The IRN of the Multimedia record
     *
     * @return array|null
     *   Returns the narrative record
     */
    public function getRecordByMultimediaIRN(string $multimediaIRN)
    {
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $narrative = $mongo->emu->enarratives;
        $this->record = $narrative->findOne(['MulMultiMediaRef' => $multimediaIRN]);

        if (is_null($this->record)) {
            return null;
        }

        return $this->record;
    }
}
