<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Multimedia;

class SearchController extends Controller
{
    /**
     * Handles the main search page.
     *
     * @return view
     */
    public function showSearch()
    {
        $view = view('search', [
            ])->render();

        return $view;
    }

    /**
     * Handles the search page Form
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handleSearch(Request $request)
    {
        // Check if we have any search terms entered.
        if (empty($request->input('genus')) &&
            empty($request->input('species')) &&
            empty($request->input('keywords'))) {

                return back();
        }

        if (!empty($request->input('genus'))) {
            $genus = $request->input('genus');
        } else {
            $genus = "none";
        }
        if (!empty($request->input('species'))) {
            $species = $request->input('species');
        } else {
            $species = "none";
        }

        return redirect()->route('searchresults', [
            'genus' => $genus,
            'species' => $species,
            'keywords' => "none",
        ]);
    }

    /**
     * Queries the Sqlite database to retrieve Multimedia
     * records based upon the search terms entered.
     *
     * @param string $genus
     * @param string $species
     * @param string $keywords
     *
     * @return Response
     */
    public function showSearchResults($genus, $species, $keywords)
    {
        $query = $this->getDBQuery($genus, $species, $keywords);
        $records = $query->get();

        return view('search-results', [
            'title' => 'Search results',
            'description' => 'Search results',
            'resultsCount' => count($records),
            'searchResults' => $records,
        ]);
    }

    /**
     * Gets the DB query of the Multimedia records.
     *
     * @param string $genus
     * @param string $species
     * @param string $keywords
     *
     * @return DB
     *   A DB object of the query.
     */
    public function getDBQuery($genus, $species, $keywords)
    {
        // Searching the search table.
        $query = DB::table('search');

        if ($genus !== "none") {
            $query->where('genus', '=', $genus);
        }
        if ($species !== "none") {
            $query->where('species', '=', $species);
        }

        // Ordering by Genus, Species.
        $query->orderBy('genus', 'asc');
        $query->orderBy('species', 'asc');

        return $query;
    }
}
