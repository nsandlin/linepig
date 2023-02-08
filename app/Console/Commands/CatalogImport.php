<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Catalog;
use MongoDB\Client;

class CatalogImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all LinEpig catalog records into the linepig MongoDB collection';

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
        $numDeletedDocs = $this->deleteAllDocs();
        $deletedMsg = "Deleted " . number_format($numDeletedDocs) . " from catalog collection." . PHP_EOL;
        Log::info($deletedMsg);
        print($deletedMsg);

        $this->addRecords();
    }

    /**
     * Retrieve and add all of the LinEpig records for the catalog linepig collection.
     *
     * @return void
     */
    public function addRecords()
    {
        // First, we need to get all of the unique catalog IRNs (catirn) from the existing
        // multimedia MongoDB collection.
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $multimediaCollection = $mongo->linepig->multimedia;
        $catalogCollection = $mongo->linepig->catalog;

        $multimediaDocs = $multimediaCollection->find([
            'catirn' => ['$exists' => true, '$ne' => ""]
        ]);
        $catalogIRNs = [];
        foreach ($multimediaDocs as $doc) {
            $catalogIRNs[] = $doc['catirn'];
        }
        $catalogIRNs = array_unique($catalogIRNs);
        $countMsg = "We have " . number_format(count($catalogIRNs)) . " records to process." . PHP_EOL;
        Log::info($countMsg);
        print($countMsg);

        // Finally, find and insert the catalog docs into MongoDB
        foreach ($catalogIRNs as $irn) {
            $catalog = new Catalog();
            $record = $catalog->getRecord($irn, true); // Setting getRecord to be import
            $insertOneResult = $catalogCollection->insertOne($record);
            $insertId = $insertOneResult->getInsertedId();
            Log::info("Added $insertId doc to the catalog collection.");
            print("Added $insertId doc to the catalog collection." . PHP_EOL);
        }

        Log::info("Done adding docs to the catalog collection.");
        print("Done adding docs to the catalog collection." . PHP_EOL);
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
        $catalogCollection = $mongo->linepig->catalog;
        $deleteResult = $catalogCollection->deleteMany([]);
        $count = $deleteResult->getDeletedCount();

        return $count;
    }
}
