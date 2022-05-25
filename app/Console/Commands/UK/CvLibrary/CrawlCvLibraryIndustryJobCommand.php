<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:34
 */

namespace App\Console\Commands\UK\CvLibrary;

use App\Crawler\UK\CvLibrary\CvLibraryIndustryJobCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlCvLibraryIndustryJobCommand extends Command
{
    protected $signature = 'crawl-cv-library-industry-job:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlJob = new CvLibraryIndustryJobCrawl([], [
            'site' => 'cv-library.co.uk',
        ]);

        $industriesLink = DB::table('industries_uk')
            ->where('ind_site', 'cv-library.co.uk')
            ->where('ind_type', IndustryEnum::LINK_CATEGORY)
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