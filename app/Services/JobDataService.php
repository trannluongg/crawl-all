<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 27/03/2022
 * Time: 18:16
 */

namespace App\Services;

use App\Models\JobData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Workable\Crawler\Lib\CliEcho;

class JobDataService
{
    /**
     * Note:
     * @param array $dataInsert
     * User: TranLuong
     * Date: 27/03/2022
     */
    public function store(array $dataInsert = [])
    {
        $linkMd5  = array_get($dataInsert, 'link_md5');
        $checkJob = $this->__checkJobExist($linkMd5);
        if (!$checkJob)
        {
            CliEcho::infonl('-- Insert data:' . print_r($dataInsert));
            JobData::query()->create($dataInsert);
        }
        else
        {
            CliEcho::infonl('-- Data Duplicate with link md5:' . print_r($linkMd5));
        }
    }

    /**
     * Note:
     * @param $linkMd5
     * @return Builder|Model|object|null
     * User: TranLuong
     * Date: 27/03/2022
     */
    private function __checkJobExist($linkMd5)
    {
        return JobData::query()->where('link_md5', $linkMd5)->first();
    }
}