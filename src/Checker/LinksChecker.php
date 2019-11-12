<?php

declare(strict_types=1);

namespace SeoAnalyser\Checker;

use \Exception;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;
use SeoAnalyser\Http\Client;
use \stdClass;

class LinksChecker implements CheckerInterface, LinksCheckerInterface
{
    const TERMS_TO_IGNORE = [
        'javascript:void(0)',
        '/',
        '#',
        " ",
        "",
        "."
    ];

    /**
     * @var \SeoAnalyser\Http\Client
     */
    private $client;

    /**
     * @var string
     */
    protected $currentUrl;

    public function check(Crawler $crawler): Collection
    {
        if (empty($this->client)) {
            throw new Exception('Http client not defined!');
        }

        if (empty($this->currentUrl)) {
            throw new Exception('Current url not defined!');
        }

        $collectedLinks = $this->collectPageLinks($crawler);

        $errors = new Collection();

        if (!empty($collectedLinks)) {
            $errors = $this->validatePageLinks($collectedLinks);
        }

        return $errors;
    }

    public function collectPageLinks(Crawler $crawler): array
    {
        $links = [];

        $crawler->filterXPath('//a/@href')->each(
            function (Crawler $node) use (&$links) {
                $text = $node->text();
                if (!empty($text)) {
                    $hashText = md5($text);
                    if (!in_array($text, self::TERMS_TO_IGNORE) && !empty($hashText)) {
                        $links["$hashText"] = $text;
                    }
                }
            }
        );

        return $links;
    }

    public function validatePageLinks(array $links): Collection
    {
        $errors = new Collection();
        
        foreach ($links as $key => $link) {
            if ($link[0] == '/') {
                $link = $this->getBaseUrl() . $link;
            }

            if ('http' == substr($link, 0, 4)) {
                $response = $this->client->get($link);

                if ($response->getStatusCode() !== 200) {
                    $errors->push(
                        new Error(
                            sprintf('Dead link: %d', $response->getStatusCode()),
                            Error::SEVERITY_HIGH
                        )
                    );
                }
            } else {
                $errors->push(
                    new Error(
                        sprintf('Link ignored %s', $link),
                        Error::SEVERITY_LOW
                    )
                );
            }
        }

        return $errors;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function setCurrentUrl(string $url)
    {
        $this->currentUrl = $url;
    }

    public function getBaseUrl(): string
    {
        $url = parse_url($this->currentUrl);

        if (!empty($url['host']) && !empty($url['scheme'])) {
            return $url['scheme'] . '://' . $url['host'];
        }

        return '';
    }
}
