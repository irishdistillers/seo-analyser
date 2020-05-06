<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

class ImageAltChecker implements CheckerInterface
{
    use CheckerNameTrait;

    /**
     * {@inheritDoc}
     */
    public function check(Crawler $crawler): Collection
    {
        $errors = new Collection;

        /** @var \DomElement $imageNode */
        foreach ($crawler->filterXPath('//img') as $imageNode) {
            if (empty(trim($imageNode->getAttribute('alt')))) {
                $errors->push(
                    new Error(
                        sprintf('Missing or empty <img> alt attribute for %s', $imageNode->getAttribute('src')),
                        Error::SEVERITY_LOW
                    )
                );
            }
        }

        return $errors;
    }
}
