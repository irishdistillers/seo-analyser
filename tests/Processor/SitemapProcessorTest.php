<?php

namespace Tests\Processor;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Processor\SitemapProcessor;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapProcessorTest extends TestCase
{
    public function testProcessSuccess()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new SitemapProcessor($mockClient);

        $firstLevelUrl = 'http://example.com/sitemap.xml';
        $secondLevelUrl = 'http://example.com/dir/sitemap.xml';
        $thirdLevelUrl = 'http://example.com/dir/dir/sitemap.xml';

        $mockClient->expects()->get($firstLevelUrl)->andReturns(new Response(
            200,
            [],
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
    <sitemapindex>
        <sitemap><loc>http://example.com/dir/sitemap.xml</loc></sitemap>
    </sitemapindex>
XML
        ));
        $mockClient->expects()->get($secondLevelUrl)->andReturns(new Response(
            200,
            [],
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
    <sitemapindex>
        <sitemap>
            <loc>http://example.com/dir/dir/sitemap.xml</loc>
        </sitemap>
    </sitemapindex>
XML
        ));
        $mockClient->expects()->get($thirdLevelUrl)->andReturns(new Response(
            200,
            [],
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset>
    <url>
        <loc>http://example.com/page-one</loc>
    </url>
    <url>
        <loc>http://example.com/page-two</loc>
    </url>
</urlset>
XML
        ));
        $mockOutput->expects()->writeln('Retrieving '.$firstLevelUrl, OutputInterface::VERBOSITY_VERBOSE);
        $mockOutput->expects()->writeln('Retrieving '.$secondLevelUrl, OutputInterface::VERBOSITY_VERBOSE);
        $mockOutput->expects()->writeln('Retrieving '.$thirdLevelUrl, OutputInterface::VERBOSITY_VERBOSE);

        $sitemaps = $processor->process($firstLevelUrl, $mockOutput);
        $this->assertEquals($thirdLevelUrl, $sitemaps->get(0)->getUrl());
        $this->assertEquals($secondLevelUrl, $sitemaps->get(1)->getUrl());
        $this->assertEquals($firstLevelUrl, $sitemaps->get(2)->getUrl());

        $this->assertCount(2, $sitemaps->get(0)->getLocations());
        $this->assertCount(0, $sitemaps->get(1)->getLocations());
        $this->assertCount(0, $sitemaps->get(2)->getLocations());

        $this->assertCount(3, $sitemaps);
    }

    public function testProcessMalformedXML()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new SitemapProcessor($mockClient);

        $url = 'http://example.com/sitemap.xml';

        $mockClient->expects()->get($url)->andReturns(new Response(200, [], '<invalid><xml'));
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $sitemaps = $processor->process($url, $mockOutput);

        $sitemap = $sitemaps->pop();
        $this->assertTrue($sitemap->hasErrors());
        $errors = $sitemap->getErrors();
        $this->assertEquals('Malformed XML', $errors[0]->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors[0]->getSeverity());
    }

    public function testProcessHttpError()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new SitemapProcessor($mockClient);

        $url = 'http://example.com/sitemap.xml';

        $mockClient->expects()->get($url)->andReturns(new Response(301));
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $sitemaps = $processor->process($url, $mockOutput);

        $sitemap = $sitemaps->pop();
        $this->assertTrue($sitemap->hasErrors());
        $errors = $sitemap->getErrors();
        $this->assertEquals('Failed to load with HTTP status code 301', $errors[0]->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors[0]->getSeverity());
    }

    public function testProcessRequestError()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new SitemapProcessor($mockClient);

        $url = 'http://example.com/sitemap.xml';

        $mockClient->expects()->get($url)->andThrows(
            new RequestException(
                'HTTP 500',
                new Request('GET', $url),
                new Response(500)
            )
        );
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $sitemaps = $processor->process($url, $mockOutput);

        $sitemap = $sitemaps->pop();
        $this->assertTrue($sitemap->hasErrors());
        $errors = $sitemap->getErrors();
        $this->assertEquals('Failed to load with HTTP status code 500', $errors[0]->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors[0]->getSeverity());
    }

    public function testProcessConnectionError()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new SitemapProcessor($mockClient);

        $url = 'http://example.com/sitemap.xml';

        $mockClient->expects()->get($url)->andThrows(
            new RequestException(
                'Connection error',
                new Request('GET', $url)
            )
        );
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $sitemaps = $processor->process($url, $mockOutput);

        $sitemap = $sitemaps->pop();
        $this->assertTrue($sitemap->hasErrors());
        $errors = $sitemap->getErrors();
        $this->assertEquals('Connection error', $errors[0]->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors[0]->getSeverity());
    }
}
