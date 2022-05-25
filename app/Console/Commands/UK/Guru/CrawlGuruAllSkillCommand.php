<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 22/05/2022
 * Time: 15:01
 */

namespace App\Console\Commands\UK\Guru;

use App\Crawler\UK\Guru\GuruCrawlAllSkill;
use Illuminate\Console\Command;

class CrawlGuruAllSkillCommand extends Command
{
    protected $signature = 'crawl-guru-all-skill:run';
    protected $description = 'Crawl all skill';

    public function __construct()
    {
        parent::__construct();

    }

    public function handle()
    {
        $crawlSkill = new GuruCrawlAllSkill([], [
            'site' => 'guru.com',
            'url'  => 'https://www.guru.com/m/hire/freelancers/all-skills'
        ]);
        $crawlSkill->run();
    }
}