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
        if (!empty($request->input('searchTerms'))) {
            return redirect()->route('searchresults', [
                'searchTerms' => $this->getTermsForURL($request->input('searchTerms'))
            ]);
        }
        else {
            return back();
        }
    }

    /**
     * Formats the search terms to be plus-delimited so that our search results page
     * can properly handle the search terms.
     *
     * @param string $searchTerms
     *   The search terms inputted by the user.
     *
     * @return string
     *   Returns a formatted string to use with the results page URL.
     */
    public function getTermsForURL($searchTerms)
    {
        $searchTermsExploded = explode(" ", $searchTerms);
        $termsForUrl = "";

        foreach ($searchTermsExploded as $term) {
            $termsForUrl .= $term . "+";
        }
        $termsForUrl = rtrim($termsForUrl, "+");

        return $termsForUrl;
    }

    /**
     * Queries the Sqlite database to retrieve Multimedia
     * records based upon the search terms entered.
     *
     * @param string $searchTerms
     *   The search terms in string format, plus-delimited
     *
     * @return Response
     */
    public function showSearchResults($searchTerms)
    {
        $searchTermsArray = explode("+", $searchTerms);
        $query = $this->getDBQuery($searchTermsArray);
        $records = $query->get();

        return view('search-results', [
            'title' => 'Search results',
            'description' => 'Search results',
            'searchTerms' => $searchTermsArray,
            'urlSearch' => $searchTerms,
            'resultsCount' => count($records),
            //'searchResults' => $query->paginate(25),
            'searchResults' => $records,
        ]);
    }

    /**
     * Gets the DB query of the Multimedia records.
     *
     * @param array $searchTerms
     *   An array of the search terms provided by the user.
     *
     * @return DB
     *   A DB object of the query.
     */
    public function getDBQuery($searchTerms)
    {
        // Searching the search table.
        $query = DB::table('search');

        // Adding each search term to where clause.
        foreach ($searchTerms as $term) {
            $query->where('search', 'like', "% $term %");
            $query->orWhere('search', 'like', "$term %");
            $query->orWhere('search', 'like', "% $term");
            $query->orWhere('search', 'like', $term);
        }

        // Ordering by Genus, Species.
        $query->orderBy('genus', 'asc');
        $query->orderBy('species', 'asc');

        return $query;
    }
}
