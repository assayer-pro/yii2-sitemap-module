<?php
/**
 * DefaultController for sitemap module
 *
 * @link https://github.com/himiklab/yii2-sitemap-module
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @copyright 2015 Assayer Pro Company
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace assayerpro\sitemap\controllers;

use assayerpro\sitemap\Sitemap;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use assayerpro\sitemap\RobotsTxt;

/**
 * DefaultController for sitemap module
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @package assayerpro\sitemap
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'pageCache' => [
                'class' => 'yii\filters\PageCache',
                'only' => ['index', 'robots-txt'],
                'duration' => $this->module->sitemap->cacheExpire,
                'variations' => [Yii::$app->request->get('id')],
            ],
        ];
    }

    /**
     * Action for sitemap/default/index
     *
     * @access public
     * @return string
     */
    public function actionIndex($id = 0)
    {
        /**
         * @var Sitemap $component
         */
        $sitemap = $this->module->sitemap->render();
        if (empty($sitemap[$id])) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/xml');
        $result = $sitemap[$id]['xml'];
        if ($this->module->sitemap->enableGzip) {
            $result = gzencode($result);
            $headers->add('Content-Encoding', 'gzip');
            $headers->add('Content-Length', strlen($result));
        }
        return $result;
    }

    /**
     * Action for sitemap/default/robot-txt
     *
     * @access public
     * @return string
     */
    public function actionRobotsTxt()
    {
        $robotsTxt = empty(Yii::$app->components['robotsTxt']) ? new RobotsTxt() : Yii::$app->robotsTxt;
        $robotsTxt->sitemap = Yii::$app->urlManager->createAbsoluteUrl(
            empty($robotsTxt->sitemap) ? [$this->module->id.'/'.$this->id.'/index'] : $robotsTxt->sitemap
        );
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/plain');
        return $robotsTxt->render();
    }
}
