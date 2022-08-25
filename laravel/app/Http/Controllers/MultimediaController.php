<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Multimedia;
use App\Models\Taxonomy;

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
        $mmId = "multimedia_record_$irn";

        $record = Cache::remember($mmId, config('emuconfig.cache_ttl'), function () use ($irn)
        {
            $multimedia = new Multimedia();
            return $multimedia->getRecord($irn);
        });

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

        $taxonomy = new Taxonomy();
        $taxon = $taxonomy->getRecord($taxonomyIRN);
        $genusSpecies = $taxon['ClaGenus'] . " " . $taxon['ClaSpecies'];

        $view = view('subset', [
            'genus_species' => $genusSpecies,
            'type' => $type,
            'records' => $records,
        ])->render();

        return $view;
    }
}
