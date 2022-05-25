<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 28/03/2022
 * Time: 13:55
 */

namespace App\Console\Commands\US;

use App\Crawler\US\Talent\TalentCrawl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrawlJobSiteTalentCommand extends Command
{
    protected $signature = 'crawl-site:talent-job';
    protected $description = 'Crawl job';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $options = [
            'site' => 'talent.com'
        ];
        $crawl = new TalentCrawl([], $options);

        $provinces = DB::table('us_locations')
            ->where('type_text', 'province')
            ->get();

        foreach ($provinces as $province)
        {
            $crawl->run($province->name_code);
        }
    }
}