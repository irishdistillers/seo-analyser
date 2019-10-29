<?php declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Tightenco\Collect\Support\Collection;

class MetaDescriptionChecker implements CheckerInterface
{
    const LIMIT_CONTENT = 160;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * @var \Tightenco\Collect\Support\Collection
     */
    protected $errors;

    /**
     * {@inheritdoc}
     */
    public function check(Crawler $crawler): Collection
    {
        $this->errors = new Collection();
        $this->crawler = $crawler;

        $this->checkField('description');
        $this->checkField('og:description', 'property');
        $this->checkField('twitter:description');

        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    private function checkField(
        string $fieldTagName,
        string $fieldType = 'name'
    ) {
        $path = '//html/head/meta[@'.$fieldType.'="'.$fieldTagName.'"]';
        $xpath = $this->crawler->filterXPath($path);

        if (0 >= count($xpath)) {
            $this->errors->push(
                new Error(
                    "<$fieldTagName> tag is not available!",
                    Error::SEVERITY_HIGH
                )
            );

            return;
        }

        if (1 < count($xpath)) {
            $this->errors->push(
                new Error(
                    "Too many <$fieldTagName> tag! You should only have one!",
                    Error::SEVERITY_HIGH
                )
            );

            return;
        }

        $message = 'Only ' . strlen($xpath->attr('content')).' characters found.';

        $validator = Validation::createValidator();

        $violations = $validator->validate($xpath->attr('content'), [
            new Assert\Length([
                'min' => 10,
                'max' => self::LIMIT_CONTENT,
                'minMessage' => "<$fieldTagName> tag must be at least {{ limit }} characters long. $message",
                'maxMessage' => "<$fieldTagName> tag should not be longer than {{ limit }} characters. $message",
            ]),
        ]);

        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                $this->errors->push(
                    new Error(
                        $violation->getMessage(),
                        Error::SEVERITY_NORMAL
                    )
                );
            }
        }
    }
}
