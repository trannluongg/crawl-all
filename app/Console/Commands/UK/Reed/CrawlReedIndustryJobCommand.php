<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:34
 */

namespace App\Console\Commands\UK\Reed;

use App\Crawler\UK\Reed\ReedIndustryJobCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlReedIndustryJobCommand extends Command
{
    protected $signature = 'crawl-reed-industry-job:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlJob = new ReedIndustryJobCrawl([], [
            'site' => 'reed.co.uk',
        ]);

        $industriesLink = DB::table('industries_uk')
            ->where('ind_site', 'reed.co.uk')
            ->where('ind_type', IndustryEnum::LINK_CATEGORY_HOT)
            ->where('id', '>', 1762)
            ->get(['id', 'ind_url']);

        foreach ($industriesLink as $item)
        {
            $crawlJob->run($item->ind_url, [
                'industry_id' => $item->id
            ]);
        }
    }
}