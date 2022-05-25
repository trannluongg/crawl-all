<?php

namespace App\Console\Commands\US;

use App\Crawler\US\Simplyhired\SimplyhiredCrawl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlJobSiteSimplyhiredCommand extends Command
{
    protected $signature = 'crawl-site:simplyhired-job';
    protected $description = 'Crawl job';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $options = [
            'site' => 'simplyhired.com'
        ];
        $crawl = new SimplyhiredCrawl([], $options);

        $provinces = DB::table('us_locations')
            ->where('type_text', 'province')
            ->get();

        foreach ($provinces as $province)
        {
            $crawl->run($province->name_code);
        }
    }
}
