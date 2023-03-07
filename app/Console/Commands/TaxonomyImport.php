<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Notification;
use App\Notifications\SlackNotification;
use App\Models\Taxonomy;
use MongoDB\Client;

class TaxonomyImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taxonomy:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all LinEpig taxonomy records into the linepig MongoDB collection';

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
     * @return int
     */
    public function handle()
    {
        Notification::route('slack', env('SLACK_HOOK'))
                    ->notify(new SlackNotification($this->getName()));

        $numDeletedDocs = $this->deleteAllDocs();
        $deletedMsg = "Deleted " . number_format($numDeletedDocs) . " from taxonomy collection." . PHP_EOL;
        Log::info($deletedMsg);
        print($deletedMsg);

        $this->addRecords();
    }

    /**
     * Retrieve and add all of the LinEpig records for the taxonomy linepig collection.
     *
     * @return void
     */
    public function addRecords()
    {
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $multimediaCollection = $mongo->linepig->multimedia;
        $taxonomyCollection = $mongo->linepig->taxonomy;

        // Get all of the multimedia docs, so we can get all of the 'taxonomy_irn' values.
        $taxonomyIRNs = [];
        $multimediaDocs = $multimediaCollection->find([
            'taxonomy_irn' => ['$exists' => true, '$ne' => ""]
        ]);
        foreach ($multimediaDocs as $multimedia) {
            $taxonomyIRNs[] = $multimedia['taxonomy_irn'];
        }
        $taxonomyIRNs = array_unique($taxonomyIRNs);
        $countMsg = "We have " . number_format(count($taxonomyIRNs)) . " records to process." . PHP_EOL;
        Log::info($countMsg);
        print($countMsg);

        // Insert taxonomy docs into MongoDB.
        foreach ($taxonomyIRNs as $irn) {
            $taxonomy = new Taxonomy();
            $record = $taxonomy->getRecord($irn);
            $insertOneResult = $taxonomyCollection->insertOne($record);
            $insertId = $insertOneResult->getInsertedId();
            Log::info("Added $insertId doc to the taxonomy collection.");
            print("Added $insertId doc to the taxonomy collection." . PHP_EOL);
        }

        Log::info("Done adding docs to the taxonomy collection.");
        print("Done adding docs to the taxonomy collection." . PHP_EOL);
    }

    /**
     * Deletes all MongoDB docs so we can import fresh.
     *
     * @return int
     *   number of deleted documents
     */
    public function deleteAllDocs(): int
    {
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $taxonomyCollection = $mongo->linepig->taxonomy;
        $deleteResult = $taxonomyCollection->deleteMany([]);
        $count = $deleteResult->getDeletedCount();

        return $count;
    }
}
