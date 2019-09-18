<?php

namespace Tests\Processor;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Processor\LocationProcessor;
use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Sitemap\Location;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\Checker\AlwaysErrorChecker;

class LocationProcessorTest extends TestCase
{
    public function testProcessSuccess()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new LocationProcessor($mockClient);
        $processor->addChecker(new AlwaysErrorChecker);

        $url = 'http://example.com/location';
        $location = new Location($url);

        $mockClient->expects()->get($url)->andReturns(new Response);
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $processor->process($location, $mockOutput);

        $this->assertTrue($location->hasErrors());
    }

    public function testProcessHttpError()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new LocationProcessor($mockClient);
        $processor->addChecker(new AlwaysErrorChecker);

        $url = 'http://example.com/location';
        $location = new Location($url);

        $mockClient->expects()->get($url)->andReturns(new Response(301));
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $processor->process($location, $mockOutput);

        $this->assertTrue($location->hasErrors());
        $errors = $location->getErrors();
        $this->assertEquals('Failed to load with HTTP status code 301', $errors[0]->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors[0]->getSeverity());
    }

    public function testProcessRequestError()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new LocationProcessor($mockClient);
        $processor->addChecker(new AlwaysErrorChecker);

        $url = 'http://example.com/location';
        $location = new Location($url);

        $mockClient->expects()->get($url)->andThrows(
            new RequestException(
                'HTTP 500',
                new Request('GET', $url),
                new Response(500)
            )
        );
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $processor->process($location, $mockOutput);

        $this->assertTrue($location->hasErrors());
        $errors = $location->getErrors();
        $this->assertEquals('Failed to load with HTTP status code 500', $errors[0]->getDescription());
        $this->assertEquals(Error::SEVERITY_HIGH, $errors[0]->getSeverity());
    }
}
