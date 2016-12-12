<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Multimedia;

class SearchImport extends Command
{
    /**
     * Find total count of LinEpig records to import.
     *
     * @var int $count
     */
    protected $count;

    /**
     * All of the LinEpig EMu records.
     *
     * @var array $records
     */
    protected $records = array();

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all LinEpig records into the local search database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('America/Chicago');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createSearchTable();
        $this->findCount();
        $this->addRecords();
    }

    /**
     * Retrieve and add all of the LinEpig records for the search database.
     * We can't use the main Multimedia function because it only includes
     * the primary records.
     *
     * @return void
     */
    public function addRecords()
    {
        // Create a Session.
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('MulMultimediaCreatorRef_tab', '177281');
        $columns = config('emuconfig.search_fields');

        // Fetching results.
        $hits = $module->findTerms($terms);
        $results = $module->fetch('start', 0, -1, $columns);
        $this->records = $results->rows;
        $i = 1;

        foreach ($this->records as $record) {

            // Process the record for insertion into search table.
            $irn = (int) $record['irn'];
            $module = "emultimedia";
            $genus = @$record['etaxonomy:MulMultiMediaRef_tab'][0]['ClaGenus'];
            $species = @$record['etaxonomy:MulMultiMediaRef_tab'][0]['ClaSpecies'];
            $keywords = @$this->combineArrayForSearch($record['DetSubject_tab']);
            $title = $record['MulTitle'];
            $description = $record['MulDescription'];
            $thumbnailURL = Multimedia::fixThumbnailURL($record);
            $searchString = $this->combineArrayForSearch($record);

            // Add record to search table.
            DB::insert(
                'INSERT INTO search (
                        irn, module, genus, species, keywords, title,
                        description, thumbnailURL, search)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$irn, $module, $genus, $species, $keywords, $title,
                 $description, $thumbnailURL, $searchString]
            );
            Log::info("Added $i records to the search table.");
            print("Added $i records to the search table." . PHP_EOL);
            $i++;
        }

        Log::info("Done adding records to the search database.");
        print("Done adding records to the search database." . PHP_EOL);
    }

    /**
     * Finds total count of records to retrieve.
     *
     * @return void
     */
    public function findCount()
    {
        // Create a Session.
        $session = new \IMuSession(config('emuconfig.emuserver'), config('emuconfig.emuport'));
        $module = new \IMuModule('emultimedia', $session);

        // Adding our search terms.
        $terms = new \IMuTerms();
        $terms->add('MulMultimediaCreatorRef_tab', '177281');

        // Fetching results.
        $hits = $module->findTerms($terms);
        $results = $module->fetch('start', 0, -1, 'irn');

        $this->count = $results->count;

        $message = "We have " . $this->count . " records to process." . PHP_EOL;
        Log::info($message);
        print($message);
    }

    /**
     * Create the search table.
     *
     * @return void
     */
    public function createSearchTable()
    {
        Log::info("Dropping search table...");
        print("Dropping search table..." . PHP_EOL);
        DB::statement('DROP TABLE IF EXISTS search');

        Log::info("Creating search table...");
        print("Creating search table..." . PHP_EOL);
        DB::statement(
            'CREATE TABLE IF NOT EXISTS search (
                irn INTEGER NOT NULL,
                module TEXT NOT NULL,
                genus TEXT,
                species TEXT,
                keywords TEXT,
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                thumbnailURL TEXT NOT NULL,
                search TEXT
            )'
        );
    }


    /**
     * Combines record array elements into one string, for DB search purposes.
     *
     * @param array $array
     *   The elements to combine.
     *
     * @return string
     *   The string of the concatenated array elements.
     */
    public function combineArrayForSearch($array)
    {
        if (!isset($array) || empty($array)) {
            return null;
        }

        $searchString = "";

        foreach ($array as $element) {
            if (!is_array($element)) {
                $searchString .= " | " . $element;
            }
            else {
                foreach ($element as $second) {
                    if (!is_array($second)) {
                        $searchString .= " | " . $second;
                    }
                    else {
                        foreach ($second as $third) {
                            if (!is_array($third)) {
                                $searchString .= " | " . $third;
                            }
                        }
                    }
                }
            }

        }

        return $searchString;
    }
}
