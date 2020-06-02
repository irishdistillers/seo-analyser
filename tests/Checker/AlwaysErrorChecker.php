<?php

namespace Tests\Checker;

use SeoAnalyser\Checker\CheckerInterface;
use SeoAnalyser\Resource\Error;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

class AlwaysErrorChecker implements CheckerInterface
{
    public function getName(): string
    {
        return 'AlwaysError';
    }

    public function check(Crawler $crawler): Collection
    {
        return new Collection([
            new Error('Error message', Error::SEVERITY_NORMAL)
        ]);
    }
}
