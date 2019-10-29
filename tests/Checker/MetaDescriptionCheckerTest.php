<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\MetaDescriptionChecker;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class MetaDescriptionCheckerTest extends TestCase
{
    public function testPageHasAllNecessaryDescriptions()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice!!">
                    <meta property="og:description" content="claude and the juice!!">
                    <meta name="twitter:description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);
        $this->assertCount(0, $errors);
    }

    public function testPageHasTooLongDescriptions()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="'.$this->generateRandomSentence().'">
                    <meta property="og:description" content="'.$this->generateRandomSentence().'">
                    <meta name="twitter:description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(2, $errors);
        $this->assertContains(
            '<description> tag should not be longer than 160 characters',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_NORMAL, $errors->get(0)->getSeverity());

        $this->assertContains(
            '<og:description> tag should not be longer than 160 characters',
            $errors->get(1)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_NORMAL, $errors->get(1)->getSeverity());
    }

    public function testPageHasTooManyDescriptions()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice!!">
                    <meta name="description" content="claude and the juice!!">
                    <meta property="og:description" content="claude and the juice!!">
                    <meta name="twitter:description" content="claude and the juice!!">
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
                <head>
                    <meta property="og:description" content="claude and the juice!!">
                    <meta name="twitter:description" content="claude and the juice!!">
                </head>
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

    public function testPageHasTooManyOgDescriptions()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice!!">
                    <meta property="og:description" content="claude and the juice!!">
                    <meta property="og:description" content="claude and the juice!!">
                    <meta name="twitter:description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);
        $this->assertContains(
            'Too many <og:description> tag',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());
    }

    public function testPageMissingOgDescription()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice!!">
                    <meta name="twitter:description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);
        $this->assertContains(
            '<og:description> tag is not available',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());
    }

    public function testPageHasTooManyTwitterDescriptions()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice !!!">
                    <meta property="og:description" content="claude and the juice!!">
                    <meta name="twitter:description" content="claude and the juice!!">
                    <meta name="twitter:description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);
        $this->assertContains(
            'Too many <twitter:description> tag',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());
    }

    public function testPageMissingTwitterDescription()
    {
        $crawler = new Crawler(
            '<html>
                <head>
                    <meta name="description" content="claude and the juice!!">
                    <meta property="og:description" content="claude and the juice!!">
                </head>
            </html>'
        );

        $checker = new MetaDescriptionChecker();
        $errors = $checker->check($crawler);

        $this->assertCount(1, $errors);
        $this->assertContains(
            '<twitter:description> tag is not available',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    private function generateRandomSentence()
    {
        $sentence = '';
        for ($i=0; $i<30; $i++) {
            $sentence .= $this->generateRandomString($i) . ' ';
        }
        return $sentence;
    }
}
