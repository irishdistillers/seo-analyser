<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\H1Checker;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class H1CheckerTest extends TestCase
{
    public function testChecker()
    {
        $checker = new H1Checker;
        $this->assertEquals('H1', $checker->getName());

        $crawler = new Crawler('<html></html>');
        $errors = $checker->check($crawler);
        $this->assertCount(1, $errors);
        $this->assertEquals('Only 1 H1 tag is recommended, 0 found', $errors->get(0)->getDescription());
        $this->assertEquals(Error::SEVERITY_NORMAL, $errors->get(0)->getSeverity());

        $crawler = new Crawler('<html><h1>Header</h1></html>');
        $errors = $checker->check($crawler);
        $this->assertCount(0, $errors);

        $crawler = new Crawler('<html><h1>Header</h1><h1>Another header</h1></html>');
        $errors = $checker->check($crawler);
        $this->assertCount(1, $errors);
        $this->assertEquals('Only 1 H1 tag is recommended, 2 found', $errors->get(0)->getDescription());
        $this->assertEquals(Error::SEVERITY_NORMAL, $errors->get(0)->getSeverity());
    }
}
