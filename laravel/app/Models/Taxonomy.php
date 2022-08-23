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
        $this->mongo = new Client(env('MONGO_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $etaxonomy = $this->mongo->collections->etaxonomy;
        $record = $etaxonomy->findOne(['irn' => $irn]);
        $this->record = $record;

        return $this->record;
    }
}
