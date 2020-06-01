<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Resource\Error;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

class TitleChecker implements CheckerInterface
{
    use CheckerNameTrait;

    /**
     * {@inheritDoc}
     */
    public function check(Crawler $crawler): Collection
    {
        $errors = new Collection;

        $numTitle = count($crawler->filterXPath('//head/title'));

        if ($numTitle !== 1) {
            $errors->push(
                new Error(sprintf('1 title tag is required, %d found', $numTitle), Error::SEVERITY_HIGH)
            );
        }

        return $errors;
    }
}
