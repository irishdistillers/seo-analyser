<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

interface CheckerInterface
{
    /**
     * Checks the page and returns a Collection of Error
     *
     * @param  Crawler $crawler
     * @return Collection
     */
    public function check(Crawler $crawler): Collection;
}
