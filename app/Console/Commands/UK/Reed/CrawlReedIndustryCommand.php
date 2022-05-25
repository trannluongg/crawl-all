<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 16:24
 */

namespace App\Console\Commands\UK\Reed;

use App\Crawler\UK\Reed\ReedIndustryCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;

class CrawlReedIndustryCommand extends Command
{
    protected $signature = 'crawl-reed-industry:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new ReedIndustryCrawl([], [
            'site' => 'reed.co.uk',
            'url'  => 'https://www.reed.co.uk/popularjobs',
            'type' => IndustryEnum::LINK_CATEGORY
        ]);
        $crawlCategory->run();
    }
}