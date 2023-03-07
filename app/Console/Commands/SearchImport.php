<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Notification;
use App\Notifications\SlackNotification;
use App\Models\Multimedia;
use App\Models\Taxonomy;
use MongoDB\Client;

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
    protected $records;

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
        Notification::route('slack', env('SLACK_HOOK'))
                    ->notify(new SlackNotification($this->getName()));

        $this->deleteAllDocs();
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
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $emultimedia = $mongo->emu->emultimedia;
        $cursor = $emultimedia->find(['MulMultimediaCreatorRef' => '177281']);

        $mongoLinepig = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $searchCollection = $mongoLinepig->linepig->search;

        foreach ($cursor as $record) {
            if (!isset($record['AudAccessURI'])) {
                continue;
            }

            $taxonomy = new Taxonomy();
            $taxonomyIRN = $taxonomy->getTaxonomyIRN($record);
            $taxon = $taxonomy->getRecord($taxonomyIRN);

            $searchDoc = [];
            $searchDoc['irn'] = $record['irn'];
            $searchDoc['module'] = "emultimedia";
            $searchDoc['genus'] = $taxon['ClaGenus'];
            $searchDoc['species'] = $taxon['ClaSpecies'] ?? ""; // IRN 616726 is an example taxon record with no species
            $searchDoc['keywords'] = $record['DetSubject'];
            $searchDoc['title'] = $record['MulTitle'];
            $searchDoc['description'] = $record['MulDescription'];
            $searchDoc['thumbnailURL'] = Multimedia::fixThumbnailURL($record['AudAccessURI']);

            // Remove unnecessary data before combining for search
            foreach (config('emuconfig.mongodb_search_docs_fields_to_exclude') as $field) {
                unset($record[$field]);
            }
            $searchDoc['search'] = $record;

            $insertOneResult = $searchCollection->insertOne($searchDoc);
            $insertId = $insertOneResult->getInsertedId();
            Log::info("Added $insertId doc to the search collection.");
            print("Added $insertId doc to the search collection." . PHP_EOL);
        }

        Log::info("Done adding docs to the search collection.");
        print("Done adding docs to the search collection." . PHP_EOL);
    }

    /**
     * Finds total count of records to retrieve.
     *
     * @return void
     */
    public function findCount()
    {
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $emultimedia = $mongo->emu->emultimedia;
        $this->count = $emultimedia->count(['MulMultimediaCreatorRef' => '177281']);

        $message = "We have " . number_format($this->count) . " records to process." . PHP_EOL;
        Log::info($message);
        print($message);
    }

    /**
     * Deletes all MongoDB docs so we can import fresh.
     *
     * @return void
     */
    public function deleteAllDocs()
    {
        Log::info("Deleting all docs in search collection...");
        print("Deleting all docs in search collection..." . PHP_EOL);
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $searchCollection = $mongo->linepig->search;
        $deleteResult = $searchCollection->deleteMany([]);
    }
}
