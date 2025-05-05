<?php

namespace App\Http\Controllers;

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
            $record = $multimedia->getRecord($irn);
            if (empty($record)) {
                abort(404);
            }

            return $record;
        });

        if (empty($record)) {
            abort(503);
        }

        // Retrieve and store in cache all primary records so we don't have to re-query MongoDB later
        $primaryRecords = Cache::remember('primary_records', config('emuconfig.cache_ttl'), function () {
            $multimediaModel = new Multimedia();
            return $multimediaModel->getPrimaryRecords();
        });

        // Find the previous/next links for this detail page, to navigate through them all
        // without having to go back to the homepage.
        $multimediaModel = new Multimedia();
        $prevNextLinks = $multimediaModel->getDetailPrevNextLinks($primaryRecords, $irn);

        $view = view('detail', [
            'record' => $record,
            'prev_next' => $prevNextLinks,
        ]);

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

        if (empty($records)) {
            abort(404);
        }

        $taxonomy = new Taxonomy();
        $taxon = $taxonomy->getRecord($taxonomyIRN);
        $genusSpecies = $taxon['ClaGenus'] . " " . $taxon['ClaSpecies'];

        $view = view('subset', [
            'genus_species' => $genusSpecies,
            'type' => $type,
            'records' => $records,
        ]);

        return $view;
    }
}
