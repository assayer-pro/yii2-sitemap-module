<?php
/**
 * Yii2 module for automatically generating XML Sitemap.
 *
 * @link https://github.com/himiklab/yii2-sitemap-module
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @copyright 2015 Assayer Pro Company
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 *
 * based on https://github.com/himiklab/yii2-sitemap-module
 */

namespace assayerpro\sitemap;

use Yii;
use XMLWriter;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\helpers\Url;

/**
 * Yii2 module for automatically generating XML Sitemap.
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @package assayerpro\sitemap
 */
class Sitemap extends \yii\base\Component
{
    const ALWAYS = 'always';
    const HOURLY = 'hourly';
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';
    const NEVER = 'never';
    private $schemas = [
        'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
        'xmlns:image' => 'http://www.google.com/schemas/sitemap-image/1.1',
        'xmlns:news' => 'http://www.google.com/schemas/sitemap-news/0.9',
    ];

    /** @var int Cache expiration time */
    public $cacheExpire = 86400;

    /** @var string Cache key */
    public $cacheKey = 'sitemap';

    /** @var boolean Use php's gzip compressing. */
    public $enableGzip = false;

    /** @var array Model list for sitemap */
    public $models = [];

    /** @var array Url list for sitemap */
    public $urls = [];

    /** @var int */
    public $maxSectionUrl = 20000;

    /** @var bool Sort urls by priority. Top priority urls first */
    public $sortByPriority = false;
    /**
     * Build site map.
     * @return array
     */
    public function render()
    {
        $result = Yii::$app->cache->get($this->cacheKey);
        if ($result) {
            return $result;
        }
        $urls = $this->generateUrls();
        if ($this->sortByPriority) {
            $this->sortUrlsByPriority($urls);
        }

        $parts = ceil(count($urls) / $this->maxSectionUrl);
        if ($parts > 1) {
            $xml = new XMLWriter();
            $xml->openMemory();
            $xml->startDocument('1.0', 'UTF-8');
            $xml->startElement('sitemapindex');
            $xml->writeAttribute('xmlns', $this->schemas['xmlns']);
            for ($i = 1; $i <= $parts; $i++) {
                $xml->startElement('sitemap');
                $xml->writeElement('loc', Url::to(['/sitemap/default/index', 'id' =>$i], true));
                $xml->writeElement('lastmod', static::dateToW3C(time()));
                $xml->endElement();
                $result[$i]['file'] = Url::to(['/sitemap/default/index', 'id' =>$i], false);
            }
            $xml->endElement();
            $result[0]['xml'] = $xml->outputMemory();
            $result[0]['file'] = Url::to(['/sitemap/default/index']);
        }
        $urlItem = 0;
        for ($i = 1; $i <= $parts; $i++) {
            $xml = new XMLWriter();
            $xml->openMemory();
            $xml->startDocument('1.0', 'UTF-8');
            $xml->startElement('urlset');
            foreach ($this->schemas as $attr => $schemaUrl) {
                $xml->writeAttribute($attr, $schemaUrl);
            }
            for (; ($urlItem < $i * $this->maxSectionUrl) && ($urlItem < count($urls)); $urlItem++) {
                $xml->startElement('url');
                foreach ($urls[$urlItem] as $urlKey => $urlValue) {
                    if (is_array($urlValue)) {
                        switch ($urlKey) {
                            case 'news':
                                $namespace = 'news:';
                                $xml->startElement($namespace.$urlKey);
                                static::hashToXML($urlValue, $xml, $namespace);
                                $xml->endElement();
                                break;
                            case 'images':
                                $namespace = 'image:';
                                foreach ($urlValue as $image) {
                                    $xml->startElement($namespace.'image');
                                    static::hashToXML($image, $xml, $namespace);
                                    $xml->endElement();
                                }
                                break;
                        }
                    } else {
                        $xml->writeElement($urlKey, $urlValue);
                    }
                }
                $xml->endElement();
            }

            $xml->endElement(); // urlset
            $xml->endElement(); // document
            $result[$i]['xml'] = $xml->outputMemory();
        }

        if ($parts == 1) {
            $result[0] = $result[1];
            unset($result[1]);
        }
        Yii::$app->cache->set($this->cacheKey, $result, $this->cacheExpire);
        return $result;
    }

    /**
     * Generate url's array from properties $url and $models
     *
     * @access protected
     * @return array
     */
    protected function generateUrls()
    {
        $urls = $this->urls;

        foreach ($this->models as $modelName) {
            /** @var behaviors\SitemapBehavior $model */
            if (is_array($modelName)) {
                $model = new $modelName['class'];
                if (isset($modelName['behaviors'])) {
                    $model->attachBehaviors($modelName['behaviors']);
                }
            } else {
                $model = new $modelName;
            }
            $urls = array_merge($urls, $model->generateSiteMap());
        }
        $urls = array_map(function($item) {
            $item['loc'] = Url::to($item['loc'], true);
            if (isset($item['lastmod'])) {
                $item['lastmod'] = Sitemap::dateToW3C($item['lastmod']);
            }
            if (isset($item['images'])) {
                $item['images'] = array_map(function($image) {
                    $image['loc'] = Url::to($image['loc'], true);
                    return $image;
                }, $item['images']);
            }
            return $item;
        }, $urls);

        return $urls;
    }

    /**
     * Convert associative arrays to XML
     *
     * @param array $hash
     * @param XMLWriter $xml
     * @param string $namespace
     * @static
     * @access protected
     * @return XMLWriter
     */
    protected static function hashToXML($hash, $xml, $namespace = '')
    {
        foreach ($hash as $key => $value) {
            $xml->startElement($namespace.$key);
            if (is_array($value)) {
                static::hashToXML($value, $xml, $namespace);
            } else {
                $xml->text($value);
            }
            $xml->endElement();
        }
        return $xml;
    }
    /**
     * Convert date to W3C format
     *
     * @param mixed $date
     * @static
     * @access protected
     * @return string
     */
    public static function dateToW3C($date)
    {
        if (is_int($date)) {
            return date(DATE_W3C, $date);
        } else {
            return date(DATE_W3C, strtotime($date));
        }
    }

    /**
     * @return mixed
     */
    protected function sortUrlsByPriority(&$urls)
    {
        usort($urls, function ($urlA, $urlB) {
            if (!isset($urlA['priority'])) {
                return 1;
            }

            if (!isset($urlB['priority'])) {
                return -1;
            }

            $a = $urlA['priority'];
            $b = $urlB['priority'];
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? 1 : -1;
        });
    }
}
