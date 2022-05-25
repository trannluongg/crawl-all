<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:34
 */

namespace App\Console\Commands\UK\Adzuna;

use App\Crawler\UK\Adzuna\AdzunaIndustryJobCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlAdzunaIndustryJobCommand extends Command
{
    protected $signature = 'crawl-adzuna-industry-job:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlJob = new AdzunaIndustryJobCrawl([], [
            'site' => 'adzuna.co.uk',
        ]);

        $industriesLink = DB::table('industries_uk')
            ->where('ind_site', 'adzuna.co.uk')
            ->where('ind_type', IndustryEnum::LINK_CATEGORY_HOT_JOB)
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