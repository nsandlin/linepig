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
    public function showHome() 
    {
        $multimedia = new Multimedia();
        $records = $multimedia->getRecords();
        $count = $multimedia->getCount();

        $view = view('home', [
            'count' => $count,
            'records' => $records,
        ])->render();

        return $view;
    }
}
