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
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $taxonomy = $mongo->emu->etaxonomy;
        $this->record = $taxonomy->findOne(['irn' => $irn]);

        if (is_null($this->record)) {
            return [];
        }

        return $this->record;
    }

    /**
     *
     * Gets the Taxonomy IRN based upon the MulMultiMediaRef field in the
     * etaxonomy module, MulMultiMediaRef should be equal to the current multimedia page.
     *
     * As a fallback, gets the Taxonomy IRN based upon the multimedia MulOtherNumberSource.
     * The array key for MulOtherNumberSource == "etaxonomy irn" should be used
     * on the MulOtherNumber field to return the etaxonomy irn value.
     *
     * @param array $multimedia
     *
     * @return string
     */
    public static function getTaxonomyIRN(array $multimedia): string
    {
        if (empty($multimedia)) {
            return "";
        }

        // If we have an attached Multimedia record, use that first.
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $etaxonomy = $mongo->emu->etaxonomy;
        $taxonomy = $etaxonomy->findOne(['MulMultiMediaRef' => $multimedia['irn']]);

        if (!empty($taxonomy)) {
            return $taxonomy['irn'];
        }

        // MulOtherNumber fallback
        if (!is_array($multimedia['MulOtherNumber'])) {
            return $multimedia['MulOtherNumber'];
        }

        if (!is_array($multimedia['MulOtherNumberSource'])) {
            return $multimedia['MulOtherNumber'][0];
        }

        foreach ($multimedia['MulOtherNumberSource'] as $key => $value) {
            if ($value === "etaxonomy irn") {
                return $multimedia['MulOtherNumber'][$key];
            }
        }
    }
}
