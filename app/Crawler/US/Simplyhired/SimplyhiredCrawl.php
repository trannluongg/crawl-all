<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 27/03/2022
 * Time: 18:05
 */

namespace App\Crawler\US\Simplyhired;

use App\Services\JobDataService;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;
use function App\Crawler\Simplyhired\mb_strtolower;

class SimplyhiredCrawl extends CrawlerBase
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
                'selector' => 'css: #job-list li',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: .SerpJob-link.card-link',
                        'link'     => false,
                        'multiple' => false
                    ]
                ],
            ],
            'link_next' => [
                'selector' => 'css: .Pagination-link.next-pagination',
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
        $link = "https://www.simplyhired.com/search?l=" . mb_strtolower($province);
        $this->__getJob($link);
    }

    /**
     * Note:
     * @param $link
     * User: TranLuong
     * Date: 28/03/2022
     */
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
            list($css, $selector) = explode(": ", $selectorHeading, 2);

            //Get Job
            foreach ($children as $child)
            {
                if ($child instanceof Node)
                {
                    if ($child->text() != '')
                    {
                        $elementTitle = $child->find([$css, $selector]);
                        $title        = $elementTitle->text();
                        $href         = $elementTitle->attribute('href');
                        if ($title != '' && $href != '')
                        {
                            $linkJob       = 'https://' . $this->__cleanLink($site . '/' . $href);
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
            list($cssNextPage, $selectorNextPage) = explode(": ", $nextPageItem['selector'], 2);
            $elementNext = $nextPage[0]->find([$cssNextPage, $selectorNextPage]);
            $linkNext    = $elementNext->attribute('href');
            if ($linkNext)
            {
                $linkNext = 'https://' . $this->__cleanLink($site . '/' . $linkNext);
                $this->__getJob($linkNext);
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