<?php

namespace App\Console\Commands\US;

use App\Crawler\US\Indeed\IndeedCrawl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlJobSiteIndeedCommand extends Command
{
    protected $signature = 'crawl-site:indeed-job';
    protected $description = 'Crawl job';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $options = [
            'site' => 'indeed.com'
        ];
        $crawl = new IndeedCrawl([], $options);

        $provinces = DB::table('us_locations')
            ->where('type_text', 'province')
            ->get();

        foreach ($provinces as $province)
        {
            $crawl->run($province->name);
        }
    }
}
