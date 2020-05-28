<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\TitleChecker;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class TitleCheckerTest extends TestCase
{
    public function testChecker()
    {
        $checker = new TitleChecker;
        $this->assertEquals('Title', $checker->getName());

        $crawler = new Crawler('<html><body><title>Title</title></body></html>');
        $errors = $checker->check($crawler);
        $this->assertCount(1, $errors);
        $this->assertEquals('1 title tag is required, 0 found', $errors->get(0)->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());

        $crawler = new Crawler('<html><head><title>Header</title></head></html>');
        $errors = $checker->check($crawler);
        $this->assertCount(0, $errors);

        $crawler = new Crawler('<html><head><title>Header</title><title>Header</title></head></html>');
        $errors = $checker->check($crawler);
        $this->assertCount(1, $errors);
        $this->assertEquals('1 title tag is required, 2 found', $errors->get(0)->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors->get(0)->getSeverity());
    }
}
