XML Sitemap Module for Yii2
==========================

[![PHP version](https://badge.fury.io/ph/assayer-pro%2Fyii2-sitemap-module.svg)](http://badge.fury.io/ph/assayer-pro%2Fyii2-sitemap-module)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assayer-pro/yii2-sitemap-module/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assayer-pro/yii2-sitemap-module/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assayer-pro/yii2-sitemap-module/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assayer-pro/yii2-sitemap-module/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/assayer-pro/yii2-sitemap-module/badges/build.png?b=master)](https://scrutinizer-ci.com/g/assayer-pro/yii2-sitemap-module/build-status/master)

Yii2 module for automatically generating [XML Sitemap](http://www.sitemaps.org/protocol.html).

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

* Either run

```
php composer.phar require --prefer-dist "assayer-pro/yii2-sitemap-module" "*"
```

or add

```json
"assayer-pro/yii2-sitemap-module" : "*"
```

to the `require` section of your application's `composer.json` file.

* Configure the `cache` component of your application's configuration file, for example:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
]
```

* Add a new module in `modules` section of your application's configuration file, for example:

```php
'modules' => [
    'sitemap' => [
        'class' => 'assayerpro\sitemap\Module',
    ],
...
],
```

* Add confuguration for sitemap into components section:

```php
'components' => [
    'robotsTxt' => [
        'class' => 'assayerpro\sitemap\RobotsTxt',
        'userAgent' => [
            // Disallow url for all bots
            '*' => [
                'Disallow' => [
                    ['/api/default/index'],
                ],
                'Allow' => [
                    ['/api/doc/index'],
                ],
            ],
            // Block a specific image from Google Images
            'Googlebot-Image' => [
                'Disallow' => [
                    // All images on your site from Google Images
                    '/',
                    // Files of a specific file type (for example, .gif)
                    '/*.gif$',
                ],
            ],
        ],
    ],
    'sitemap' => [
        'class' => 'assayerpro\sitemap\Sitemap',
        'models' => [
            // your models
            'app\modules\news\models\News',
            // or configuration for creating a behavior
            [
                'class' => 'app\modules\news\models\News',
                'behaviors' => [
                    'sitemap' => [
                        'class' => '\assayerpro\sitemap\behaviors\SitemapBehavior',
                        'scope' => function ($model) {
                            /** @var \yii\db\ActiveQuery $model */
                            $model->select(['url', 'lastmod']);
                            $model->andWhere(['is_deleted' => 0]);
                        },
                        'dataClosure' => function ($model) {
                            /** @var self $model */
                            return [
                                'loc' => Url::to($model->url, true),
                                'lastmod' => strtotime($model->lastmod),
                                'changefreq' => \assayerpro\sitemap\Sitemap::DAILY,
                                'priority' => 0.8
                            ];
                        }
                    ],
                ],
            ],
        ],
        'urls'=> [
            // your additional urls
            [
                'loc' => ['/news/default/index'],
                'changefreq' => \assayerpro\sitemap\Sitemap::DAILY,
                'priority' => 0.8,
                'news' => [
                    'publication'   => [
                        'name'          => 'Example Blog',
                        'language'      => 'en',
                    ],
                    'access'            => 'Subscription',
                    'genres'            => 'Blog, UserGenerated',
                    'publication_date'  => 'YYYY-MM-DDThh:mm:ssTZD',
                    'title'             => 'Example Title',
                    'keywords'          => 'example, keywords, comma-separated',
                    'stock_tickers'     => 'NASDAQ:A, NASDAQ:B',
                ],
                'images' => [
                    [
                        'loc'           => 'http://example.com/image.jpg',
                        'caption'       => 'This is an example of a caption of an image',
                        'geo_location'  => 'City, State',
                        'title'         => 'Example image',
                        'license'       => 'http://example.com/license',
                    ],
                ],
            ],
        ],
        'enableGzip' => true, // default is false
        'cacheExpire' => 1, // 1 second. Default is 24 hours,
        'sortByPriority' => true, // default is false
    ],
],
```

* Add behavior in the AR models, for example:

```php
use assayerpro\sitemap\behaviors\SitemapBehavior;

public function behaviors()
{
    return [
        'sitemap' => [
            'class' => SitemapBehavior::className(),
            'scope' => function ($model) {
                /** @var \yii\db\ActiveQuery $model */
                $model->select(['url', 'lastmod']);
                $model->andWhere(['is_deleted' => 0]);
            },
            'dataClosure' => function ($model) {
                /** @var self $model */
                return [
                    'loc' => Url::to($model->url, true),
                    'lastmod' => strtotime($model->lastmod),
                    'changefreq' => Sitemap::DAILY,
                    'priority' => 0.8
                ];
            }
        ],
    ];
}
```

* Add a new rule for `urlManager` of your application's configuration file, for example:

```php
'urlManager' => [
    'rules' => [
        ['pattern' => 'sitemap-<id:\d+>', 'route' => '/sitemap/default/index', 'suffix' => '.xml'],
        ['pattern' => 'sitemap', 'route' => 'sitemap/default/index', 'suffix' => '.xml'],
    ],
],
```
Console generate sitemap
------------------------

Remove sitemap section from modules configuration.

Add console command configuration:
```php
    'controllerMap' => [
        'sitemap' => [
            'class' => 'assayerpro\sitemap\console\CreateController',
        ],
    ],
```

Add baseUrl for urlManager:
```php
     'urlManager' => [
         'baseUrl' => '',
         'hostInfo' => 'http://example.com/',
     ],
```

Run command from console:
```sh
$ ./yii sitemap/create
```

Resources
---------
* [XML Sitemap](http://www.sitemaps.org/protocol.html)

* [News Sitemap](https://support.google.com/news/publisher/answer/74288?hl=en)

* [Image sitemaps](https://support.google.com/webmasters/answer/178636?hl=en)
