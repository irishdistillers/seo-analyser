<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Resource\Error;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Tightenco\Collect\Support\Collection;

class TwitterMetasChecker implements CheckerInterface
{
    use CheckerNameTrait, FieldTrait, ValidatorTrait;

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

        $this->isFieldAvailable('twitter:card', 'name');
        $this->isFieldAvailable('twitter:title', 'name');

        return $this->errors;
    }

    private function validateTwitterSite(string $fieldTagName, $xpath)
    {
        $message = 'set to '.$xpath->attr('content').' instead of @twitterhandler';
        $validator = Validation::createValidator();
        $violations = $validator->validate($xpath->attr('content'), [
            new Assert\Regex([
                'pattern' => '/@\w+/',
                'match' => true,
                'message' => "<$fieldTagName> format is wrong. $message",
            ])
        ]);

        $this->pushViolationsErrors(
            $violations,
            Error::SEVERITY_NORMAL
        );
    }

    private function validateTwitterCreator(string $fieldTagName, $xpath)
    {
        $message = 'set to '.$xpath->attr('content').' instead of @twitterhandler';
        $validator = Validation::createValidator();
        $violations = $validator->validate($xpath->attr('content'), [
            new Assert\Regex([
                'pattern' => '/@\w+/',
                'match' => true,
                'message' => "<$fieldTagName> format is wrong. $message",
            ])
        ]);

        $this->pushViolationsErrors(
            $violations,
            Error::SEVERITY_NORMAL
        );
    }
}
