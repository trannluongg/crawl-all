<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:35
 */

namespace App\Crawler\UK\Reed;

use App\Enum\IndustryEnum;
use App\Services\IndustryUsService;
use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;
use Illuminate\Support\Facades\DB;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class ReedIndustryJobCrawl extends CrawlerBase
{
    private $params;
    private $industryUkService;

    public function __construct($config = [], $params = [])
    {
        parent::__construct($config);
        $this->params            = $params;
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
                'selector' => 'css: #server-results .job-result-card',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: .job-result-heading__title a',
                        'link'     => false,
                        'multiple' => false
                    ],
                ],
            ],
            'link_next' => [
                'selector' => 'css: #nextPage',
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
     */
    private function __getJobs(string $url = '', array $options = [])
    {
        try
        {
            $this->__visitUrl($url);
            $site         = array_get($this->params, 'site');
            $selector     = $this->__configSelector();
            $selectorItem = $selector['jobs'];
            $nextPageItem = $selector['link_next'];

            $nextPage = $this->findElement($nextPageItem);
            $children = $this->findElement($selectorItem);

            $selectorHeading = $selectorItem['child']['heading']['selector'];
            list($css, $selector) = explode(": ", $selectorHeading, 2);

            foreach ($children as $child)
            {
                if ($child instanceof Node)
                {
                    if ($child->text() != "")
                    {
                        $elementTitle = $child->find([$css, $selector]);
                        $title        = $elementTitle->text();
                        $href         = $elementTitle->attribute('href');
                        if ($title != '' && $href != '')
                        {
                            $link       = 'https://' . $this->__cleanLink($site . '/' . $href);
                            $dataInsert = [
                                'ind_industry_id' => array_get($options, 'industry_id'),
                                'ind_name'        => $title,
                                'ind_url'         => $link,
                                'ind_url_md5'     => md5($link),
                                'ind_site'        => $site,
                                'ind_type'        => IndustryEnum::LINK_CATEGORY_JOB,
                            ];
                            $this->__insertJob($dataInsert);
                        }
                    }
                }
            }

            if (count($nextPage) > 0)
            {
                $this->__getLinkNext($nextPage, $options, $site);
            }
        } catch (\Exception $exception)
        {
            CliEcho::errornl($exception->getMessage());
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
            $linkNext        = 'https://' . $this->__cleanLink($site . '/' . $linkNext);
            $explodeLinkNext = explode('=', $linkNext);
            if (isset($explodeLinkNext[1]) && $explodeLinkNext[1] <= 50)
            {
                $this->__getJobs($linkNext, $options);
            }
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