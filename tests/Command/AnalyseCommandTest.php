<?php

namespace Tests\Command;

use SeoAnalyser\Format\FormatterFactory;
use PHPUnit\Framework\TestCase;
use SeoAnalyser\Command\AnalyseCommand;
use SeoAnalyser\Exception\InvalidOptionException;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Processor\LocationProcessor;
use SeoAnalyser\Processor\SitemapProcessor;
use SeoAnalyser\Resource\Error;
use SeoAnalyser\Resource\Location;
use SeoAnalyser\Resource\Sitemap;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tightenco\Collect\Support\Collection;

class AnalyseCommandTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface|\SeoAnalyser\Processor\SitemapProcessor
     */
    private $mockSitemapProcessor;

    /**
     * @var \Mockery\MockInterface|\SeoAnalyser\Processor\LocationProcessor
     */
    private $mockLocationProcessor;

    /**
     * @var FormatterFactory
     */
    private $formatterFactory;

    /**
     * @var \Mockery\MockInterface|\SeoAnalyser\Http\Client
     */
    private $mockClient;

    /**
     * @var AnalyseCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp(): void
    {
        /** @var \Mockery\MockInterface|\SeoAnalyser\Processor\SitemapProcessor */
        $this->mockSitemapProcessor = \Mockery::mock(SitemapProcessor::class);

        /** @var \Mockery\MockInterface|\SeoAnalyser\Processor\LocationProcessor */
        $this->mockLocationProcessor = \Mockery::mock(LocationProcessor::class);
        $this->mockLocationProcessor->allows()->getCheckers()->andReturns(new Collection);

        $this->formatterFactory = new FormatterFactory;
        $this->formatterFactory->addFormatter(new \Tests\Format\DummyFormatter);
        
        /** @var \Mockery\MockInterface|\SeoAnalyser\Http\Client */
        $this->mockClient = \Mockery::mock(Client::class);

        $application = new Application();
        $application->add(new AnalyseCommand(
            $this->mockSitemapProcessor,
            $this->mockLocationProcessor,
            $this->formatterFactory,
            $this->mockClient
        ));

        $this->command = $application->find('analyse');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testInvalidAuthConfig()
    {
        $this->expectException(InvalidOptionException::class);
        $this->mockClient->expects()->configAuth(['invalid-auth'])
            ->andThrows(new InvalidOptionException('Invalid auth'))
        ;
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml',
            '--auth' => ['invalid-auth']
        ]);
    }

    public function testSitemapErrors()
    {
        $this->mockClient->expects()->configAuth([]);

        $sitemap = new Sitemap('http://example.com/sitemap.xml');
        $sitemap->addError(new Error('Error message', Error::SEVERITY_NORMAL));

        $this->mockSitemapProcessor->expects()->process(
            'http://example.com/sitemap.xml',
            \Mockery::type(OutputInterface::class)
        )->andReturns(new Collection([$sitemap]));
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml',
            '--format' => 'dummy'
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertEquals(<<<OUT
Using checkers: 
Retrieving sitemaps
Found 1 sitemaps with 0 urls (1 errors)

OUT
, $output);
    }

    public function testLocationErrors()
    {
        $this->mockClient->expects()->configAuth([]);

        $sitemap = new Sitemap('http://example.com/sitemap.xml');
        $location = new Location('http://example.com/page-one', $sitemap);
        $location->addError(new Error('Missing something important', Error::SEVERITY_HIGH));
        $location->addError(new Error('Missing something no one cares about', Error::SEVERITY_LOW));
        $sitemap->addLocation($location);

        $this->mockSitemapProcessor->expects()->process(
            'http://example.com/sitemap.xml',
            \Mockery::type(OutputInterface::class)
        )->andReturns(new Collection([$sitemap]));

        $this->mockLocationProcessor->expects()->process($location, \Mockery::type(OutputInterface::class));
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml',
            '--format' => 'dummy'
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertEquals(<<<OUT
Using checkers: 
Retrieving sitemaps
Found 1 sitemaps with 1 urls (0 errors)

OUT
, $output);

        /** @var \Tests\Format\DummyFormatter $formatter */
        $formatter = $this->formatterFactory->getFormatter('dummy');
        $errors = $formatter->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals([
            'url' => 'http://example.com/page-one',
            'errors' => ['Low', 'Missing something no one cares about']
        ], $errors[0]);
    }

    public function testNoErrors()
    {
        $this->mockClient->expects()->configAuth([]);

        $sitemap = new Sitemap('http://example.com/sitemap.xml');
        $location = new Location('http://example.com/page-one', $sitemap);
        $sitemap->addLocation($location);

        $this->mockSitemapProcessor->expects()->process(
            'http://example.com/sitemap.xml',
            \Mockery::type(OutputInterface::class)
        )->andReturns(new Collection([$sitemap]));

        $this->mockLocationProcessor->expects()->process($location, \Mockery::type(OutputInterface::class));
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml',
            '--format' => 'dummy'
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals(<<<OUT
Using checkers: 
Retrieving sitemaps
Found 1 sitemaps with 1 urls (0 errors)

OUT
, $output);

        /** @var \Tests\Format\DummyFormatter $formatter */
        $formatter = $this->formatterFactory->getFormatter('dummy');
        $errors = $formatter->getErrors();

        $this->assertCount(0, $errors);
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }
}
