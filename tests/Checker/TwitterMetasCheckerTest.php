<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\TwitterMetasChecker;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class TwitterMetasCheckerTest extends TestCase
{
    public function provideGoodMetas()
    {
        return [
            ['
                <meta name="twitter:site" content="@claudeandthejuice"/>
                <meta name="twitter:creator" content="@claudeandthejuice"/>
                <meta name="twitter:card" content="summary"/>
                <meta name="twitter:title" content="claude and the juice!!"/>
            ']
        ];
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageHasAllMetas($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                </head>
            </html>"
        );

        $checker = new TwitterMetasChecker();
        $errors = $checker->check($crawler);
        $this->assertCount(0, $errors);
    }

    public function testPageIsMissingMetas()
    {
        $crawler = new Crawler(
            "<html>
                <head></head>
            </html>"
        );

        $checker = new TwitterMetasChecker();
        $this->assertEquals('TwitterMetas', $checker->getName());
        $errors = $checker->check($crawler);

        $this->assertCount(4, $errors);
        
        $this->assertStringContainsString(
            'name <twitter:site> is not available!',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(0)->getSeverity()
        );

        $this->assertStringContainsString(
            'name <twitter:creator> is not available!',
            $errors->get(1)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(1)->getSeverity()
        );
        
        $this->assertStringContainsString(
            'name <twitter:card> is not available!',
            $errors->get(2)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(2)->getSeverity()
        );

        $this->assertStringContainsString(
            'name <twitter:title> is not available!',
            $errors->get(3)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(3)->getSeverity()
        );
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testPageHasTooManyMetas($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                    $metas
                </head>
            </html>"
        );

        $checker = new TwitterMetasChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(4, $errors);
        
        $this->assertStringContainsString(
            'Too many <twitter:site> tag! You should only have one!',
            $errors->get(0)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(0)->getSeverity()
        );

        $this->assertStringContainsString(
            'Too many <twitter:creator> tag! You should only have one!',
            $errors->get(1)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(1)->getSeverity()
        );
        
        $this->assertStringContainsString(
            'Too many <twitter:card> tag! You should only have one!',
            $errors->get(2)->getDescription()
        );

        $this->assertEquals(
            Error::SEVERITY_HIGH,
            $errors->get(2)->getSeverity()
        );
        
        $this->assertStringContainsString(
            'Too many <twitter:title> tag! You should only have one!',
            $errors->get(3)->getDescription()
        );
    }

    /**
     * @dataProvider provideGoodMetas
     */
    public function testValidateTwitterSite($metas)
    {
        $pattern = '/<meta name="twitter:site" content=".*"\/>/';
        $checker = new TwitterMetasChecker();

        $modifiedMetas = preg_replace(
            $pattern,
            '<meta name="twitter:site" content="11111">',
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
        
        $this->assertStringContainsString(
            '<twitter:site> format is wrong',
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
    public function testValidateTwitterCreator($metas)
    {
        $pattern = '/<meta name="twitter:creator" content=".*"\/>/';
        $checker = new TwitterMetasChecker();

        $modifiedMetas = preg_replace(
            $pattern,
            '<meta name="twitter:creator" content="1112eeww">',
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
        
        $this->assertStringContainsString(
            '<twitter:creator> format is wrong.',
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
    public function testIsFieldAvailableReturnsCrawler($metas)
    {
        $crawler = new Crawler(
            "<html>
                <head>
                    $metas
                </head>
            </html>"
        );

        $checker = new TwitterMetasChecker();
        $checker->check($crawler);

        $xpath = $checker->isFieldAvailable('twitter:site', 'name');
        $this->assertNotEmpty($xpath->attr('content'));

        $xpath = $checker->isFieldAvailable('twitter:sitexxx', 'name');
        $this->assertEmpty($xpath->attr('content'));
    }
}
