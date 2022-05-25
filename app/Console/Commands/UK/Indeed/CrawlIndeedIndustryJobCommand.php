<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:34
 */

namespace App\Console\Commands\UK\Indeed;

use App\Crawler\UK\Indeed\IndeedIndustryJobCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlIndeedIndustryJobCommand extends Command
{
    protected $signature = 'crawl-indeed-industry-job:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlJob = new IndeedIndustryJobCrawl([], [
            'site' => 'uk.indeed.com',
        ]);

        $industriesLink = DB::table('industries_uk')
            ->where('ind_site', 'uk.indeed.com')
            ->where('ind_type', IndustryEnum::LINK_CATEGORY_HOT)
            ->get(['id', 'ind_url', 'ind_meta']);

        foreach ($industriesLink as $item)
        {
            $crawlJob->run($item->ind_url, [
                'industry_id' => $item->id,
                'meta'        => $item->ind_meta
            ]);
        }
    }
}