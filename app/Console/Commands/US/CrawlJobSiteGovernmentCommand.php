<?php

namespace App\Console\Commands\US;

use App\Crawler\US\Government\GovernmentCrawl;
use Illuminate\Console\Command;

class CrawlJobSiteGovernmentCommand extends Command
{
    protected $signature = 'crawl-site:government-job';
    protected $description = 'Crawl job';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $options = [
            'site' => 'governmentjobs.com'
        ];
        $crawl = new GovernmentCrawl([], $options);
        $crawl->run();
    }
}
