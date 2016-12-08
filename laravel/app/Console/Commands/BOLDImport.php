<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Goutte\Client;

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
    protected $description = 'Imports BOLD species list into the local DB.';

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
        $client = new Client();
        $crawler = $client->request('GET', config('emuconfig.BOLD_import_url'));
        $genusSpeciesList = $crawler->filter('img.metaMarker')->each(function ($node) {
            return $node->attr('filter-identifier');
        });

        $this->createBOLDTable();
        $this->insertRecords($genusSpeciesList);
    }

    /**
     * Create the BOLD table.
     *
     * @return void
     */
    public function createBOLDTable()
    {
        Log::info("Dropping BOLD table...");
        print("Dropping BOLD table..." . PHP_EOL);
        DB::statement('DROP TABLE IF EXISTS bold');

        Log::info("Creating BOLD table...");
        print("Creating BOLD table..." . PHP_EOL);
        DB::statement(
            'CREATE TABLE IF NOT EXISTS bold (
                genus_species TEXT NOT NULL
            )'
        );
    }

    /**
     * Insert all Genus/Species into the BOLD table.
     *
     * @param array $genusSpeciesList
     *   An array of the Genus/Species list.
     *
     * @return void
     */
    public function insertRecords($genusSpeciesList)
    {
        foreach ($genusSpeciesList as $gs) {
            DB::insert('INSERT INTO bold (genus_species) VALUES (?)', [$gs]);
        }

        Log::info("Done adding records to the BOLD table.");
        print("Done adding records to the BOLD table." . PHP_EOL);
    }
}
