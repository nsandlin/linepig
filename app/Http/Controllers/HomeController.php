<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Models\Multimedia;

class HomeController extends Controller
{
    /**
     * Handles the home page rendering control.
     *
     * @return view
     */
    public function showHome(Request $request) 
    {
        $records = Cache::remember('homepage_records', config('emuconfig.cache_ttl'), function () {
            $multimedia = new Multimedia();
            return $multimedia->getHomepageRecords();
        });

        $multimedia = new Multimedia();
        $recentRecords = $multimedia->getMostRecentRecords();

        // We only want the "primary" records for the home page.
        foreach ($records as $key => $value) {
            if (empty($value['keywords'])) {
                unset($records[$key]);
            }

            $keywords = (array) $value['keywords'];

            foreach ($keywords as $k => $v) {
                $keywords[$k] = strtolower($v);
            }

            if (!in_array("primary", $keywords)) {
                unset($records[$key]);
            }
        }
        $records = collect($records);
        $count = count($records);

        // Set up pagination
        $perPage = config('emuconfig.homepage_pagination_per_page');
        $currentPage = $request->input('page', 1);
        $paginator = new LengthAwarePaginator(
            $records->forPage($currentPage, $perPage),
            $count,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $view = view('home', [
            'count' => $count,
            'records' => $paginator,
            'recent_records' => $recentRecords,
        ]);

        return $view;
    }
}
