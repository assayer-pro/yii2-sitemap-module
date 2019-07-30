<?php
namespace assayerpro\sitemap;
use yii\base\Exception;
use yii\web\Application;

class ModuleTest extends \Codeception\TestCase\Test
{
    public function testConsoleInitControllerMap() {
        $module = new Module('sitemap');
        $this->assertEquals(Module::CONSOLE_CONTROLLER_MAP, $module->controllerMap);
    }

    public function testWebInitControllerMap() {
        $consoleApp = \yii::$app;
        \yii::$app = null;
        $module = new Module('sitemap');
        \yii::$app = $consoleApp;

        $this->assertEquals([], $module->controllerMap);
    }

    public function testGetComponentFromApp() {
        $appComponent = new Sitemap();
        $module = new Module('sitemap');
        \yii::$app->set('sitemap', $appComponent);

        $component = $module->sitemap;
        $this->assertEquals($component, $appComponent);
        \yii::$app->set('sitemap', null);
    }

    public function testGetComponentFromModule() {
        $moduleComponent = new Sitemap();
        $module = new Module('sitemap', null, [
            'sitemap' => $moduleComponent,
        ]);

        $component = $module->sitemap;
        $this->assertEquals($component, $moduleComponent);
        $this->assertEquals('sitemap', $component->module->id);
    }

    public function testNoComponentException() {
        $module = new Module('sitemap');
        $this->setExpectedException(Exception::class,
            'Component for sitemap module is required. Define it via application components or module components');
        $module->sitemap;
    }
}
