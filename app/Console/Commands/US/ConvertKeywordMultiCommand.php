<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 19/04/2022
 * Time: 21:34
 */

namespace App\Console\Commands\US;

use App\Services\JobDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertKeywordMultiCommand extends Command
{
    protected $signature = 'convert:job-keyword-multi';
    protected $description = 'Convert Keyword';

    private $jobDataService;

    public function __construct()
    {
        parent::__construct();
        $this->jobDataService = new JobDataService();
    }

    public function handle()
    {
        DB::table('keyword_multis')
            ->chunkById(1000, function ($items)
            {
                foreach ($items as $item)
                {
                    $dataInsert = [
                        'title'    => $item->name,
                        'link'     => $item->slug,
                        'link_md5' => md5($item->slug),
                        'country'  => 'us',
                        'site'     => 'keyword_multi',
                    ];

                    $this->jobDataService->store($dataInsert);
                }
            });
    }
}