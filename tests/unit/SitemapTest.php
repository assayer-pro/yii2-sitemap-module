<?php
use assayerpro\sitemap\Sitemap;
use assayerpro\tests\unit\Article;
use assayerpro\tests\unit\Gallery;

class SitemapTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $oldTimeZone;

    protected function _before()
    {
        $this->oldTimeZone = date_default_timezone_get();
        date_default_timezone_set("UTC");
    }

    protected function _after()
    {
        date_default_timezone_set($this->oldTimeZone);
    }

    // tests
    public function testSitemap()
    {
        Yii::$app->cache->flush();
        $sitemap = new Sitemap([
            'urls' => [
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
                        'publication_date'  => '2015-11-04T19:27:01TZD',
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
                [
                    'loc' => ['/main/default/index'],
                    'lastmod' => '2015-04-14 12:20',
                ],
            ]
        ]);
$expectedXml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"><url><loc>http://www.example.com/news</loc><changefreq>daily</changefreq><priority>0.8</priority><news:news><news:publication><news:name>Example Blog</news:name><news:language>en</news:language></news:publication><news:access>Subscription</news:access><news:genres>Blog, UserGenerated</news:genres><news:publication_date>2015-11-04T19:27:01TZD</news:publication_date><news:title>Example Title</news:title><news:keywords>example, keywords, comma-separated</news:keywords><news:stock_tickers>NASDAQ:A, NASDAQ:B</news:stock_tickers></news:news><image:image><image:loc>http://example.com/image.jpg</image:loc><image:caption>This is an example of a caption of an image</image:caption><image:geo_location>City, State</image:geo_location><image:title>Example image</image:title><image:license>http://example.com/license</image:license></image:image></url><url><loc>http://www.example.com/</loc><lastmod>2015-04-14T12:20:00+00:00</lastmod></url></urlset>
EOF;
        $this->assertEquals($expectedXml, $sitemap->render()[0]['xml']);
    }
    public function testSitemapIndex()
    {
        Yii::$app->cache->flush();
        $sitemap = new Sitemap([
            'maxSectionUrl' => 1,
            'urls' => [
                [
                    'loc' => ['/news/default/index'],
                    'changefreq' => \assayerpro\sitemap\Sitemap::DAILY,
                    'priority' => 0.8,
                ],
                [
                    'loc' => ['/main/default/index'],
                ]
            ]
        ]);
        $render = $sitemap->render();
        $this->assertEquals('/sitemap.xml', $render[0]['file']);
$expectedXml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>http://www.example.com/sitemap-1.xml</loc>
        <lastmod>2015-11-04T13:31:41+00:00</lastmod>
    </sitemap>
    <sitemap>
        <loc>http://www.example.com/sitemap-2.xml</loc>
        <lastmod>2015-11-04T13:31:41+00:00</lastmod>
    </sitemap>
</sitemapindex>
EOF;
        $expected = new DOMDocument;
        $expected->loadXML($expectedXml);
        $actual = new DOMDocument;
        $actual->loadXML($render[0]['xml']);
        $this->assertEqualXMLStructure($expected->firstChild, $actual->firstChild);
    }

    public function testSitemapCache()
    {
        Yii::$app->cache->flush();
        $sitemap = new Sitemap([
            'urls' => [
                [ 'loc' => '/'],
                [ 'loc' => '/api'],
            ]
        ]);
        $expectedXML = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"><url><loc>http://www.example.com/</loc></url><url><loc>http://www.example.com/api</loc></url></urlset>
EOF;
        $this->assertEquals($expectedXML,$sitemap->render()[0]['xml']);
        $sitemap->urls = [];
        $this->assertEquals($expectedXML,$sitemap->render()[0]['xml']);
    }
    public function testSitemapDatetow3c()
    {
        $this->assertEquals('2015-01-01T00:00:00+00:00', Sitemap::dateToW3C("01-01-2015"));
        $this->assertEquals('2015-11-04T14:52:47+00:00', Sitemap::dateToW3C(1446648767));
        $this->assertEquals('2015-11-04T14:53:57+00:00', Sitemap::dateToW3C("Wed Nov 4 17:53:57 MSK 2015"));
    }

    public function testSitemapModel() {
        Yii::$app->cache->flush();
        $sitemap = new Sitemap([
            'models' => [
                'assayerpro\sitemap\tests\unit\Article',
                [
                    'class' => 'assayerpro\sitemap\tests\unit\Gallery',
                    'behaviors' => [
                        'sitemap' => [
                            'class' => 'assayerpro\sitemap\behaviors\SitemapBehavior',
                            'dataClosure' => function ($model) {
                                /** @var \yii\db\ActiveQuery $model */
                                return [
                                    'loc' => $model->url,
                                    'changefreq' => \assayerpro\sitemap\Sitemap::WEEKLY,
                                    'priority' => 0.8
                                ];
                            }
                        ],
                     ],
                ],
            ]
        ]);
        $expectedXML = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"><url><loc>http://www.example.com/article/1-article-one</loc><changefreq>weekly</changefreq><priority>0.8</priority></url><url><loc>http://www.example.com/article/2-article-two</loc><changefreq>weekly</changefreq><priority>0.8</priority></url><url><loc>http://www.example.com/article/3-article-with-long-long-long-title</loc><changefreq>weekly</changefreq><priority>0.8</priority></url><url><loc>http://www.example.com/gallery/1-first-gallery</loc><changefreq>weekly</changefreq><priority>0.8</priority></url><url><loc>http://www.example.com/gallery/2-landscape</loc><changefreq>weekly</changefreq><priority>0.8</priority></url></urlset>
EOF;
        $this->assertEquals($expectedXML,$sitemap->render()[0]['xml']);
    }

    public function testSortByPriority() {
        Yii::$app->cache->flush();
        $sitemap = new Sitemap([
            'sortByPriority' => true,
            'urls' => [
                [
                    'loc' => ['/news/default/index'],
                    'priority' => 0.8,
                ],
                [
                    'loc' => ['/main/default/index'],
                    'priority' => 1,
                ],
            ]
        ]);
        $expectedXml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"><url><loc>http://www.example.com/</loc><priority>1</priority></url><url><loc>http://www.example.com/news</loc><priority>0.8</priority></url></urlset>
EOF;
        $this->assertEquals($expectedXml, $sitemap->render()[0]['xml']);
    }
}
