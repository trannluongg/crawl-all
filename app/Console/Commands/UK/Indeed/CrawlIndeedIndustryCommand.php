<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 16:24
 */

namespace App\Console\Commands\UK\Indeed;

use App\Crawler\UK\Indeed\IndeedIndustryCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;

class CrawlIndeedIndustryCommand extends Command
{
    protected $signature = 'crawl-indeed-industry:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new IndeedIndustryCrawl([], [
            'site' => 'uk.indeed.com',
            'url'  => 'https://uk.indeed.com/browsejobs/',
            'type' => IndustryEnum::LINK_CATEGORY
        ]);
        $crawlCategory->run();
    }
}