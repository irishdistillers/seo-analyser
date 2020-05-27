<?php

namespace Tests\Command;

use \Mockery;
use PHPUnit\Framework\TestCase;
use SeoAnalyser\Command\AnalyseCommand;
use SeoAnalyser\Exception\InvalidAuthOptionException;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Processor\LocationProcessor;
use SeoAnalyser\Processor\SitemapProcessor;
use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Sitemap\Location;
use SeoAnalyser\Sitemap\Sitemap;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tightenco\Collect\Support\Collection;

class AnalyseCommandTest extends TestCase
{
    public function setUp(): void
    {
        $this->mockSitemapProcessor = Mockery::mock(SitemapProcessor::class);
        $this->mockLocationProcessor = Mockery::mock(LocationProcessor::class, ['getCheckers' => new Collection([])]);
        $this->mockClient = Mockery::mock(Client::class);

        $application = new Application();
        $application->add(new AnalyseCommand(
            $this->mockSitemapProcessor,
            $this->mockLocationProcessor,
            $this->mockClient
        ));

        $this->command = $application->find('analyse');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testInvalidAuthConfig()
    {
        $this->mockClient->expects()->configAuth(['invalid-auth'])
            ->andThrows(new InvalidAuthOptionException('Invalid auth'))
        ;
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml',
            '--auth' => ['invalid-auth']
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testSitemapErrors()
    {
        $this->mockClient->expects()->configAuth([]);

        $sitemap = new Sitemap('http://example.com/sitemap.xml');
        $sitemap->addError(new Error('Error message', Error::SEVERITY_NORMAL));

        $this->mockSitemapProcessor->expects()->process(
            'http://example.com/sitemap.xml',
            Mockery::type(OutputInterface::class)
        )->andReturns(new Collection([$sitemap]));
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml'
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertEquals(<<<OUT
Using checkers: 
Retrieving sitemaps
Found 1 sitemaps with 0 urls (1 errors)
Found 1 errors for http://example.com/sitemap.xml (parent: None)
+----------+---------------+
| Severity | Message       |
+----------+---------------+
| Normal   | Error message |
+----------+---------------+

0 URL errors found

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
            Mockery::type(OutputInterface::class)
        )->andReturns(new Collection([$sitemap]));

        $this->mockLocationProcessor->expects()->process($location, Mockery::type(OutputInterface::class));
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml'
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertEquals(<<<OUT
Using checkers: 
Retrieving sitemaps
Found 1 sitemaps with 1 urls (0 errors)
Found 2 errors for http://example.com/page-one (parent: http://example.com/sitemap.xml)
+----------+--------------------------------------+
| Severity | Message                              |
+----------+--------------------------------------+
| High     | Missing something important          |
| Low      | Missing something no one cares about |
+----------+--------------------------------------+

2 URL errors found

OUT
, $output);
    }

    public function testNoErrors()
    {
        $this->mockClient->expects()->configAuth([]);

        $sitemap = new Sitemap('http://example.com/sitemap.xml');
        $location = new Location('http://example.com/page-one', $sitemap);
        $sitemap->addLocation($location);

        $this->mockSitemapProcessor->expects()->process(
            'http://example.com/sitemap.xml',
            Mockery::type(OutputInterface::class)
        )->andReturns(new Collection([$sitemap]));

        $this->mockLocationProcessor->expects()->process($location, Mockery::type(OutputInterface::class));
        
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'sitemap_url' => 'http://example.com/sitemap.xml'
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals(<<<OUT
Using checkers: 
Retrieving sitemaps
Found 1 sitemaps with 1 urls (0 errors)
0 URL errors found

OUT
, $output);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
