<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Resource\Error;
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
            if (!$imageNode->hasAttribute('alt')) {
                $errors->push(
                    new Error(
                        sprintf('Missing <img> alt attribute for %s', $imageNode->getAttribute('src')),
                        Error::SEVERITY_LOW
                    )
                );
            } elseif (empty(trim($imageNode->getAttribute('alt')))) {
                $errors->push(
                    new Error(
                        sprintf('Empty <img> alt attribute for %s', $imageNode->getAttribute('src')),
                        Error::SEVERITY_LOW
                    )
                );
            }
        }

        return $errors;
    }
}
