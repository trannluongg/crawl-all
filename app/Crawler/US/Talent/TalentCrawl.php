<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 28/03/2022
 * Time: 13:57
 */

namespace App\Crawler\US\Talent;

use App\Services\JobDataService;
use DiDom\Document;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class TalentCrawl extends CrawlerBase
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
     * @return array[]
     * User: TranLuong
     * Date: 28/03/2022
     */
    private function __configSelector(): array
    {
        return [
            'jobs'      => [
                'selector' => 'css: #nv-jobs > .card.card__job',
                'link'     => false,
                'multiple' => true,
                'child'    => [
                    'heading' => [
                        'selector' => 'css: .card__job-link',
                        'link'     => false,
                        'multiple' => false
                    ]
                ],
            ],
            'link_next' => [
                'selector' => 'xpath: //div[contains(@class, "pagination")]/a[last()]',
                'link'     => false,
                'multiple' => false,
            ]
        ];
    }

    /**
     * Note:
     * @param $province
     * User: TranLuong
     * Date: 28/03/2022
     */
    public function run($province)
    {
        $link = "https://www.talent.com/jobs?k=&l=" . $province . "&radius=30";
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

            list($cssTitle, $selectorTitle) = explode(": ", $selectorHeading, 2);

            //Get Job
            foreach ($children as $child)
            {
                if ($child instanceof Node)
                {
                    $elementTitle = $child->find([$cssTitle, $selectorTitle]);
                    $title        = $elementTitle->text();
                    $id           = $child->attribute('data-id');
                    if ($title != '' && $id != '')
                    {
                        $linkJob    = 'https://' . $this->__cleanLink($site . '/view?id=' . $id . '&context=serp');
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
                $document = new Document();
                $document->loadHtml($nextPage[0]->html());
                $tagA = $document->find('a');
                if (count($tagA)) $tagA = $tagA[0];
                $linkNext = $tagA->getAttribute('data-href');
                $style    = $tagA->hasAttribute('style');
                if ($linkNext && !$style)
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