<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Notifications\SlackNotification;
use App\Models\Multimedia;
use App\Models\Taxonomy;
use MongoDB\Client;
use Carbon\Carbon;
use Notification;

class SearchImport extends Command
{
    /**
     * Find total count of LinEpig records to import.
     *
     * @var int $count
     */
    protected $count;

    /**
     * MongoDB connection to LinEpig database.
     *
     * @var \MongoDB\Client $mongoLinepig
     */
    protected $mongoLinepig;

    /**
     * MongoDB search collection.
     *
     * @var mixed $searchCollection
     */
    protected $searchCollection;

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

        $this->mongoLinepig = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));

        if (App::environment() === "production") {
            $this->searchCollection = $this->mongoLinepig->linepig->search;
        } else {
            $this->searchCollection = $this->mongoLinepig->linepig->search_dev;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (App::environment() === "production") {
            Notification::route('slack', env('SLACK_HOOK'))
                        ->notify(new SlackNotification($this->getName()));
        }

        $this->findCount();

        if ($this->count > 100) {
            $this->deleteAllDocs();
        }

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
        $searchCollectionName = $this->searchCollection->getCollectionName();
        $i = 0;

        foreach ($cursor as $record) {
            if (!isset($record['AudAccessURI'])) {
                continue;
            }

            if ($i % 100 === 0) {
                Log::info("Added $i doc(s) to the $searchCollectionName collection.");
                print("Added $i doc(s) to the $searchCollectionName collection." . PHP_EOL);
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

            // Set up created and modified dates
            $searchDoc['date_created'] = $this->getMongoDate($record['AdmDateInserted']);
            $searchDoc['date_modified'] = $this->getMongoDate($record['AdmDateModified']);

            // Remove unnecessary data before combining for search
            foreach (config('emuconfig.mongodb_search_docs_fields_to_exclude') as $field) {
                unset($record[$field]);
            }
            $searchDoc['search'] = $record;

            $this->searchCollection->insertOne($searchDoc);
            $i++;
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
        $name = $this->searchCollection->getCollectionName();

        Log::info("Deleting all docs in $name collection...");
        print("Deleting all docs in $name collection..." . PHP_EOL);
        $this->searchCollection->deleteMany([]);
    }

    /**
     * Gets a Carbon date from the EMu array format.
     *
     * @param array $emuDate
     *   An array of date info from EMu
     *
     * @return \MongoDB\BSON\UTCDateTime
     *   Returns the date
     */
    public function getMongoDate(array $emuDate)
    {
        $date = Carbon::parse(implode('-', $emuDate), 'UTC');
        $utcDate = new \MongoDB\BSON\UTCDateTime($date);

        return $utcDate;
    }
}
