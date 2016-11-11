<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Multimedia;

class HomeController extends Controller
{
    /**
     * Handles the home page rendering control.
     *
     * @return view
     */
    public function showHome() {

        // Create a Session and selecting the module we want to query.
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('MulMultimediaCreatorRef_tab', '177281');
        $terms->add('DetSubject_tab', 'epigynum');
        $terms->add('DetSubject_tab', 'primary');

        // Fetching results.
        $hits = $module->findTerms($terms);
        $module->sort('MulIdentifier');
        $columns = array('irn', 'MulIdentifier', 'MulTitle', 'MulMimeType', 'thumbnail'); 
        $results = $module->fetch('start', 0, -1, $columns);
        $records = $results->rows;
        $count = $results->count;

        // Let's process each record for additional data.
        $records = self::processRecords($records);

        $view = view('home', [
            'count' => $count,
            'records' => $records,
        ])->render();

        return $view;
    }

    /**
     * Processes each record to calculate additional values, e.g.
     * thumbnail URLs and species names.
     *
     * @param array $records
     *   All of the Multimedia records
     *
     * @return array
     *   Returns the records back as an array
     */
    public function processRecords($records)
    {
        foreach ($records as $key => $value) {
            // Calculate thumbnail URL.
            $records[$key]['thumbnail_url'] = Multimedia::fixThumbnailURL($value);

            // Calculate species name.
            $records[$key]['species_name'] = Multimedia::fixSpeciesTitle($value);
        }

        return $records;
    }
}
