<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:35
 */

namespace App\Crawler\UK\Gumtree;

use App\Enum\IndustryEnum;
use App\Services\IndustryUsService;
use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class GumtreeIndustryJobCrawl extends CrawlerBase
{
    private $params;
    private $industryUkService;

    public function __construct($config = [], $params = [])
    {
        parent::__construct($config);
        $this->params = $params;
        $this->industryUkService = new IndustryUsService();
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
                'selector' => 'css: .list-listing-maxi li',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: .listing-title',
                        'link'     => false,
                        'multiple' => false
                    ],
                    'href'    => [
                        'selector' => 'css: .listing-link',
                        'link'     => false,
                        'multiple' => false
                    ]
                ]
            ],
            'link_next' => [
                'selector' => 'css: .pagination-next > a',
                'link'     => false,
                'multiple' => false,
            ]
        ];
    }

    /**
     * Note:
     * User: TranLuong
     * Date: 23/05/2022
     */
    public function run(string $url = '', array $options = [])
    {
        $this->__getJobs($url, $options);
    }

    /**
     * Note:
     * @param string $url
     * @param array $options
     * User: TranLuong
     * Date: 23/05/2022
     * @throws InvalidSelectorException
     */
    private function __getJobs(string $url = '', array $options = [])
    {
        $this->__visitUrl($url);
        $site = array_get($this->params, 'site');

        $selector     = $this->__configSelector();
        $selectorItem = $selector['jobs'];
        $nextPageItem = $selector['link_next'];
        $nextPage     = $this->findElement($nextPageItem);
        $children     = $this->findElement($selectorItem);

        $selectorHeading = $selectorItem['child']['heading']['selector'];
        $selectorHref    = $selectorItem['child']['href']['selector'];
        list($css, $selector) = explode(": ", $selectorHeading, 2);
        list($cssHref, $selectorHref) = explode(": ", $selectorHref, 2);

        foreach ($children as $child)
        {
            try
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
                            'ind_industry_id' => array_get($options, 'industry_id'),
                            'ind_name'        => $title,
                            'ind_url'         => $linkJob,
                            'ind_url_md5'     => md5($linkJob),
                            'ind_site'        => $site,
                            'ind_type'        => IndustryEnum::LINK_CATEGORY_JOB
                        ];
                        $this->__insertJob($dataInsert);
                    }
                }
            } catch (\Exception $exception)
            {
                continue;
            }
        }
        //Next page
        if (count($nextPage) > 0)
        {
            $this->__getLinkNext($nextPage, $options, $site);
        }
    }

    /**
     * Note:
     * @param array $dataInsert
     * User: TranLuong
     * Date: 23/05/2022
     */
    private function __insertJob(array $dataInsert = [])
    {
        $this->industryUkService->store($dataInsert);
    }

    /**
     * Note:
     * @param array $nextPage
     * @param array $options
     * @param string $site
     * User: TranLuong
     * Date: 23/05/2022
     * @throws InvalidSelectorException
     */
    private function __getLinkNext(array $nextPage = [], array $options = [], string $site = '')
    {
        $document = new Document();
        $document->loadHtml($nextPage[0]->html());
        $tags     = $document->find('a');
        $linkNext = $tags[0]->getAttribute('href');
        if ($linkNext)
        {
            $linkNext = 'https://' . $this->__cleanLink($site . '/' . $linkNext);
            $this->__getJobs($linkNext, $options);
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
}