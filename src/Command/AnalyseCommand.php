<?php declare(strict_types=1);

namespace SeoAnalyser\Command;

use SeoAnalyser\Exception\InvalidAuthOptionException;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Processor\LocationProcessor;
use SeoAnalyser\Processor\SitemapProcessor;
use SeoAnalyser\Sitemap\ResourceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;

class AnalyseCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'analyse';

    /**
     * @var \SeoAnalyser\Processor\SitemapProcessor
     */
    private $sitemapProcessor;

    /**
     * @var \SeoAnalyser\Processor\LocationProcessor
     */
    private $locationProcessor;

    /**
     * @var \SeoAnalyser\Http\Client
     */
    private $httpClient;

    /**
     * @param SitemapProcessor  $sitemapProcessor
     * @param LocationProcessor $locationProcessor
     */
    public function __construct(
        SitemapProcessor $sitemapProcessor,
        LocationProcessor $locationProcessor,
        Client $httpClient
    ) {
        parent::__construct();

        $this->sitemapProcessor = $sitemapProcessor;
        $this->locationProcessor = $locationProcessor;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('sitemap_url', InputArgument::REQUIRED, 'Sitemap URL')
            ->addOption('auth', 'a', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'user:pwd@host', [])
            ->addOption(
                'checkers',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'List of checkers to use (ie: -c H1 -c ImageAlt)',
                []
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->httpClient->configAuth($input->getOption('auth'));
        } catch (InvalidAuthOptionException $exception) {
            $output->writeln($exception->getMessage());
            return 1;
        }

        $filterCheckers = $input->getOption('checkers');
        if (!empty($filterCheckers)) {
            $this->locationProcessor->filterCheckers($filterCheckers);
        }

        $output->writeln(
            sprintf(
                'Using checkers: %s',
                implode(
                    ', ',
                    $this->locationProcessor->getCheckers()->map(function ($checker) {
                        return $checker->getName();
                    })->toArray()
                )
            )
        );

        $sitemaps = $this->processSitemaps($input->getArgument('sitemap_url'), $output);

        foreach ($sitemaps as $sitemap) {
            $this->printErrors($sitemap, $output);
        }

        $totalErrors = 0;
        foreach ($sitemaps as $sitemap) {
            foreach ($sitemap->getLocations() as $location) {
                $this->locationProcessor->process($location, $output);
                $this->printErrors($location, $output);

                $totalErrors += count($location->getErrors());
            }
        }

        $output->writeln(sprintf('%d URL errors found', $totalErrors));

        return ($this->countErrors($sitemaps) === 0 && $totalErrors === 0) ? 0 : 1;
    }

    /**
     * @param  string          $url
     * @param  OutputInterface $output
     * @return Collection
     */
    public function processSitemaps(string $url, OutputInterface $output): Collection
    {
        $output->writeln('Retrieving sitemaps');
        $sitemaps = $this->sitemapProcessor->process($url, $output);

        $totalUrls = $sitemaps->reduce(function ($carry, $sitemap) {
            return $carry + count($sitemap->getLocations());
        }, 0);

        $output->writeln(sprintf(
            'Found %d sitemaps with %d urls (%d errors)',
            count($sitemaps),
            $totalUrls,
            $this->countErrors($sitemaps)
        ));

        return $sitemaps;
    }

    /**
     * @param  ResourceInterface $resource
     * @param  OutputInterface   $output
     */
    private function printErrors(ResourceInterface $resource, OutputInterface $output)
    {
        if ($resource->hasErrors()) {
            $output->writeln(
                sprintf('Found %d errors for %s', count($resource->getErrors()), $resource->getUrl())
            );

            $table = new Table($output);
            $table->setHeaders(['Severity', 'Message']);

            foreach ($resource->getErrors() as $error) {
                $table->addRow([$error->getSeverity(), $error->getDescription()]);
            }

            $table->render();
            $output->writeln('');
        }
    }

    /**
     * @param  Collection $collection
     * @return int
     */
    private function countErrors(Collection $collection): int
    {
        return $collection->reduce(function ($carry, $resource) {
            return $carry + count($resource->getErrors());
        }, 0);
    }
}
