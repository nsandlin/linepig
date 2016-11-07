<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handles the home page rendering control.
     *
     * @return view
     */
    public function showHome() {

        // Create a Session and selecting the module we want to query.
        $session = new \IMuSession(env('EMU_SERVER'), env('EMU_PORT'));
        $module = new \IMuModule('emultimedia', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('MulMultimediaCreatorRef_tab', '177281');
        $terms->add('DetSubject_tab', 'epigynum');
        $terms->add('DetSubject_tab', 'primary');

        // Fetching results.
        $hits = $module->findTerms($terms);
        $module->sort('MulIdentifier');
        $columns = array('irn', 'MulIdentifier', 'MulTitle', 'MulMimeType'); 
        $results = $module->fetch('start', 0, -1, $columns);
        $records = $results->rows;
        $count = $results->count;

        $view = view('home', [
            'title' => 'LinEpig - A resource for ID of female erigonines',
            'description' => 'A visual aid for identifying the difficult spiders in family Linyphiidae.',
            'records' => $records,
        ])->render();
    }
}
