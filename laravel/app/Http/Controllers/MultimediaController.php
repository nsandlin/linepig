<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Multimedia;

class MultimediaController extends Controller
{
    /**
     * Handles the individual Multimedia page control.
     *
     * @param int $irn
     *  The IRN of the Multimedia record.
     *
     * @return view
     */
    public function showMultimedia($irn)
    {
        $multimedia = new Multimedia();
        $record = $multimedia->getRecord($irn);

        if (empty($record)) {
            abort(503);
        }

        $view = view('detail', [
            'record' => $record,
        ])->render();

        return $view;
    }

    /**
     * Handles displaying a subset of Multimedia records, linked from the detail page.
     *
     * @param string $type
     *   The subset type that we're displaying.
     *
     * @param int $taxonomyIRN
     *   The Taxonomy IRN for the subset of records to display.
     *
     * @return view
     */
    public function showSubset($type, $taxonomyIRN)
    {
        $multimedia = new Multimedia();
        $records = $multimedia->getSubset($type, $taxonomyIRN);
        $genusSpecies = $multimedia->getGenusSpecies($records[0]);

        $view = view('subset', [
            'genus_species' => $genusSpecies,
            'type' => $type,
            'records' => $records,
        ])->render();

        return $view;
    }
}
