<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 14:35
 */

namespace App\Crawler\UK\CvLibrary;

use App\Enum\IndustryEnum;
use App\Services\IndustryUsService;
use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;
use Illuminate\Support\Facades\DB;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class CvLibraryIndustryJobCrawl extends CrawlerBase
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
            'jobs'        => [
                'selector' => 'css: #searchResults li.results__item',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: .job__title a',
                        'link'     => false,
                        'multiple' => false
                    ]
                ],
            ],
            'also_search' => [
                'selector' => 'css: .page-aside ol li a',
                'link'     => false,
                'multiple' => true
            ],
            'link_next'   => [
                'selector' => 'css: .pagination__next',
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

            $this->__getAlsoSearch($site, $options);

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
     * @param string $site
     * @param array $options
     * User: TranLuong
     * Date: 23/05/2022
     */
    private function __getAlsoSearch(string $site = '', array $options = [])
    {
        $selector           = $this->__configSelector();
        $selectorAlsoSearch = $selector['also_search'];
        $childrenAlsoSearch = $this->findElement($selectorAlsoSearch);

        $meta       = array_get($options, 'meta');
        $industryId = array_get($options, 'industry_id');
        if (count($childrenAlsoSearch) > 0 && $meta == null)
        {
            $dataAlso = [];
            foreach ($childrenAlsoSearch as $alsoSearch)
            {
                $title = $alsoSearch->text();
                $href  = $alsoSearch->attribute('href');

                if ($title != '' && $href != '')
                {
                    $link       = 'https://' . $this->__cleanLink($site . '/' . $href);
                    $dataAlso[] = [
                        'text' => $title,
                        'link' => $link
                    ];
                }
            }

            if (count($dataAlso) > 0)
            {
                DB::table('industries_uk')
                    ->where('id', $industryId)
                    ->update([
                        'ind_meta' => json_encode($dataAlso)
                    ]);
                CliEcho::warningnl('Update also search');
            }
        }
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