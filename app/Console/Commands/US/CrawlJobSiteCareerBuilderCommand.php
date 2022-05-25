<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 28/03/2022
 * Time: 13:55
 */

namespace App\Console\Commands\US;

use App\Crawler\US\Careerbuilder\CareerbuilderCrawl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlJobSiteCareerBuilderCommand extends Command
{
    protected $signature = 'crawl-site:careerbuilder-job';
    protected $description = 'Crawl job';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $options = [
            'site' => 'careerbuilder.com'
        ];
        $crawl = new CareerbuilderCrawl([], $options);

        $provinces = DB::table('us_locations')
            ->where('type_text', 'province')
            ->where('id', '>', 10) //KS
            ->get();

        foreach ($provinces as $province)
        {
            $crawl->run($province->name_code);
        }
    }
}