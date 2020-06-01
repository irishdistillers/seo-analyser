<?php

namespace Tests\Format;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Format\HtmlFormatter;
use Symfony\Component\Console\Output\BufferedOutput;
use Tightenco\Collect\Support\Collection;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class HtmlFormatterTest extends TestCase
{
    public function testGetName()
    {
        $loader = new ArrayLoader;
        $environment = new Environment($loader);
        $formatter = new HtmlFormatter($environment);

        $this->assertEquals('html', $formatter->getName());
    }

    public function testExtractErrors()
    {
        $sitemaps = new Collection;

        $loader = new ArrayLoader;
        $loader->setTemplate('report.html.twig', '<html></html>');
        $environment = new Environment($loader);
        $formatter = new HtmlFormatter($environment);

        $formatter = new HtmlFormatter($environment);
        $output = new BufferedOutput;

        $formatter->extractErrors($sitemaps, $output);

        $this->assertEquals(
            '<html></html>',
            $output->fetch()
        );
    }
}
