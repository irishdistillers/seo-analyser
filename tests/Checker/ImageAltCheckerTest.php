<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\ImageAltChecker;
use SeoAnalyser\Resource\Error;
use Symfony\Component\DomCrawler\Crawler;

class ImageAltCheckerTest extends TestCase
{
    public function testChecker()
    {
        $checker = new ImageAltChecker;
        $this->assertEquals('ImageAlt', $checker->getName());

        $crawler = new Crawler(
            '<html><img src="one.jpg" alt="Alt text"/><img src="two.jpg" alt=" "/><img src="three.jpg"/></html>'
        );
        $errors = $checker->check($crawler);
        $this->assertCount(2, $errors);
        $this->assertEquals('Empty <img> alt attribute for two.jpg', $errors->get(0)->getDescription());
        $this->assertEquals(Error::SEVERITY_LOW, $errors->get(0)->getSeverity());

        $this->assertEquals('Missing <img> alt attribute for three.jpg', $errors->get(1)->getDescription());
        $this->assertEquals(Error::SEVERITY_LOW, $errors->get(1)->getSeverity());
    }
}
