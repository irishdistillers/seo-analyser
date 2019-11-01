<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\MetaSocialMediaChecker;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class MetaSocialMediaCheckerTest extends TestCase
{
    public function provideGoodMetas()
    {
        return [
            ['
                <meta name="twitter:site" content="@claudeandthejuice"/>
                <meta name="twitter:creator" content="@claudeandthejuice"/>
                <meta property="og:site_name" content="claude and the juice!!"/>
                <meta property="og:type" content="website"/>
                <meta property="og:locale" content="en_EN"/>
                <meta property="og:restrictions:content" content="alcohol"/>
                <meta name="twitter:card" content="summary"/>
                <meta property="og:url" content="http://claudeandthejuice.com"/>
                <meta name="name" content="claude and the juice!!"/>
                <meta property="og:title" content="claude and the juice!!"/>
                <meta name="twitter:title" content="claude and the juice!!"/>
            ']
        ];
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageHasAllNecessaryMetas($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                </head>
            </html>"
        );

        $checker = new MetaSocialMediaChecker();
        $errors = $checker->check($crawler);
        $this->assertCount(0, $errors);
    }

    public function testPageIsMissingSomeNecessaryMetas()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="twitter:site" content="@claudeandthejuice"/>
                    <meta name="twitter:creator" content="@claudeandthejuice"/>
                    <meta property="og:type" content="website"/>
                    <meta property="og:locale" content="en_EN"/>
                    <meta name="twitter:card" content="summary"/>
                    <meta property="og:url" content="http://claudeandthejuice.com"/>
                    <meta name="name" content="claude and the juice!!"/>
                    <meta name="twitter:title" content="claude and the juice!!"/>
                </head>
            </html>'
        );

        $checker = new MetaSocialMediaChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(3, $errors);
        $this->assertContains(
            'property <og:restrictions:content> is not available!',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());

        $this->assertContains(
            'property <og:site_name> is not available!',
            $errors->get(1)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(1)->getSeverity());

        $this->assertContains(
            'property <og:title> is not available!',
            $errors->get(2)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(2)->getSeverity());
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageIsMissingNecessaryMetas($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head></head>
            </html>"
        );

        $checker = new MetaSocialMediaChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(11, $errors);
        
        $this->assertContains(
            'name <twitter:site> is not available!',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());

        $this->assertContains(
            'name <twitter:creator> is not available!',
            $errors->get(1)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(1)->getSeverity());
        
        $this->assertContains(
            'property <og:locale> is not available!',
            $errors->get(2)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(2)->getSeverity());

        $this->assertContains(
            'property <og:restrictions:content> is not available!',
            $errors->get(3)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(3)->getSeverity());
        
        $this->assertContains(
            'property <og:url> is not available!',
            $errors->get(4)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(4)->getSeverity());

        $this->assertContains(
            'property <og:site_name> is not available!',
            $errors->get(5)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(5)->getSeverity());
        
        $this->assertContains(
            'property <og:type> is not available!',
            $errors->get(6)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(6)->getSeverity());

        $this->assertContains(
            'name <twitter:card> is not available!',
            $errors->get(7)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(7)->getSeverity());
        
        $this->assertContains(
            'name <name> is not available!',
            $errors->get(8)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(8)->getSeverity());

        $this->assertContains(
            'property <og:title> is not available!',
            $errors->get(9)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(9)->getSeverity());
        
        $this->assertContains(
            'name <twitter:title> is not available!',
            $errors->get(10)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(10)->getSeverity());
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageHasTooManyNecessaryMetas($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                    $metas
                </head>
            </html>"
        );

        $checker = new MetaSocialMediaChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(11, $errors);
        
        $this->assertContains(
            'Too many <twitter:site> tag! You should only have one!',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());

        $this->assertContains(
            'Too many <twitter:creator> tag! You should only have one!',
            $errors->get(1)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(1)->getSeverity());
        
        $this->assertContains(
            'Too many <og:locale> tag! You should only have one!',
            $errors->get(2)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(2)->getSeverity());

        $this->assertContains(
            'Too many <og:restrictions:content> tag! You should only have one!',
            $errors->get(3)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(3)->getSeverity());
        
        $this->assertContains(
            'Too many <og:url> tag! You should only have one!',
            $errors->get(4)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(4)->getSeverity());

        $this->assertContains(
            'Too many <og:site_name> tag! You should only have one!',
            $errors->get(5)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(5)->getSeverity());
        
        $this->assertContains(
            'Too many <og:type> tag! You should only have one!',
            $errors->get(6)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(6)->getSeverity());

        $this->assertContains(
            'Too many <twitter:card> tag! You should only have one!',
            $errors->get(7)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(7)->getSeverity());
        
        $this->assertContains(
            'Too many <name> tag! You should only have one!',
            $errors->get(8)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(8)->getSeverity());

        $this->assertContains(
            'Too many <og:title> tag! You should only have one!',
            $errors->get(9)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(9)->getSeverity());
        
        $this->assertContains(
            'Too many <twitter:title> tag! You should only have one!',
            $errors->get(10)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(10)->getSeverity());
    }


    

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageHasInvalidTwitterSite($metas)
    {
        $crawler = new Crawler(
            '<html>
                <head>
                <meta name="twitter:site" content="claudeandthejuice"/>
                </head>
            </html>'
        );

        $checker = new MetaSocialMediaChecker();
        $errors = $checker->check($crawler);

        $this->assertContains(
            '<twitter:site> format is wrong',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_NORMAL, $errors->get(0)->getSeverity());
    }




















    /**
     * @dataProvider provideGoodMetas
     */
    public function testIsFieldAvailableReturnsCrawler($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                </head>
            </html>"
        );

        $checker = new MetaSocialMediaChecker();
        $checker->check($crawler);

        $xpath = $checker->isFieldAvailable('og:site_name', 'property');
        $this->assertNotEmpty($xpath->attr('content'));

        $xpath = $checker->isFieldAvailable('og:site_namexxx', 'property');
        $this->assertEmpty($xpath->attr('content'));
    }
}
