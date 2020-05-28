<?php declare(strict_types=1);

namespace SeoAnalyser\Command;

use SeoAnalyser\Exception\InvalidOutputFileException;
use SeoAnalyser\Format\FormatterFactory;
use SeoAnalyser\Http\Client;
use SeoAnalyser\Processor\LocationProcessor;
use SeoAnalyser\Processor\SitemapProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
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
     * @var FormatterFactory
     */
    private $formatterFactory;

    /**
     * @param SitemapProcessor  $sitemapProcessor
     * @param LocationProcessor $locationProcessor
     * @param FormatterFactory  $formatterFactory
     * @param Client            $httpClient
     */
    public function __construct(
        SitemapProcessor $sitemapProcessor,
        LocationProcessor $locationProcessor,
        FormatterFactory $formatterFactory,
        Client $httpClient
    ) {
        parent::__construct();

        $this->sitemapProcessor = $sitemapProcessor;
        $this->locationProcessor = $locationProcessor;
        $this->httpClient = $httpClient;
        $this->formatterFactory = $formatterFactory;
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
                'List of checkers to use (see the `list-checkers` command for a full list)',
                []
            )
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format (Text, XML, JSON)', 'text')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Write to file')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->httpClient->configAuth($input->getOption('auth'));
        $formatter = $this->formatterFactory->getFormatter($input->getOption('format'));

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

        $sitemaps = $this->retrieveSitemaps($input->getArgument('sitemap_url'), $output);

        $reportOutput = $output;
        if (!empty($input->getOption('output'))) {
            $reportOutput = $this->prepareOutput($input->getOption('output'));
        }

        $formatter->extractErrors($sitemaps, $reportOutput);

        return $formatter->hasErrors() ? 1 : 0;
    }

    /**
     * @param  string          $url
     * @param  OutputInterface $output
     * @return Collection
     */
    public function retrieveSitemaps(string $url, OutputInterface $output): Collection
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

        foreach ($sitemaps as $sitemap) {
            foreach ($sitemap->getLocations() as $location) {
                $this->locationProcessor->process($location, $output);
            }
        }

        return $sitemaps;
    }

    protected function prepareOutput(string $path)
    {
        if (file_exists($path) && !is_writable($path)) {
            throw new InvalidOutputFileException(sprintf('File %s is not writable', $path));
        }

        return new StreamOutput(fopen($path, 'w', false));
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
