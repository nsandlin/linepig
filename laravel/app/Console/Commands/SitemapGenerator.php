<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SitemapGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the sitemap for the website';

    /**
     * The domain name of the website.
     *
     * @var string $domain
     */
    protected $domain;

    /**
     * The URLs we need for the Sitemap.
     *
     * @var array $urls
     */
    protected $urls = array();

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->domain = config('emuconfig.website_domain');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // First, we're going to get all of the records in the search table and add them to URLs.
        // The search table SHOULD include all of the Multimedia records (pages) for the site.
        $searchRecords = DB::select('SELECT * FROM search');
        $this->addSearchRecords($searchRecords);

        // Second, we're going to get all of the other links on the site.
        $this->addOtherLinks();

        // Lastly, output the Sitemap XML file.
        $this->writeXMLSitemap();
    }

    /**
     * Process Search database records and add them to the URLs array for the Sitemap.
     *
     * @param array $searchRecords
     *   The records from the Search database.
     *
     * @return void
     */
    public function addSearchRecords(array $searchRecords) {

        foreach ($searchRecords as $record) {
            switch ($record->module) {
                case "emultimedia":
                    $this->urls[] = config('emuconfig.website_domain') .
                                    "multimedia/" .
                                    $record->irn;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Adds the other links that are on the website, via the config file.
     *
     * @return void
     */
    public function addOtherLinks()
    {
        $otherLinks = config('emuconfig.website_links');

        foreach ($otherLinks as $link) {
            $this->urls[] = $link;
        }
    }

    /**
     * Writes the URLs to XML Sitemap.
     *
     * @return void
     */
    public function writeXMLSitemap() {
        $xmlstr = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
                  . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($this->urls as $url) {
            $xmlstr .= '<url><loc>' . $url . '</loc></url>' . PHP_EOL;
        }

        $xmlstr .= '</urlset>' . PHP_EOL;

        try {
            Storage::disk('public_root')->put('sitemap.xml', $xmlstr);
        }
        catch (Exception $e) {
            print $e->getMessage();
        }
    }
}
