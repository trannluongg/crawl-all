<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:34
 */

namespace App\Console\Commands\UK\Gumtree;

use App\Crawler\UK\Gumtree\GumtreeIndustryJobCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlGumtreeIndustryJobCommand extends Command
{
    protected $signature = 'crawl-gumtree-industry-job:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new GumtreeIndustryJobCrawl([], [
            'site' => 'gumtree.com',
        ]);

        $industriesLink = DB::table('industries_uk')
            ->where('ind_site', 'gumtree.com')
            ->where('ind_type', IndustryEnum::LINK_CATEGORY)
            ->get(['id', 'ind_url']);

        foreach ($industriesLink as $item)
        {
            $crawlCategory->run($item->ind_url, [
                'industry_id' => $item->id
            ]);
        }
    }
}