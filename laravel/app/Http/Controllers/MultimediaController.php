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
    public function showMultimedia($irn) {

        // Create a Session and selecting the module we want to query.
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('irn', $irn);
        $terms->add('DetSubject_tab', 'epigynum');

        // Fetching results.
        $hits = $module->findTerms($terms);
        $columns = array(
                      'irn', 'MulIdentifier', 'MulTitle',
                      'DetSource', 'MulOtherNumber_tab', 'DetMediaRightsRef.(SummaryData)',
                      '<etaxonomy:MulMultiMediaRef_tab>.(ClaGenus,ClaSpecies,AutAuthorString)',
                      'RelRelatedMediaRef_tab.(irn, MulMimeType, MulIdentifier)',
        );
        $result = $module->fetch('start', 0, 1, $columns);
        $record = $result->rows[0];

        $view = view('multimedia', [
            'record' => $record,
        ])->render();

        return $view;
    }
}
