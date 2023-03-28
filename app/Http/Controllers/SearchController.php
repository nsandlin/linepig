<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elastic\Elasticsearch\ClientBuilder;
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
        $searchConditions = [];
        $query = null;
        $musts = [];

        if (!empty($genus) && $genus !== 'none') {
            $genus = trim($genus);
            $searchConditions[] = $genus;
            $musts[] = ['match' => ['genus' => $genus]];
        }

        if (!empty($species) && $species !== 'none') {
            $species = trim($species);
            $searchConditions[] = $species;
            $musts[] = ['match' => ['species' => $species]];
        }

        if (!empty($keywords) && $keywords !== 'none') {
            $keywordsArray = explode("+", $keywords);

            foreach ($keywordsArray as $kw) {
                $kw = trim($kw);
                $searchConditions[] = $kw;
                $musts[] = ['match' => ['keywords' => $kw]];
            }
        }

        $client = ClientBuilder::create()
            ->setHosts([env('ES_URL')])
            ->setApiKey(env('ES_API_KEY'))
            ->build();

        if (!empty($musts)) {
            $query['bool']['must'] = $musts;
        }

        $params = [
            'index' => 'linepig.search',
            'body' => [
                "from" => 0,
                "size" => 1300,
                'query' => $query,
            ]
        ];

        $response = $client->search($params);
        $response = $response->asArray();
        $count = 0;
        $records = [];

        if ($response['hits']['hits']) {
            $count = $response['hits']['total']['value'];

            foreach ($response['hits']['hits'] as $hit) {
                $records[] = $hit['_source'];
            }

            usort($records, function ($a, $b) {
                if ($a['genus'] == $b['genus']) {
                    return $a['species'] <=> $b['species'];
                }
                return $a['genus'] <=> $b['genus'];
            });
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
