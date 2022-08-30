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
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $taxonomy = $mongo->linepig->taxonomy;
        $this->record = $taxonomy->findOne(['irn' => $irn]);

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
        if (empty($multimediaRecord)) {
            return "";
        }

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
