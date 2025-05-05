<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BOLDImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bold:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports BOLD species list into the bold linepig MongoDB collection';

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
        $client = new \Goutte\Client();
        $crawler = $client->request('GET', config('emuconfig.BOLD_import_url'));
        $resultsTable = $crawler->filter('#resultsTable');
        $trs = $resultsTable->filter('tr');
        $tds = $trs->filter('td:nth-of-type(2)');
        $genusSpeciesList = [];

        foreach ($tds as $td) {
            $genusSpecies = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $td->textContent);
            $doc['genus_species'] = $genusSpecies;
            $genusSpeciesList[] = $doc;
        }

        // Clear out the bold collection
        $mongo = new \MongoDB\Client(env('MONGO_LINEPIG_CONN'), [], config('emuconfig.mongodb_conn_options'));
        $boldCollection = $mongo->linepig->bold;
        $deleteResult = $boldCollection->deleteMany([]);

        // Add all new bold docs
        array_shift($genusSpeciesList);
        $insertManyResult = $boldCollection->insertMany($genusSpeciesList);
        printf("Inserted %d document(s)\n", $insertManyResult->getInsertedCount());
    }
}
