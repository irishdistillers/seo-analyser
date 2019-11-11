<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\OpenGraphMetasChecker;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class OpenGraphMetasCheckerTest extends TestCase
{
    public function provideGoodMetas()
    {
        return [
            ['
                <meta property="og:site_name" content="claude and the juice!!"/>
                <meta property="og:type" content="website"/>
                <meta property="og:locale" content="en_EN"/>
                <meta property="og:restrictions:content" content="alcohol"/>
                <meta property="og:url" content="http://claudeandthejuice.com"/>
                <meta property="og:title" content="claude and the juice!!"/>
                <meta property="og:description" content="claude and the juice!!"/>
            '],
        ];
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageHasAllOgMetas($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                </head>
            </html>"
        );

        $checker = new OpenGraphMetasChecker();
        $errors = $checker->check($crawler);
        $this->assertCount(0, $errors);
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageIsMissingOgMetas($metas)
    {
        $crawler = new Crawler(
            '<html>
                <head></head>
            </html>'
        );

        $checker = new OpenGraphMetasChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(7, $errors);

        $this->assertContains(
            'property <og:locale> is not available!',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(0)->getSeverity()
        );

        $this->assertContains(
            'property <og:restrictions:content> is not available!',
            $errors->get(1)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(1)->getSeverity()
        );

        $this->assertContains(
            'property <og:url> is not available!',
            $errors->get(2)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(2)->getSeverity()
        );

        $this->assertContains(
            'property <og:description> is not available!',
            $errors->get(3)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(3)->getSeverity()
        );

        $this->assertContains(
            'property <og:site_name> is not available!',
            $errors->get(4)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(4)->getSeverity()
        );

        $this->assertContains(
            'property <og:type> is not available!',
            $errors->get(5)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(5)->getSeverity()
        );

        $this->assertContains(
            'property <og:title> is not available!',
            $errors->get(6)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(6)->getSeverity()
        );
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageHasTooManyOgMetas($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                    $metas
                </head>
            </html>"
        );

        $checker = new OpenGraphMetasChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(7, $errors);

        $this->assertContains(
            'Too many <og:locale> tag! You should only have one!',
            $errors->get(0)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(0)->getSeverity()
        );

        $this->assertContains(
            'Too many <og:restrictions:content> tag! You should only have one!',
            $errors->get(1)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(1)->getSeverity()
        );

        $this->assertContains(
            'Too many <og:url> tag! You should only have one!',
            $errors->get(2)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(2)->getSeverity()
        );

        $this->assertContains(
            'Too many <og:description> tag! You should only have one!',
            $errors->get(3)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(3)->getSeverity()
        );

        $this->assertContains(
            'Too many <og:site_name> tag! You should only have one!',
            $errors->get(4)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(4)->getSeverity()
        );

        $this->assertContains(
            'Too many <og:type> tag! You should only have one!',
            $errors->get(5)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(5)->getSeverity()
        );

        $this->assertContains(
            'Too many <og:title> tag! You should only have one!',
            $errors->get(6)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(6)->getSeverity()
        );
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testValidateOgDescription($metas)
    {
        $pattern = '/<meta property="og:description" content=".*"\/>/';
        $checker = new OpenGraphMetasChecker();

        $modifiedMetas = preg_replace(
            $pattern,
            '<meta property="og:description" content="d">',
            $metas
        );

        $crawler = new Crawler(
            "<html>
                <head>
                    $modifiedMetas
                </head>
            </html>"
        );

        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);

        $this->assertContains(
            '<og:description> tag must be at least 10 characters long. Only 1 characters found.',
            $errors->get(0)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_NORMAL,
            $errors->get(0)->getSeverity()
        );

        ////////////////

        $modifiedMetas = preg_replace(
            $pattern,
            '<meta property="og:description" content="'.$this->provideRandomText().'">',
            $metas
        );

        $crawler = new Crawler(
            "<html>
                <head>
                    $modifiedMetas
                </head>
            </html>"
        );

        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);

        $this->assertContains(
            '<og:description> tag should not be longer than',
            $errors->get(0)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_NORMAL,
            $errors->get(0)->getSeverity()
        );
    }

    private function provideRandomText()
    {
        return "Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
        Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque
        penatibus et magnis dis parturient montes, nascetur ridiculus mus.";
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testvalidateOgLocale($metas)
    {
        $pattern = '/<meta property="og:locale" content=".*"\/>/';
        $checker = new OpenGraphMetasChecker();

        $modifiedMetas = preg_replace(
            $pattern,
            '<meta property="og:locale" content="en">',
            $metas
        );

        $crawler = new Crawler(
            "<html>
                <head>
                    $modifiedMetas
                </head>
            </html>"
        );

        $errors = $checker->check($crawler);

        $this->assertCount(2, $errors);

        $this->assertContains(
            '<og:locale> format is wrong.',
            $errors->get(0)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_NORMAL,
            $errors->get(0)->getSeverity()
        );

        $this->assertContains(
            'This value should have exactly',
            $errors->get(1)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_NORMAL,
            $errors->get(1)->getSeverity()
        );
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testvalidateOgRestrictionsContent($metas)
    {
        $pattern = '/<meta property="og:restrictions:content" content=".*"\/>/';
        $checker = new OpenGraphMetasChecker();

        $modifiedMetas = preg_replace(
            $pattern,
            '<meta property="og:restrictions:content" content="blablabla">',
            $metas
        );

        $crawler = new Crawler(
            "<html>
                <head>
                    $modifiedMetas
                </head>
            </html>"
        );

        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);

        $this->assertContains(
            '<og:restrictions:content> should be',
            $errors->get(0)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_LOW,
            $errors->get(0)->getSeverity()
        );
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testvalidateOgUrl($metas)
    {
        $pattern = '/<meta property="og:url" content=".*"\/>/';
        $checker = new OpenGraphMetasChecker();

        $modifiedMetas = preg_replace(
            $pattern,
            '<meta property="og:url" content="blablabla.com">',
            $metas
        );

        $crawler = new Crawler(
            "<html>
                <head>
                    $modifiedMetas
                </head>
            </html>"
        );

        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);

        $this->assertContains(
            '<og:url> is not a valid url',
            $errors->get(0)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_NORMAL,
            $errors->get(0)->getSeverity()
        );
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testIsMetaAvailableReturnsCrawler($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                </head>
            </html>"
        );

        $checker = new OpenGraphMetasChecker();
        $checker->check($crawler);

        $xpath = $checker->isMetaAvailable('og:site_name', 'property');
        $this->assertNotEmpty($xpath->attr('content'));

        $xpath = $checker->isMetaAvailable('og:site_namexxx', 'property');
        $this->assertEmpty($xpath->attr('content'));
    }
}
