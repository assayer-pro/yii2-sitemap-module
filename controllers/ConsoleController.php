<?php
/**
 * CreateController for sitemap module
 *
 * @link https://github.com/assayer-pro/yii2-sitemap-module
 * @author Serge Larin <serge.larin@gmail.com>
 * @copyright 2015 Assayer Pro Company
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace assayerpro\sitemap\controllers;

use assayerpro\sitemap\Module;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\Url;


/**
 * Generate sitemap for application
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @package assayerpro\sitemap
 */

/**
 * Class ConsoleController
 *
 * @package assayerpro\sitemap\controllers
 * @property Module $module
 */
class ConsoleController extends Controller
{
    /**
     * @var string folder for sitemaps files
     */
    public $rootDir = '@webroot';

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['rootDir']);
    }

    /**
     * Generate sitemap.xml file
     *
     * @access public
     * @return integer
     */
    public function actionIndex()
    {
        $route = '/' . $this->module->id . '/web/index';

        $rootDir = Yii::getAlias('/' . trim($this->rootDir, '/'));
        $file = $rootDir . Url::to([$route], false);

        $this->stdout("Generate sitemap file.\n", Console::FG_GREEN);
        $this->stdout("Rendering sitemap...\n", Console::FG_GREEN);
        $generator = $this->module->generator;
        $sitemap = $generator->render();

        $this->stdout("Writing sitemap to $file\n", Console::FG_GREEN);
        file_put_contents($file, $sitemap[0]['xml']);
        $sitemapCount = count($sitemap);

        for ($i = 1; $i < $sitemapCount; $i++) {
            $file = $rootDir . Url::to([$route, 'id' => $i], false);
            $this->stdout("Writing sitemap to $file\n", Console::FG_GREEN);
            file_put_contents($file, $sitemap[$i]['xml']);
        }
        $this->stdout("Done\n", Console::FG_GREEN);
        return self::EXIT_CODE_NORMAL;
    }
}
