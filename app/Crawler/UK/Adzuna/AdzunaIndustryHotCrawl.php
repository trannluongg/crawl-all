<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 16:25
 */

namespace App\Crawler\UK\Adzuna;

use App\Enum\IndustryEnum;
use App\Models\IndustryUk;
use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;
use Illuminate\Support\Facades\DB;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class AdzunaIndustryHotCrawl extends CrawlerBase
{
    private $params;

    public function __construct($config = [], $params = [])
    {
        parent::__construct($config);
        $this->params = $params;
    }

    /**
     * Note:
     * @return array[]
     * User: TranLuong
     * Date: 22/05/2022
     */
    private function __configSelector(): array
    {
        return [
            'jobs'      => [
                'selector' => 'xpath: //section[contains(@class, "ui-main")]/div[1]/ul/li',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: a',
                        'link'     => false,
                        'multiple' => false
                    ],
                ],
            ],
            'link_job'  => [
                'selector' => 'xpath:  //section[contains(@class, "ui-main")]/a',
                'link'     => false,
                'multiple' => false,
            ],
            'link_next' => [
                'selector' => 'xpath: //div[contains(@class, "px-3 text-center")]/a[contains(text(), "next ❯")]',
                'link'     => false,
                'multiple' => false,
            ]
        ];
    }

    /**
     * Note:
     * @param $url
     * @param $id
     * User: TranLuong
     * Date: 25/05/2022
     * @throws InvalidSelectorException
     */
    public function run($url, $id)
    {
        $site = array_get($this->params, 'site');
        $this->__visitUrl($url);

        $selector = $this->__configSelector();
        $children = $this->findElement($selector['jobs']);
        $nextPage = $this->findElement($selector['link_next']);

        $selectorCategory = array_get($selector, 'jobs.child.heading.selector');
        list($cssHeading, $selectorHeading) = explode(": ", $selectorCategory, 2);

        $dataInsert = [];
        foreach ($children as $child)
        {
            if ($child instanceof Node)
            {
                $elementSkill = $child->find([$cssHeading, $selectorHeading]);
                $title        = $elementSkill->text();
                $link         = $elementSkill->attribute('href');

                $dataInsert[] = [
                    'ind_industry_id' => $id,
                    'ind_name'        => trim(str_replace('jobs', '', $title)),
                    'ind_url'         => $link,
                    'ind_url_md5'     => md5($link),
                    'ind_site'        => $site,
                    'ind_type'        => IndustryEnum::LINK_CATEGORY_HOT,
                    'created_at'      => now()->toDateTimeString(),
                    'updated_at'      => now()->toDateTimeString(),
                ];
            }
        }

        CliEcho::warningnl('Count data: ' . count($dataInsert));

        $this->__insertIndustry($dataInsert);

        $this->__getLinkJobAll($selector, $id, $site);
        if (count($nextPage) > 0)
        {
            $this->__getLinkNext($nextPage, $id);
        }
    }

    /**
     * Note:
     * @param $selector
     * @param $id
     * @param $site
     * User: TranLuong
     * Date: 25/05/2022
     */
    private function __getLinkJobAll($selector, $id, $site)
    {
        $linkJobAll = $this->findElement($selector['link_job']);
        if (count($linkJobAll) > 0)
        {
            $link = $linkJobAll[0]->attribute('href');
            $linkCurrent = IndustryUk::query()
                ->where('ind_url_md5', md5($link))
                ->first();

            if (!$linkCurrent)
            {
                $linkParent = IndustryUk::query()
                    ->where('id', $id)
                    ->first();

                IndustryUk::query()->create([
                    'ind_industry_id' => $id,
                    'ind_name'        => $linkParent->ind_name,
                    'ind_url'         => $link,
                    'ind_url_md5'     => md5($link),
                    'ind_site'        => $site,
                    'ind_type'        => IndustryEnum::LINK_CATEGORY_HOT_JOB,
                ]);

                CliEcho::warningnl('Insert LINK_CATEGORY_HOT_JOB');
            }
        }
    }

    /**
     * Note:
     * @param array $nextPage
     * @param $id
     * User: TranLuong
     * Date: 25/05/2022
     * @throws InvalidSelectorException
     */
    private function __getLinkNext(array $nextPage = [], int $id = 0)
    {
        $document = new Document();
        $document->loadHtml($nextPage[0]->html());
        $tags     = $document->find('a');
        $linkNext = $tags[0]->getAttribute('href');
        if ($linkNext)
        {
            $this->run($linkNext, $id);
        }
    }

    /**
     * Note:
     * @param array $dataInsert
     * User: TranLuong
     * Date: 23/05/2022
     */
    private function __insertIndustry(array $dataInsert = [])
    {
        if (count($dataInsert) > 0)
        {
            DB::table('industries_uk')->insert($dataInsert);
            CliEcho::successnl('Save Data Successfully');
        }
        else
        {
            CliEcho::warningnl('Empty Data');
        }
    }

    /**
     * Note:
     * @param $link
     * User: TranLuong
     * Date: 23/05/2022
     */
    private function __visitUrl(string $link = '')
    {
        CliEcho::infonl('-- Visit url:' . $link);
        $this->client->visit($link);
    }
}