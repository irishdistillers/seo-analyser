<?php

declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Traits\TraitChecker;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Tightenco\Collect\Support\Collection;

class MetaSocialMediaChecker implements CheckerInterface
{
    use TraitChecker;

    const DEFAULT_OG_RESTRICTIONS_CONTENT = 'alcohol';

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * @var \Tightenco\Collect\Support\Collection
     */
    protected $errors;

    /**
     * @var \Symfony\Component\Validator\Validation
     */
    protected $validation;

    /**
     * {@inheritdoc}
     */
    public function check(
        Crawler $crawler
    ): Collection {
        $this->errors = new Collection();
        $this->crawler = $crawler;

        $xpath = $this->isFieldAvailable('twitter:site', 'name');
        if (!empty($xpath->attr('content'))) {
            $this->validateTwitterSite('twitter:site', $xpath);
        }

        $xpath = $this->isFieldAvailable('twitter:creator', 'name');
        if (!empty($xpath->attr('content'))) {
            $this->validateTwitterCreator('twitter:creator', $xpath);
        }

        $xpath = $this->isFieldAvailable('og:locale', 'property');
        if (!empty($xpath->attr('content'))) {
            $this->validateOgLocale('og:locale', $xpath);
        }

        $xpath = $this->isFieldAvailable('og:restrictions:content', 'property');
        if (!empty($xpath->attr('content'))) {
            $this->validateOgRestrictionsContent('og:restrictions:content', $xpath);
        }

        $xpath = $this->isFieldAvailable('og:url', 'property');
        if (!empty($xpath->attr('content'))) {
            $this->validateOgUrl('og:url', $xpath);
        }

        $this->isFieldAvailable('og:site_name', 'property');
        $this->isFieldAvailable('og:type', 'property');
        $this->isFieldAvailable('twitter:card', 'name');
        $this->isFieldAvailable('name', 'name');
        $this->isFieldAvailable('og:title', 'property');
        $this->isFieldAvailable('twitter:title', 'name');

        return $this->errors;
    }

    private function validateTwitterSite(string $fieldTagName, $xpath)
    {
        $message = 'set to '.$xpath->attr('content').' instead of @twitterhandler';
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $xpath->attr('content'),
            [
                new Assert\Regex([
                    'pattern' => '/@\w+/',
                    'match' => true,
                    'message' => "<$fieldTagName> format is wrong. $message",
                ]),
            ]
        );

        $this->pushViolationsErrors(
            $violations,
            Error::SEVERITY_NORMAL
        );
    }

    private function validateTwitterCreator(string $fieldTagName, $xpath)
    {
        $message = 'set to '.$xpath->attr('content').' instead of @twitterhandler';
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $xpath->attr('content'),
            [
                new Assert\Regex([
                    'pattern' => '/@\w+/',
                    'match' => true,
                    'message' => "<$fieldTagName> format is wrong. $message",
                ]),
            ]
        );

        $this->pushViolationsErrors(
            $violations,
            Error::SEVERITY_NORMAL
        );
    }

    private function validateOgLocale(string $fieldTagName, $xpath)
    {
        $message = 'Set to '.$xpath->attr('content').' instead of something like en_gb';
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $xpath->attr('content'),
            [
                new Assert\Regex([
                    'pattern' => '/\w{2}_\w{2}/',
                    'match' => true,
                    'message' => "<$fieldTagName> format is wrong. $message",
                ]),
            ]
        );

        $this->pushViolationsErrors(
            $violations,
            Error::SEVERITY_NORMAL
        );
    }

    private function validateOgRestrictionsContent(string $fieldTagName, $xpath)
    {
        if ($xpath->attr('content') != self::DEFAULT_OG_RESTRICTIONS_CONTENT) {
            $this->errors->push(
                new Error(
                    "<$fieldTagName> should be ".self::DEFAULT_OG_RESTRICTIONS_CONTENT,
                    Error::SEVERITY_LOW
                )
            );
        }
    }

    private function validateOgUrl(string $fieldTagName, $xpath)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $xpath->attr('content'),
            [
                new Assert\Url([
                    'message' => "<$fieldTagName> is not a valid url: {{ value }}",
                ]),
            ]
        );

        $this->pushViolationsErrors(
            $violations,
            Error::SEVERITY_NORMAL
        );
    }
}
