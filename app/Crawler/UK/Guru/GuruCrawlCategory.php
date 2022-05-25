<?php
/**
 * Created by PhpStorm.
 * User: TranLuong
 * Date: 23/05/2022
 * Time: 08:51
 */

namespace App\Crawler\UK\Guru;

use App\Enum\GuruEnum;
use Illuminate\Support\Facades\DB;
use Openbuildings\Spiderling\Node;
use Workable\Crawler\Browsers\CrawlerBase;
use Workable\Crawler\Lib\CliEcho;

class GuruCrawlCategory extends CrawlerBase
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
            'selector' => 'css: .c-heading.c-heading--small.u-margin-bottom--xsmall',
            'link'     => false,
            'multiple' => true,
            'child'    => [
                'heading' => [
                    'selector' => 'css: .c-heading.c-heading--small.u-margin-bottom--xsmall a',
                    'link'     => false,
                    'multiple' => false
                ],
            ],
        ];
    }

    /**
     * Note:
     * @return bool
     * User: TranLuong
     * Date: 23/05/2022
     */
    public function run(): bool
    {
        $site = array_get($this->params, 'site');
        $url  = array_get($this->params, 'url');
        $this->__visitUrl($url);

        $selector = $this->__configSelector();
        $children = $this->findElement($selector);

        $selectorCategory = array_get($selector, 'child.heading.selector');
        list($css, $selector) = explode(": ", $selectorCategory, 2);

        $dataInsert = [];
        foreach ($children as $child)
        {

            if ($child instanceof Node)
            {
                $elementCategory = $child->find([$css, $selector]);
                $title           = $elementCategory->text();
                $href            = $elementCategory->attribute('href');
                $link            = 'https://' . $this->__cleanLink($site . '/' . $href);

                $dataInsert[] = [
                    'key_name'    => $title,
                    'key_url'     => $link,
                    'key_url_md5' => md5($link),
                    'key_site'    => $site,
                    'key_type'    => GuruEnum::LINK_CATEGORY,
                    'created_at'  => now()->toDateTimeString(),
                    'updated_at'  => now()->toDateTimeString(),
                ];
            }
        }
        $this->__insertCategory($dataInsert);

        return true;
    }


    /**
     * Note:
     * @param array $dataInsert
     * User: TranLuong
     * Date: 23/05/2022
     */
    private function __insertCategory(array $dataInsert = [])
    {
        if (count($dataInsert) > 0)
        {
            DB::table('keywords_uk')->insert($dataInsert);
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

    /**
     * Note:
     * @param string $link
     * @return array|string|string[]
     * User: TranLuong
     * Date: 23/05/2022
     */
    private function __cleanLink(string $link = '')
    {
        return str_replace('//', '/', $link);
    }
}