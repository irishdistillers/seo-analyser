<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\MetaDescriptionChecker;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class MetaDescriptionCheckerTest extends TestCase
{
    public function provideRandomText()
    {
        return [[
            "Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
            Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque
            penatibus et magnis dis parturient montes, nascetur ridiculus mus."
        ]];
    }

    public function testPageHasAllNecessaryDescriptions()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);
        $this->assertCount(0, $errors);
    }

    /**
     * @dataProvider provideRandomText()
     */
    public function testPageHasTooLongDescriptions($randomText)
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="'.$randomText.'">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);
        $this->assertContains(
            '<description> tag should not be longer',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_NORMAL, $errors->get(0)->getSeverity());
    }

    public function testPageHasTooManyDescriptions()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice!!">
                    <meta name="description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);
        $this->assertContains(
            'Too many <description> tag',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());
    }

    public function testPageMissingDescription()
    {
        $crawler = new Crawler(
            '<html>
                <head></head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);
        $this->assertContains(
            '<description> tag is not available!',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());
    }
}
