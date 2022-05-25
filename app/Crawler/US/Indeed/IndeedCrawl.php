<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 27/03/2022
 * Time: 12:02
 */

namespace App\Crawler\US\Indeed;

use App\Services\JobDataService;
use DiDom\Document;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class IndeedCrawl extends CrawlerBase
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
                'selector' => 'css: .jobsearch-ResultsList > li',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: .jcs-JobTitle span',
                        'link'     => false,
                        'multiple' => false
                    ],
                    'href'    => [
                        'selector' => 'css: .jcs-JobTitle',
                        'link'     => false,
                        'multiple' => false
                    ],
                ],
            ],
            'link_next' => [
                'selector' => 'xpath: //ul[contains(@class, "pagination-list")]/li[last()]/a',
                'link'     => false,
                'multiple' => false,
            ]
        ];
    }

    /**
     * Note:
     * User: TranLuong
     * Date: 27/03/2022
     */
    public function run($province)
    {
        $link = "https://www.indeed.com/jobs?l=" . $province;
        $this->__getJob($link);
    }

    private function __getJob($link)
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
            //Get Job;
            foreach ($children as $child)
            {
                if ($child instanceof Node)
                {
                    if ($child->text() != "")
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
            }

            //Next page
            if (count($nextPage) > 0)
            {
                $document = new Document();
                $document->loadHtml($nextPage[0]->html());
                $tags     = $document->find('a');
                $linkNext = $tags[0]->getAttribute('href');
                if ($linkNext)
                {
                    $linkNext = 'https://' . $this->__cleanLink($site . '/' . $linkNext);
                    $this->__getJob($linkNext);
                }
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
     * Date: 27/03/2022
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
     * Date: 27/03/2022
     */
    private function __cleanLink($link)
    {
        return str_replace('//', '/', $link);
    }

    /**
     * Note:
     * User: TranLuong
     * Date: 27/03/2022
     */
    private function __insertData($dataInsert = [])
    {
        $this->jobDataService->store($dataInsert);
    }
}