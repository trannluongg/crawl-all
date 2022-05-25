<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 29/03/2022
 * Time: 09:39
 */

namespace App\Crawler\US\Careerbuilder;

use App\Services\JobDataService;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class CareerbuilderCrawl extends CrawlerBase
{
    private $options;
    private $jobDataService;

    public function __construct($config = [], $options = [])
    {
        $this->options        = $options;
        $this->jobDataService = new JobDataService();
        parent::__construct($config);
    }

    /**
     * Note:
     * @return array
     * User: TranLuong
     * Date: 27/03/2022
     */
    private function __configSelector(): array
    {
        return [
            'jobs'      => [
                'selector' => 'css: #jobs_collection .data-results-content-parent',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: .data-results-content-parent .data-results-title',
                        'link'     => false,
                        'multiple' => false
                    ],
                    'href'    => [
                        'selector' => 'css: .job-listing-item',
                        'link'     => false,
                        'multiple' => false
                    ],
                ],
            ],
            'link_next' => [
                'selector' => 'css: .btn-clear-blue',
                'link'     => false,
                'multiple' => false,
            ]
        ];
    }

    /**
     * Note:
     * @param $province
     * User: TranLuong
     * Date: 31/03/2022
     */
    public function run($province)
    {
        $link = "https://www.careerbuilder.com/jobs?keywords=&location=" . $province . "&page_number=1";
        $this->__getJob($link);
    }

    /**
     * Note:
     * @param $link
     * User: TranLuong
     * Date: 31/03/2022
     */
    private function __getJob($link, $page = 1)
    {
        try
        {
            $this->__visitUrl($link);
            $site            = array_get($this->options, 'site');
            $selector        = $this->__configSelector();
            $selectorItem    = $selector['jobs'];
            $nextPageItem    = $selector['link_next'];
            $nextPage        = $this->findElement($nextPageItem);
            $children        = $this->findElement($selectorItem);
            $selectorHeading = $selectorItem['child']['heading']['selector'];
            $selectorHref    = $selectorItem['child']['href']['selector'];
            list($css, $selector) = explode(": ", $selectorHeading, 2);
            list($cssHref, $selectorHref) = explode(": ", $selectorHref, 2);

            foreach ($children as $child)
            {
                if ($child instanceof Node)
                {
                    $elementTitle = $child->find([$css, $selector]);
                    $elementHref  = $child->find([$cssHref, $selectorHref]);
                    $title        = $elementTitle->text();
                    $href         = $elementHref->attribute('href');
                    if ($title != '' && $href != '')
                    {
                        $linkJob    = 'https://' . $this->__cleanLink($site . '/' . $href);
                        $dataInsert = [
                            'title'    => $title,
                            'link'     => $linkJob,
                            'link_md5' => md5($linkJob),
                            'country'  => array_get($this->options, 'country', 'us'),
                            'site'     => $site
                        ];
                        $this->__insertData($dataInsert);
                    }
                }
            }
            //Next page
            if (count($nextPage) > 0)
            {
                $pageNew  = $page + 1;
                $linkNext = str_replace('page_number=' . $page, 'page_number=' . $pageNew, $link);
                $this->__getJob($linkNext, $pageNew);
            }
        } catch (\Exception $exception)
        {
            CliEcho::errornl($exception->getMessage());
        }
    }

    /**
     * Note:
     * @param $link
     * User: TranLuong
     * Date: 31/03/2022
     */
    private function __visitUrl($link)
    {
        CliEcho::infonl('-- Visit url:' . $link);
        $this->client->visit($link);
    }

    /**
     * Note:
     * @param $link
     * @return array|string|string[]
     * User: TranLuong
     * Date: 31/03/2022
     */
    private function __cleanLink($link)
    {
        return str_replace('//', '/', $link);
    }

    /**
     * Note:
     * @param $dataInsert
     * User: TranLuong
     * Date: 31/03/2022
     */
    private function __insertData($dataInsert = [])
    {
        $this->jobDataService->store($dataInsert);
    }
}