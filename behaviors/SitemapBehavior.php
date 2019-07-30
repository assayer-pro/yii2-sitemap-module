<?php
/**
 * Behavior for XML Sitemap Yii2 module.
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @link https://github.com/assayer-pro/yii2-sitemap-module
 * @copyright 2015 Assayer Pro Company
 * @license http://opensource.org/licenses/MIT MIT
 *
 * based on https://github.com/himiklab/yii2-sitemap-module
 */

namespace assayerpro\sitemap\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Behavior for XML Sitemap Yii2 module.
 *
 * For example:
 *
 * ```php
 * public function behaviors()
 * {
 *  return [
 *       'sitemap' => [
 *           'class' => SitemapBehavior::className(),
 *           'scope' => function ($model) {
 *               $model->select(['url', 'lastmod']);
 *               $model->andWhere(['is_deleted' => 0]);
 *           },
 *           'dataClosure' => function ($model) {
 *              return [
 *                  'loc' => yii\helpers\Url::to($model->url, true),
 *                  'lastmod' => Sitemap::dateToW3C($model->lastmod),
 *                  'changefreq' => Sitemap::DAILY,
 *                  'priority' => 0.8
 *              ];
 *          }
 *       ],
 *  ];
 * }
 * ```
 *
 * @see http://www.sitemaps.org/protocol.html
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @package assayerpro\sitemap
 */
class SitemapBehavior extends Behavior
{
    /** @var callable function generate url array for model */
    public $dataClosure;

    /** @var string|bool default time for model change frequency */
    public $defaultChangefreq = false;

    /** @var float|bool  default priority for model */
    public $defaultPriority = false;

    /** @var callable function for model filter */
    public $scope;

    /** @var int Query batch size */
    public $batchSize = 100;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!is_callable($this->dataClosure)) {
            throw new InvalidConfigException('SitemapBehavior::$dataClosure isn\'t callable.');
        }
    }

    /**
     * generate sitemap structure for model
     *
     * @access public
     * @return array
     */
    public function generateSiteMap()
    {
        $result = [];

        /** @var \yii\db\ActiveRecord $owner */
        $owner = $this->owner;
        $query = $owner::find();
        if (is_callable($this->scope)) {
            call_user_func($this->scope, $query);
        }

        foreach ($query->each($this->batchSize) as $model) {

            $urlData = call_user_func($this->dataClosure, $model);

            if (empty($urlData)) {
                continue;
            }

            if (!isset($urlData['changefreq']) && ($this->defaultChangefreq !== false)) {
                $urlData['changefreq'] = $this->defaultChangefreq;
            }

            if (!isset($urlData['priority']) && ($this->defaultPriority !== false)) {
                $urlData['priority'] = $this->defaultPriority;
            }

            $result[] = $urlData;
        }
        return $result;
    }
}
