<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Multimedia;

class HomeController extends Controller
{
    /**
     * Handles the home page rendering control.
     *
     * @return view
     */
    public function showHome() 
    {
        $multimedia = new Multimedia();
        $records = $multimedia->getRecords();

        // We only want the "primary" records for the home page.
        foreach ($records as $key => $value) {
            if (strpos($value->keywords, "primary") === false) {
                unset($records[$key]);
            }
        }

        $count = count($records);

        $view = view('home', [
            'count' => $count,
            'records' => $records,
        ])->render();

        return $view;
    }
}
