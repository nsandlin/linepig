<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MongoDB\Client;
use App\Multimedia;

class SearchController extends Controller
{
    /**
     * @var array $searchConditions
     *   An array of conditions we used to search the database.
     */
    protected $searchConditions;

    /**
     * Handles the main search page.
     *
     * @return view
     */
    public function showSearch()
    {
        // Get available keywords for the search.
        $keywords = config('emuconfig.search_keywords');

        $view = view('search', [
                'keywords' => $keywords,
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
            $genus = ucfirst(trim($request->input('genus')));
        } else {
            $genus = "none";
        }
        if (!empty($request->input('species'))) {
            $species = strtolower(trim($request->input('species')));
        } else {
            $species = "none";
        }
        if (!empty($request->input('keywords'))) {
            $keywords = implode("+", $request->input('keywords'));
        } else {
            $keywords = "none";
        }

        return redirect()->route('searchresults', [
            'genus' => $genus,
            'species' => $species,
            'keywords' => $keywords,
        ]);
    }

    /**
     * Queries the MongoDB collection to retrieve Multimedia
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
        $filter = null;
        $searchConditions = [];

        if (!empty($genus) && $genus !== 'none') {
            $filter['genus'] = trim($genus);
            $searchConditions[] = $genus;
        }

        if (!empty($species) && $species !== 'none') {
            $filter['species'] = trim($species);
            $searchConditions[] = $species;
        }

        // TODO: Allow for searching on multiple keywords.
        if (!empty($keywords) && $keywords !== 'none') {
            $searchValues = explode("+", $keywords);

            foreach ($searchValues as $value) {
                $value = trim($value);
                $filter['keywords'] = ['$regex' => ".$value.*"];
                $searchConditions[] = $value;
            }
        }

        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $searchCollection = $mongo->linepig->search;
        $cursor = $searchCollection->find($filter);
        $count = $searchCollection->count($filter);
        $records = [];

        if ($count > 0) {
            foreach ($cursor as $record) {
                $records[] = $record;
            }
        }

        return view('search-results', [
            'title' => 'Search results',
            'description' => 'Search results',
            'resultsCount' => $count,
            'searchConditions' => $searchConditions,
            'searchResults' => $records,
        ]);
    }

    /**
     * Retrieves all available keyword options for the search form.
     *
     * @param array $records
     *   All of the search table records.
     *
     * @return array $keywords
     *   Returns an array of available keywords for the search.
     */
    public function getKeywords($records) : array
    {
        $keywords = config('emuconfig.search_keywords');

        return $keywords;
    }
}
