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

/**
 * Class Module for sitemap
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @package app\modules\webmaster
 */
class Module extends \yii\base\Module
{
    public $cacheExpire = 0;
    public $enableGzip = false;
    protected $_components = [
        'generator' => [
            'class' => \assayerpro\sitemap\Sitemap::class,
        ],
    ];
    /**
     * The namespace that controller classes are in.
     *
     * @var string
     * @access public
     */
    public $controllerNamespace = 'assayerpro\sitemap\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->generator->moduleId = $this->id;

        // custom initialization code goes here
    }
}
