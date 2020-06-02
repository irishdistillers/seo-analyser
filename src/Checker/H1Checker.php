<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Resource\Error;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

class H1Checker implements CheckerInterface
{
    use CheckerNameTrait;

    /**
     * {@inheritDoc}
     */
    public function check(Crawler $crawler): Collection
    {
        $errors = new Collection;

        $numHeaders = count($crawler->filterXPath('//h1'));

        if ($numHeaders !== 1) {
            $errors->push(
                new Error(sprintf('Only 1 H1 tag is recommended, %d found', $numHeaders), Error::SEVERITY_NORMAL)
            );
        }

        return $errors;
    }
}
