<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Resource\Error;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

class HrefLangChecker implements CheckerInterface
{
    use CheckerNameTrait;

    /**
     * {@inheritDoc}
     */
    public function check(Crawler $crawler): Collection
    {
        $errors = new Collection;

        $alternateLinks = $crawler->filterXPath('//link[@rel="alternate"]');

        /** @var \DOMElement $alternateLink */
        foreach ($alternateLinks as $alternateLink) {
            if (!$alternateLink->hasAttribute('hreflang')) {
                $errors->push(
                    new Error(sprintf(
                        'Alternate link for %s is missing a hreflang attribute',
                        $alternateLink->getAttribute('href')
                    ), Error::SEVERITY_LOW)
                );
            } elseif (empty($alternateLink->getAttribute('hreflang'))) {
                $errors->push(
                    new Error(sprintf(
                        'Alternate link for %s has an empty hreflang attribute',
                        $alternateLink->getAttribute('href')
                    ), Error::SEVERITY_LOW)
                );
            }
        }

        return $errors;
    }
}
