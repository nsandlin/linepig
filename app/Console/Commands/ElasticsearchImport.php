<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SlackNotification;
use App\Models\Multimedia;

class ElasticsearchImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all of the MongoDB search collection docs into Elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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

        $start = now();
        $client = ClientBuilder::create()
            ->setHosts([env('ES_URL')])
            ->setApiKey(env('ES_API_KEY'))
            ->build();

        $multimediaModel = new Multimedia();
        $this->comment("Querying MongoDB for LinEpig search documents.");
        $mongoSearchDocuments = $multimediaModel->getHomepageRecords();

        if (empty($mongoSearchDocuments)) {
            $this->error("MongoDB returned no documents to index, exiting.");
            return 1;
        }

        $documentCount = number_format(count($mongoSearchDocuments));
        $this->comment("Found $documentCount MongoDB documents.");
        $this->comment("Indexing $documentCount documents.");

        foreach ($mongoSearchDocuments as $document) {
            $paramsForIndex = $this->setupDocument($document);
            $id = $paramsForIndex['id'];
            $paramsForDelete = [
                'index' => env('ES_INDEX'),
                'id' => $id,
            ];

            try {
                $client->delete($paramsForDelete);
                $client->index($paramsForIndex);
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $this->error("Error trying to index document: $id, $message");
            }
        }

        $end = $start->diffInMinutes(now());
        $this->comment("Elasticsearch indexing took $end minutes.");
        $this->comment("Done.");
        return 0;
    }

    /**
     * Sets up a MongoDB document for insertion into Elasticsearch.
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/indexing_documents.html#_single_document_indexing
     *
     * @param array $document
     *   A single MongoDB document
     *
     * @return array
     */
    public function setupDocument(array $document): array
    {
        $params = [];

        $id = (string) $document['_id'];
        unset($document['_id']);
        unset($document['search']['_id']);

        $params['index'] = env('ES_INDEX');
        $params['id'] = $id;
        $params['body'] = $document;

        return $params;
    }
}
