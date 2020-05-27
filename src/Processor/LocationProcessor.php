<?php declare(strict_types=1);

namespace SeoAnalyser\Processor;

use SeoAnalyser\Sitemap\Sitemap;
use GuzzleHttp\Exception\RequestException;
use SeoAnalyser\Checker\CheckerInterface;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Sitemap\Location;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Tightenco\Collect\Support\Collection;

class LocationProcessor
{
    use CreateErrorTrait;

    /**
     * @var \SeoAnalyser\Http\Client
     */
    private $client;

    /**
     * @var Collection
     */
    private $checkers;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->checkers = new Collection;
    }

    /**
     * @param CheckerInterface $checker
     */
    public function addChecker(CheckerInterface $checker)
    {
        $this->checkers->push($checker);
    }

    public function getCheckers(): Collection
    {
        return $this->checkers;
    }

    /**
     * Disables all checkers but the ones described in `$checkersToKeep`.
     * Elements of the `$checkersToKeep` array should be the name of the checker minus the `Checker` bit.
     *
     * @param  array  $checkersToKeep
     */
    public function filterCheckers(array $checkersToKeep)
    {
        $this->checkers = $this->checkers->filter(function ($checker) use ($checkersToKeep) {
            return in_array($checker->getName(), $checkersToKeep);
        });
    }

    /**
     * @param  Location        $location
     * @param  OutputInterface $output
     */
    public function process(Location $location, OutputInterface $output)
    {
        $output->writeln('Retrieving '.$location->getUrl(), OutputInterface::VERBOSITY_VERBOSE);

        try {
            $response = $this->client->get($location->getUrl());
            if ($response->getStatusCode() !== 200) {
                $this->createRequestError($location, $response);
                return ;
            }

            $crawler = new Crawler((string) $response->getBody());

            foreach ($this->checkers as $checker) {
                $location->addErrors($checker->check($crawler));
            }
        } catch (RequestException $exception) {
            $this->createRequestError($location, $exception->getResponse());
        }
    }
}
