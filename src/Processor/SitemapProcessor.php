<?php declare(strict_types=1);

namespace SeoAnalyser\Processor;

use GuzzleHttp\Exception\RequestException;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Sitemap\Location;
use SeoAnalyser\Sitemap\Sitemap;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;

class SitemapProcessor
{
    use CreateErrorTrait;

    /**
     * @var \SeoAnalyser\Http\Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string          $url
     * @param  OutputInterface $output
     * @return Collection
     */
    public function process(string $url, OutputInterface $output): Collection
    {
        $output->writeln('Retrieving '.$url, OutputInterface::VERBOSITY_VERBOSE);
        $sitemaps = new Collection;

        $sitemap = new Sitemap($url);

        try {
            $response = $this->client->get($url);
            if ($response->getStatusCode() !== 200) {
                $this->createRequestError($sitemap, $response);
                return $sitemaps->merge([$sitemap]);
            }

            libxml_use_internal_errors(true);
            $doc = simplexml_load_string((string) $response->getBody());
            
            if ($doc === false) {
                $sitemap->addError(new Error('Malformed XML', Error::SEVERITY_HIGH));
                return $sitemaps->merge([$sitemap]);
            }
            
            foreach ($doc->sitemap as $docSitemap) {
                $sitemaps = $sitemaps->merge($this->process((string) $docSitemap->loc, $output));
            }

            foreach ($doc->url as $url) {#
                $sitemap->addLocation(new Location((string) $url->loc));
            }
        } catch (RequestException $exception) {
            $this->logExceptionError($sitemap, $exception);
        }
        
        return $sitemaps->merge([$sitemap]);
    }

    private function logExceptionError(Sitemap $sitemap, RequestException $exception)
    {
        if ($response = $exception->getResponse()) {
            $this->createRequestError($sitemap, $exception->getResponse());
            return;
        }
        
        $sitemap->addError(new Error($exception->getMessage(), Error::SEVERITY_HIGH));
    }
}
