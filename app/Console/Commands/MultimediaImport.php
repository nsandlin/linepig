<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
    protected $description = 'Imports all LinEpig multimedia documents into the linepig MongoDB collection';

    /**
     * Total count of multimedia documents to import
     *
     * @var int
     */
    protected $count;

    /**
     * MongoDB client for LinEpig
     *
     * @var \MongoDB\Client
     */
    protected $mongoLinEpig;

    /**
     * MongoDB client for EMu
     *
     * @var \MongoDB\Client
     */
    protected $mongoEMu;

    /**
     * MongoDB multimedia collection name
     *
     * @var \MongoDB\Collection
     */
    protected $multimediaCollection;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('America/Chicago');

        // Set the MongoDB Clients
        $this->mongoLinEpig = new Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $this->mongoEMu = new Client(env('MONGO_EMU_CONN'), [], config('emuconfig.mongodb_conn_options'));

        // Set the multimedia collection name
        $environment = App::environment();
        switch ($environment) {
            case 'production':
                $this->multimediaCollection = $this->mongoLinEpig->linepig->multimedia;
                break;
            default:
                $this->multimediaCollection = $this->mongoLinEpig->linepig->multimedia_dev;
                break;
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (App::environment('production')) {
            Notification::route('slack', env('SLACK_HOOK'))
                    ->notify(new SlackNotification($this->getName()));
        }

        $this->findCount();
        if ($this->count < 1000) {
            Log::info('Not enough records in MongoDB to add to multimedia collection, exiting.');
            print('Not enough records in MongoDB to add to multimedia collection, exiting.' . PHP_EOL);
            return;
        }
        Log::info('Found ' . number_format($this->count) . ' multimedia to import, proceeding.');
        print('Found ' . number_format($this->count) . ' multimedia to import, proceeding.' . PHP_EOL);

        $this->deleteAllDocs();
        $docs = $this->getEMuDocs();
        $this->addDocs($docs);
    }

    /**
     * Get all LinEpig multimedia documents from the emu MongoDB database.
     *
     * @return array
     *   Returns an array of MongoDB docs
     */
    public function getEMuDocs(): array
    {
        $emultimedia = $this->mongoEMu->emu->emultimedia;
        $cursor = $emultimedia->find(['MulMultimediaCreatorRef' => '177281']);
        $docs = [];
        $i = 0;

        foreach ($cursor as $emultimediaRecord) {
            if (!isset($emultimediaRecord['AudAccessURI'])) {
                continue;
            }

            $multimedia = new Multimedia();
            $doc = $multimedia->getRecord($emultimediaRecord['irn'], true);
            if ($doc) {
                $docs[] = $doc;
            }

            $i++;
            if ($i % 100 == 0) {
                $message = number_format($i) . " documents retrieved.";
                Log::info($message);
                print($message . PHP_EOL);
            }
        }

        Log::info("Done retrieving documents.");
        print("Done retrieving documents." . PHP_EOL);

        return $docs;
    }

    /**
     * Add all of the LinEpig documents for the multimedia linepig collection.
     *
     * @return void
     */
    public function addDocs(array $documents)
    {
        $insertManyResult = $this->multimediaCollection->insertMany($documents);
        $count = number_format($insertManyResult->getInsertedCount());

        Log::info("Added $count documents to the multimedia collection.");
        print("Added $count documents to the multimedia collection." . PHP_EOL);
    }

    /**
     * Finds total count of documents to retrieve.
     *
     * @return void
     */
    public function findCount()
    {
        $emultimedia = $this->mongoEMu->emu->emultimedia;
        $this->count = $emultimedia->count(['MulMultimediaCreatorRef' => '177281']);

        $message = "We have " . number_format($this->count) . " documents to process." . PHP_EOL;
        Log::info($message);
        print($message . PHP_EOL);
    }

    /**
     * Deletes all MongoDB docs so we can import fresh.
     *
     * @return void
     */
    public function deleteAllDocs()
    {
        Log::info("Deleting all documents in multimedia collection.");
        print("Deleting all documents in multimedia collection." . PHP_EOL);

        $deleteResult = $this->multimediaCollection->deleteMany([]);
        $message = number_format($deleteResult->getDeletedCount()) .
                " documents deleted from the multimedia collection.";

        Log::info($message);
        print($message . PHP_EOL);
    }
}
