<?php
/**
 * Sitemap module
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @link http://assayer.pro/
 * @copyright 2015 Assayer Pro Company
 * @license http://opensource.org/licenses/LGPL-3.0
 */


namespace assayerpro\sitemap;
use yii\base\Exception;
use yii\console\Application;

/**
 * Class Module for sitemap
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @package app\modules\webmaster
 * @property Sitemap $sitemap
 */
class Module extends \yii\base\Module
{
    const CONSOLE_CONTROLLER_MAP = [
        'create' => [
            'class' => 'assayerpro\sitemap\console\CreateController',
        ],
    ];

    /**
     * sitemap
     *
     * @var mixed
     * @access private
     */
    private $_sitemap = null;
    /**
     * The namespace that controller classes are in.
     *
     * @var string
     * @access public
     */
    public $controllerNamespace = 'assayerpro\sitemap\controllers';

    public function init()
    {
        parent::init();
        if (\yii::$app instanceof Application) {
            $this->controllerMap = self::CONSOLE_CONTROLLER_MAP;
        }
    }


    /**
     * setSitemap
     *
     * @param mixed $sitemap
     * @access public
     * @return void
     */
    public function setSitemap($sitemap) {
        $this->_sitemap = $sitemap;

    }

    /**
     * getSitemap
     *
     * @access public
     * @return Sitemap
     */
    public function getSitemap() {
        if (!empty(\yii::$app->components['sitemap'])) {
            $result = \yii::$app->get('sitemap');
        } else if (!($result = $this->_sitemap)) {
            throw new Exception('Component for sitemap module is required. Define it via application components '
                . 'or module components');
        }

        $result->module = $this;

        return $result;
    }
}
