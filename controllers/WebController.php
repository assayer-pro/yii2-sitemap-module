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
class WebController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'pageCache' => [
                'class' => 'yii\filters\PageCache',
                'only' => ['index'],
                'duration' => $this->module->generator->cacheExpire,
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
        $sitemap = $this->module->generator->render();
        if (empty($sitemap[$id])) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_XML;
        $result = $sitemap[$id]['xml'];
        if ($this->module->enableGzip) {
            $result = gzencode($result);
            $headers = $response->headers;
            $headers->add('Content-Encoding', 'gzip');
            $headers->add('Content-Length', strlen($result));
        }

        echo $result;
    }
}
