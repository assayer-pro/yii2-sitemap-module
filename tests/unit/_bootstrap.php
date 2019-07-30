<?php

$_SERVER["SERVER_NAME"] = 'www.example.com';
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '../../../vendor/autoload.php');
require(__DIR__ . '../../../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', __DIR__);
Yii::setAlias('@webroot', __DIR__);

new \yii\console\Application([
    'id' => 'unit',
    'basePath' => __DIR__,
    'vendorPath' => __DIR__ . '/../../../vendor',
    'modules' => [
        'sitemap' => [
            'class' => '\assayerpro\sitemap\Module',
            'controllerNamespace' => '\assayerpro\sitemap\controllers',
        ],
    ],
    'components' => [
        'request' => [
            'class' => '\yii\web\Request',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => '/main/default/index',
                'article/<id:\d+>-<slug>' => '/article/default/index',
                'gallery/<id:\d+>-<slug>' => '/gallery/default/index',
                '/api-v2' => '/api/version2/index',
                '/news' => '/news/default/index',
                ['pattern' => 'sitemap-<id:\d+>', 'route' => '/sitemap/default/index', 'suffix' => '.xml'],
                ['pattern' => 'sitemap', 'route' => '/sitemap/default/index', 'suffix' => '.xml'],
            ],
            'baseUrl' => '',
            'hostInfo' => 'http://www.example.com/',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:/' . __DIR__ . '/../_data/yii2-sitemap-test.sqlite',
            'charset' => 'utf8',
        ]
    ],

]);

