<?php

namespace Tests\Format;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Format\JsonFormatter;
use SeoAnalyser\Resource\Error;
use SeoAnalyser\Resource\Location;
use SeoAnalyser\Resource\Sitemap;
use Symfony\Component\Console\Output\BufferedOutput;
use Tightenco\Collect\Support\Collection;
use JMS\Serializer\SerializerBuilder;

class JsonFormatterTest extends TestCase
{
    public function testGetName()
    {
        $formatter = new JsonFormatter(SerializerBuilder::create());
        $this->assertEquals('json', $formatter->getName());
    }

    public function testExtractErrorsEmpty()
    {
        $formatter = new JsonFormatter(SerializerBuilder::create());
        $output = new BufferedOutput;

        $formatter->extractErrors(new Collection(), $output);

        $this->assertJsonStringEqualsJsonString('[]', $output->fetch());
    }

    public function testExtractErrorsWithErrors()
    {
        $formatter = new JsonFormatter(SerializerBuilder::create());
        $output = new BufferedOutput;

        $sitemap = new Sitemap('http://example.com/sitemap.xml');
        $sitemap->addError(new Error('Some error', Error::SEVERITY_LOW));

        $location = new Location('http://example.com/page-one', $sitemap);
        $location->addError(new Error('Some location error', Error::SEVERITY_HIGH));
        $sitemap->addLocation($location);

        $sitemaps = new Collection;
        $sitemaps->push($sitemap);
        $formatter->extractErrors($sitemaps, $output);

        $this->assertJsonStringEqualsJsonString(
            '[{"url":"http:\/\/example.com\/sitemap.xml","locations":[{"url":"http:\/\/example.com\/page-one",'.
            '"errors":[{"severity":"High","description":"Some location error"}]}],"errors":[{"severity":"Low",'.
            '"description":"Some error"}]}]',
            $output->fetch()
        );
    }
}
