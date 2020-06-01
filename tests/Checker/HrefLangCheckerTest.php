<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\HrefLangChecker;
use SeoAnalyser\Resource\Error;
use Symfony\Component\DomCrawler\Crawler;

class HrefLangCheckerTest extends TestCase
{
    public function testChecker()
    {
        $checker = new HrefLangChecker;
        $this->assertEquals('HrefLang', $checker->getName());

        $crawler = new Crawler(<<<HTML
<html>
    <head>
        <link rel="alternate" href="http://example.com/missing-hreflang"/>
        <link rel="alternate" href="http://example.com/empty-hreflang" hreflang=""/>
        <link rel="alternate" href="http://example.com/correct-hreflang" hreflang="en"/>
    </head>
</html>
HTML
        );
        $errors = $checker->check($crawler);
        $this->assertCount(2, $errors);
        $this->assertEquals(
            'Alternate link for http://example.com/missing-hreflang is missing a hreflang attribute',
            $errors->get(0)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_LOW, $errors->get(0)->getSeverity());

        $this->assertEquals(
            'Alternate link for http://example.com/empty-hreflang has an empty hreflang attribute',
            $errors->get(1)->getDescription()
        );
        $this->assertEquals(Error::SEVERITY_LOW, $errors->get(1)->getSeverity());
    }
}
