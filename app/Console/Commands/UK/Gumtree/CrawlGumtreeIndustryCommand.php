<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 13:51
 */

namespace App\Console\Commands\UK\Gumtree;

use App\Crawler\UK\Gumtree\GumtreeIndustryCrawl;
use Illuminate\Console\Command;

class CrawlGumtreeIndustryCommand extends Command
{
    protected $signature = 'crawl-gumtree-industry:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new GumtreeIndustryCrawl([], [
            'site' => 'gumtree.com',
            'url'  => 'https://www.gumtree.com/jobs'
        ]);
        $crawlCategory->run();
    }
}