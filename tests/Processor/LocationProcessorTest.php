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
use SeoAnalyser\Sitemap\Sitemap;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\Checker\AlwaysErrorChecker;
use SeoAnalyser\Checker\H1Checker;
use SeoAnalyser\Checker\HrefLangChecker;

class LocationProcessorTest extends TestCase
{
    public function testFilterCheckers()
    {
        /** @var \Mockery\MockInterface|Client */
        $mockClient = \Mockery::mock(Client::class);

        $processor = new LocationProcessor($mockClient);
        $processor->addChecker(new AlwaysErrorChecker);
        $processor->addChecker(new H1Checker);
        $processor->addChecker(new HrefLangChecker);

        $processor->filterCheckers(['H1']);

        $this->assertEquals([1 => 'H1'], $processor->getCheckers()->map(function ($checker) {
            return $checker->getName();
        })->toArray());
    }

    public function testProcessSuccess()
    {
        /** @var \Mockery\MockInterface|Client */
        $mockClient = \Mockery::mock(Client::class);
        /** @var \Mockery\MockInterface|OutputInterface */
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new LocationProcessor($mockClient);
        $processor->addChecker(new AlwaysErrorChecker);

        $url = 'http://example.com/location';
        $location = new Location($url, new Sitemap('http://example.com/parent'));

        $mockClient->expects()->get($url)->andReturns(new Response);
        $mockOutput->expects()->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);

        $processor->process($location, $mockOutput);

        $this->assertTrue($location->hasErrors());
    }

    public function testProcessHttpError()
    {
        /** @var \Mockery\MockInterface|Client */
        $mockClient = \Mockery::mock(Client::class);
        /** @var \Mockery\MockInterface|OutputInterface */
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new LocationProcessor($mockClient);
        $processor->addChecker(new AlwaysErrorChecker);

        $url = 'http://example.com/location';
        $location = new Location($url, new Sitemap('http://example.com/parent'));

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
        /** @var \Mockery\MockInterface|Client */
        $mockClient = \Mockery::mock(Client::class);
        /** @var \Mockery\MockInterface|OutputInterface */
        $mockOutput = \Mockery::mock(OutputInterface::class);

        $processor = new LocationProcessor($mockClient);
        $processor->addChecker(new AlwaysErrorChecker);

        $url = 'http://example.com/location';
        $location = new Location($url, new Sitemap('http://example.com/parent'));

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
