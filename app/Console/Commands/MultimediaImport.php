<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Notification;
use App\Notifications\SlackNotification;
use App\Models\Multimedia;
use MongoDB\Client;

class MultimediaImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'multimedia:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all LinEpig multimedia records into the linepig MongoDB collection';

    /**
     * Total count of multimedia records to import
     *
     * @var int
     */
    protected $count;

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

        $this->deleteAllDocs();
        $this->findCount();
        $this->addRecords();
    }

    /**
     * Retrieve and add all of the LinEpig records for the multimedia linepig collection.
     *
     * @return void
     */
    public function addRecords()
    {
        $mongo = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $emultimedia = $mongo->emu->emultimedia;
        $cursor = $emultimedia->find(['MulMultimediaCreatorRef' => '177281']);

        $mongoLinepig = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $multimediaCollection = $mongoLinepig->linepig->multimedia;

        foreach ($cursor as $emultimediaRecord) {
            if (!isset($emultimediaRecord['AudAccessURI'])) {
                continue;
            }

            $multimedia = new Multimedia();
            $record = $multimedia->getRecord($emultimediaRecord['irn'], true);
            $insertOneResult = $multimediaCollection->insertOne($record);
            $insertId = $insertOneResult->getInsertedId();
            Log::info("Added $insertId doc to the multimedia collection.");
            print("Added $insertId doc to the multimedia collection." . PHP_EOL);
        }

        Log::info("Done adding docs to the multimedia collection.");
        print("Done adding docs to the multimedia collection." . PHP_EOL);
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
        Log::info("Deleting all docs in multimedia collection...");
        print("Deleting all docs in multimedia collection..." . PHP_EOL);
        $mongo = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $multimediaCollection = $mongo->linepig->multimedia;
        $deleteResult = $multimediaCollection->deleteMany([]);
    }
}
