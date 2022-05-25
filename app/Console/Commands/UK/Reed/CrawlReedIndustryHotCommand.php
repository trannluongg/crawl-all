<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 16:24
 */

namespace App\Console\Commands\UK\Reed;

use App\Crawler\UK\Indeed\IndeedIndustryHotCrawl;
use App\Crawler\UK\Reed\ReedIndustryHotCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlReedIndustryHotCommand extends Command
{
    protected $signature = 'crawl-reed-industry-hot:run';
    protected $description = 'Crawl category hot';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new ReedIndustryHotCrawl([], [
            'site' => 'reed.co.uk'
        ]);

        $industriesLink = DB::table('industries_uk')
            ->where('ind_site', 'reed.co.uk')
            ->where('ind_type', IndustryEnum::LINK_CATEGORY)
            ->get(['id', 'ind_url']);

        foreach ($industriesLink as $item)
        {
            $crawlCategory->run($item->ind_url, $item->id);
        }
    }
}