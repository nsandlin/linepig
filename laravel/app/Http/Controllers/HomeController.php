<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $records = Cache::remember('homepage_records', config('emuconfig.cache_ttl'), function () {
            $multimedia = new Multimedia();
            return $multimedia->getHomepageRecords();
        });

        // We only want the "primary" records for the home page.
        foreach ($records as $key => $value) {
            if (!is_array($value['keywords'])) {
                if ($value['keywords'] !== "primary") {
                    unset($records[$key]);
                    continue;
                }
            }

            if (!in_array("primary", $value['keywords'])) {
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
