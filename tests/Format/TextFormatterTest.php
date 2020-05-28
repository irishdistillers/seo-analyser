<?php

namespace Tests\Format;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Format\TextFormatter;
use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Sitemap\Location;
use SeoAnalyser\Sitemap\Sitemap;
use Symfony\Component\Console\Output\BufferedOutput;
use Tightenco\Collect\Support\Collection;

class TextFormatterTest extends TestCase
{
    public function testGetName()
    {
        $formatter = new TextFormatter;
        $this->assertEquals('text', $formatter->getName());
    }

    public function testExtractErrorsEmpty()
    {
        $formatter = new TextFormatter;
        $output = new BufferedOutput;

        $formatter->extractErrors(new Collection(), $output);

        $this->assertEquals('', $output->fetch());
        $this->assertFalse($formatter->hasErrors());
    }

    public function testExtractErrorsWithErrors()
    {
        $formatter = new TextFormatter;
        $output = new BufferedOutput;

        $sitemap = new Sitemap('http://example.com/sitemap.xml');
        $sitemap->addError(new Error('Some error', Error::SEVERITY_LOW));

        $location = new Location('http://example.com/page-one', $sitemap);
        $location->addError(new Error('Some location error', Error::SEVERITY_HIGH));
        $sitemap->addLocation($location);

        $sitemaps = new Collection;
        $sitemaps->push($sitemap);
        $formatter->extractErrors($sitemaps, $output);

        $this->assertEquals(
            <<<OUT
Found 1 errors for http://example.com/sitemap.xml
+----------+------------+
| Severity | Message    |
+----------+------------+
| Low      | Some error |
+----------+------------+

Found 1 errors for http://example.com/page-one
+----------+---------------------+
| Severity | Message             |
+----------+---------------------+
| High     | Some location error |
+----------+---------------------+


OUT,
            $output->fetch()
        );
        $this->assertTrue($formatter->hasErrors());
    }
}
