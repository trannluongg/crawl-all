<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 16:24
 */

namespace App\Console\Commands\UK\Adzuna;

use App\Crawler\UK\Adzuna\AdzunaIndustryCrawl;
use Illuminate\Console\Command;

class CrawlAdzunaIndustryCommand extends Command
{
    protected $signature = 'crawl-adzuna-industry:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new AdzunaIndustryCrawl([], [
            'site' => 'adzuna.co.uk',
            'url'  => 'https://www.adzuna.co.uk/jobs/browse',
        ]);
        $crawlCategory->run();
    }
}