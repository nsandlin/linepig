<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
        $record['catalog_number'] = $record['DarCatalogNumber'];

        // Set the individual Multimedia record.
        $this->record = $record;

        return $this->record;
    }
}
