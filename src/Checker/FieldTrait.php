<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

trait FieldTrait
{
    public function isFieldAvailable(
        string $fieldTagName,
        string $fieldType = 'name'
    ): Crawler {
        $path = '//html/head/meta[@'.$fieldType.'="'.$fieldTagName.'"]';
        $xpath = $this->crawler->filterXPath($path);

        if (0 >= count($xpath)) {
            $this->errors->push(
                new Error(
                    "$fieldType <$fieldTagName> is not available!",
                    Error::SEVERITY_HIGH
                )
            );

            return $this->crawler;
        }

        if (1 < count($xpath)) {
            $this->errors->push(
                new Error(
                    "Too many <$fieldTagName> tag! You should only have one!",
                    Error::SEVERITY_HIGH
                )
            );

            return $this->crawler;
        }

        return $xpath;
    }
}
