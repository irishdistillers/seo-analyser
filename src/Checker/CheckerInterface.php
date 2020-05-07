<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

interface CheckerInterface
{
    /**
     * Returns the name of the checker
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Checks the page and returns a Collection of Error
     *
     * @param  Crawler $crawler
     * @return Collection
     */
    public function check(Crawler $crawler): Collection;
}
