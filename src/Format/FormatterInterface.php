<?php declare(strict_types=1);

namespace SeoAnalyser\Format;

use Tightenco\Collect\Support\Collection;
use Symfony\Component\Console\Output\OutputInterface;

interface FormatterInterface
{
    /**
     * Returns the name of the formatter, used to identify it
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Saves errors to a medium in the format determined by the formatter
     *
     * @param Collection<\SeoAnalyser\Resource\Sitemap> $sitemaps
     * @param OutputInterface                          $output
     */
    public function extractErrors(Collection $sitemaps, OutputInterface $output);
}
