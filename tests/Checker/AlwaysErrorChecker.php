<?php

namespace Tests\Checker;

use SeoAnalyser\Checker\CheckerInterface;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

class AlwaysErrorChecker implements CheckerInterface
{
    public function check(Crawler $crawler): Collection
    {
        return new Collection([
            new Error('Error message', Error::SEVERITY_NORMAL)
        ]);
    }
}
