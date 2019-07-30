<?php
/**
 * CreateController for sitemap module
 *
 * @link https://github.com/assayer-pro/yii2-sitemap-module
 * @author Serge Larin <serge.larin@gmail.com>
 * @copyright 2015 Assayer Pro Company
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace assayerpro\sitemap\console;

use assayerpro\sitemap\Module;
use assayerpro\sitemap\Sitemap;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;


/**
 * Generate sitemap for application
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @package assayerpro\sitemap
 * @property Module $module
 */
class CreateController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'create';

    /**
     * @var string folder for sitemaps files
     */
    public $rootDir = '@webroot';

    /**
     * @var string sitemap main file name
     */
    public $sitemapFile = 'sitemap.xml';

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['rootDir', 'sitemapFile']);
    }

    /**
     * Generate sitemap.xml file
     *
     * @access public
     * @return integer
     */
    public function actionCreate()
    {
        $file = Yii::getAlias($this->rootDir.'/'.$this->sitemapFile);
        $this->stdout("Generate sitemap file.\n", Console::FG_GREEN);
        $this->stdout("Rendering sitemap...\n", Console::FG_GREEN);
        $sitemap = $this->module->getSitemap()->render();

        $this->stdout("Writing sitemap to $file\n", Console::FG_GREEN);
        file_put_contents($file, $sitemap[0]['xml']);
        $sitemap_count = count($sitemap);
        for ($i = 1; $i < $sitemap_count; $i++) {
            $file = Yii::getAlias($this->rootDir.'/'.trim($sitemap[$i]['file'], '/'));
            $this->stdout("Writing sitemap to $file\n", Console::FG_GREEN);
            file_put_contents($file, $sitemap[$i]['xml']);
        }
        $this->stdout("Done\n", Console::FG_GREEN);
        return self::EXIT_CODE_NORMAL;
    }
}
