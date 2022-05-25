<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 22/05/2022
 * Time: 15:01
 */

namespace App\Console\Commands\UK\Guru;

use App\Crawler\UK\Guru\GuruCrawlCategory;
use Illuminate\Console\Command;

class CrawlGuruCategoryCommand extends Command
{
    protected $signature = 'crawl-guru-category:run';
    protected $description = 'Crawl category';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $crawlCategory = new GuruCrawlCategory([], [
            'site' => 'guru.com',
            'url'  => 'https://www.guru.com/m/hire/freelancers/all-skills'
        ]);
        $crawlCategory->run();
    }
}