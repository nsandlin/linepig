<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Client;

class Taxonomy extends Model
{
    use HasFactory;

    /**
     * The Taxonomy record array, for an individual record.
     *
     * @var array $record
     */
    protected $record;

    /**
     * The MongoDB Client
     *
     * @var MongoDB\Client $mongo
     */
    protected $mongo;

    /**
     * Retrieves the individual Taxonomy record.
     *
     * @param int $irn
     *   The IRN of the Taxonomy record to return.
     *
     * @return array
     *   Returns an array of the Taxonomy record.
     */
    public function getRecord($irn): array
    {
        // Retrieve MongoDB document
        $this->mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $etaxonomy = $this->mongo->emu->etaxonomy;
        $this->record = $etaxonomy->findOne(['irn' => $irn]);

        if (is_null($this->record)) {
            return [];
        }

        return $this->record;
    }

    /**
     * Gets the Taxonomy IRN based upon the multimedia MulOtherNumberSource.
     * The array key for MulOtherNumberSource == "etaxonomy irn" should be used
     * on the MulOtherNumber field to return the etaxonomy irn value.
     *
     * @param array $multimediaRecord
     *
     * @return string
     */
    public static function getTaxonomyIRN(array $multimediaRecord): string
    {
        if (!is_array($multimediaRecord['MulOtherNumber'])) {
            return $multimediaRecord['MulOtherNumber'];
        }

        if (!is_array($multimediaRecord['MulOtherNumberSource'])) {
            return $multimediaRecord['MulOtherNumber'][0];
        }

        foreach ($multimediaRecord['MulOtherNumberSource'] as $key => $value) {
            if ($value === "etaxonomy irn") {
                return $multimediaRecord['MulOtherNumber'][$key];
            }
        }
    }
}
