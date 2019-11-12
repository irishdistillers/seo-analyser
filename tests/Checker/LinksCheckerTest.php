<?php

namespace Tests\Checker;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Checker\LinksChecker;
use GuzzleHttp\Client as GuzzleClient;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Sitemap\Error;
use Symfony\Component\DomCrawler\Crawler;

class LinksCheckerTest extends TestCase
{
    const CURRENT_URL = 'http://www.chivas.com';

    const GENERATED_LINKS_LIST = [
        "/search?q=mango",
        "https://www.google.com",
        "http://123456siteunknownpapy.com",
        "https://www.bing.com",
        "https://www.bing.com",
        "mailto:pay@me.now",
        "bla bla bla",
        "javascript:void(0)",
        " ",
        "",
        ".",
        "/",
        "#",
        "whatever",
    ];

    protected $checker;

    public function setUp()
    {
        $guzzleClient = new GuzzleClient();
        $client = new Client($guzzleClient);

        $this->checker = new LinksChecker();
        $this->checker->setClient($client);
        $this->checker->setCurrentUrl(self::CURRENT_URL);
    }

    public function provideHtmlContent()
    {
        $html = '<html>';
        $html .= '<head><link rel="icon" href="http://shouldbeignored.com" sizes="512x512" type="image/png"></head>';
        
        $html .= '<body>';

        foreach (self::GENERATED_LINKS_LIST as $key => $item) {
            $html .= '<div class="whahahaha' . $key . ' "><funky_tag>';
            $html .= 'This is a <a href="' . $item . '">link</a><a>what?!</a>';
            $html .= '</div>';
        }

        $html .= '</body>';
        $html .= '</html>';

        return [
            [$html],
        ];
    }

    /**
     * @dataProvider provideHtmlContent
     */
    public function testCollectedPageLinks($html)
    {
        $crawler = new Crawler($html);

        $expectedList = $this->removeIgnoredLinks(
            self::GENERATED_LINKS_LIST,
            $this->checker::TERMS_TO_IGNORE
        );

        $collectedLinks = $this->checker->collectPageLinks($crawler);

        $this->assertCount(count($expectedList), $collectedLinks);
        $this->assertEquals(
            array_values($collectedLinks),
            array_values($collectedLinks)
        );
    }

    /**
     * @dataProvider provideHtmlContent
     */
    public function testValidatePageLinks($html)
    {
        $crawler = new Crawler($html);

        $collectedLinks = $this->checker->collectPageLinks($crawler);

        $errors = $this->checker->validatePageLinks($collectedLinks);

        dump($errors);

        // $this->assertCount(count($expectedList), $collectedLinks);
        // $this->assertEquals(
        //     array_values($collectedLinks),
        //     array_values($collectedLinks)
        // );
    }

    private function removeIgnoredLinks(array $generatedLinks, array $termsToIgnore): array
    {
        foreach ($generatedLinks as $key => $item) {
            if (in_array($item, $termsToIgnore)) {
                unset($generatedLinks[$key]);
            }
        }

        return array_unique($generatedLinks);
    }
}
