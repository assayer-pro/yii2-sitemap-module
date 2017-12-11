<?php
use \assayerpro\sitemap\RobotsTxt;


class RobotsTxtTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testRobotsTxtHost()
    {
        $robotstxt = new RobotsTxt();
        $this->assertEquals('www.example.com', $robotstxt->host);
        $this->assertEquals("Host: www.example.com\n", $robotstxt->render());
    }
    public function testRobotsTxtHostHttps()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 443;
        $robotstxt = new RobotsTxt();
        $this->assertEquals('https://www.example.com', $robotstxt->host);
        $this->assertEquals("Host: https://www.example.com\n", $robotstxt->render());
    }
    public function testRobotsTxtSitemap()
    {
        $robotstxt = new RobotsTxt();
        $robotstxt->sitemap = 'http://www.example.com/sitemap.xml';
        $this->assertEquals("Host: www.example.com\nSitemap: http://www.example.com/sitemap.xml\n", $robotstxt->render());
    }
    public function testRobotsTxtCreateWithParams()
    {
        $_SERVER['HTTPS'] = 'on';
        $robotstxt = new RobotsTxt(['host' => 'example.net', 'sitemap' => 'http://example.net/data/sitemap.xml']);
        $this->assertEquals("Host: example.net\nSitemap: http://example.net/data/sitemap.xml\n", $robotstxt->render());
    }
    public function testRobotsTxtUseragent()
    {
        $robotstxt = new RobotsTxt([
            'userAgent' => [
                '*' => [
                    'Disallow' => [
                        ['/main/default/index'],
                        '/api',
                        ['/api/version2/index'],
                    ],
                    'Allow' => [
                        '/api/doc',
                    ]
                ],
                'Googlebot-Image' => [
                    'Disallow' => [
                        '/img/',
                        '/*.gif$',
                    ]
                ]
            ]
        ]);
        $expectedRobotsTxt = <<<EOF
Host: www.example.com
User-agent: *
Disallow: /
Disallow: /api
Disallow: /api-v2
Allow: /api/doc
User-agent: Googlebot-Image
Disallow: /img/
Disallow: /*.gif$

EOF;
        $this->assertEquals($expectedRobotsTxt, $robotstxt->render());
        $robotstxt = new RobotsTxt([
            'userAgent' => []
        ]);
        $this->assertEquals("Host: www.example.com\n", $robotstxt->render());
    }

}
