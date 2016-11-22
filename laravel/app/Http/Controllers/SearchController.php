<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $records = DB::select('SELECT * FROM search');
        $keywords = $this->getKeywords($records);

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
            $genus = trim($request->input('genus'));
        } else {
            $genus = "none";
        }
        if (!empty($request->input('species'))) {
            $species = trim($request->input('species'));
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
            'searchConditions' => $this->searchConditions,
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

        // Keywords searching.
        if ($keywords !== "none") {
            $searchValues = explode("+", $keywords);

            foreach ($searchValues as $value) {
                $value = trim($value);

                $query->where(function($q) use ($value) {
                    $q->orWhere('keywords', 'like', "% $value %");
                    $q->orWhere('keywords', 'like', "$value %");
                    $q->orWhere('keywords', 'like', "% $value");
                    $q->orWhere('keywords', 'like', $value);
                });

                $this->searchConditions[] = $value;
            }
        }

        // Genus searching.
        if ($genus !== "none") {
            $query->where('genus', '=', trim($genus));
            $this->searchConditions[] = trim($genus);
        }

        // Species searching.
        if ($species !== "none") {
            $query->where('species', '=', trim($species));
            $this->searchConditions[] = trim($species);
        }

        // Ordering by Genus, Species.
        $query->orderBy('genus', 'asc');
        $query->orderBy('species', 'asc');

        return $query;
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
        $keywords = array();

        foreach ($records as $record) {
            $chunks = explode(" | ", $record->keywords);
            
            foreach ($chunks as $chunk) {
                if (!empty($chunk) &&
                    $chunk !== 'primary' &&
                    $chunk !== 'Philippines Natural History') {

                    $keywords[] = $chunk;
                }
            }
        }

        $keywords = array_unique($keywords);

        return $keywords;
    }
}
