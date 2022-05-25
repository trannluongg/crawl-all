<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 15:24
 */

namespace App\Services;

use App\Models\IndustryUk;
use Workable\Crawler\Lib\CliEcho;

class IndustryUsService
{
    public function store(array $dataInsert = [])
    {
        $linkMd5  = array_get($dataInsert, 'ind_url_md5');
        $checkJob = $this->__checkJobExist($linkMd5);
        if (!$checkJob)
        {
            CliEcho::infonl('-- Insert data:' . print_r($dataInsert));
            IndustryUk::query()->create($dataInsert);
        }
        else
        {
            CliEcho::infonl('-- Data Duplicate with link md5:' . print_r($linkMd5));
        }
    }

    private function __checkJobExist($linkMd5)
    {
        return  IndustryUk::query()->where('ind_url_md5', $linkMd5)->first();
    }
}