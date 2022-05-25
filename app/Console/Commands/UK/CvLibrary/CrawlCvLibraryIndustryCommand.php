<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 16:24
 */

namespace App\Console\Commands\UK\CvLibrary;

use App\Crawler\UK\CvLibrary\CvLibraryIndustryCrawl;
use App\Enum\IndustryEnum;
use Illuminate\Console\Command;

class CrawlCvLibraryIndustryCommand extends Command
{
    protected $signature = 'crawl-cv-library-industry:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new CvLibraryIndustryCrawl([], [
            'site' => 'cv-library.co.uk',
            'url'  => 'https://www.cv-library.co.uk/sitemap',
            'type' => IndustryEnum::LINK_CATEGORY
        ]);
        $crawlCategory->run();
    }
}