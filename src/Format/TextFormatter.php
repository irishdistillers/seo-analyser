<?php declare(strict_types=1);

namespace SeoAnalyser\Format;

use SeoAnalyser\Sitemap\ResourceInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;

class TextFormatter implements FormatterInterface
{
    /**
     * @var boolean
     */
    private $hasErrors = false;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'text';
    }

    /**
     * {@inheritDoc}
     */
    public function extractErrors(Collection $sitemaps, OutputInterface $output)
    {
        /** @var \SeoAnalyser\Sitemap\Sitemap $sitemap */
        foreach ($sitemaps as $sitemap) {
            $this->printErrors($sitemap, $output);
            /** @var \SeoAnalyser\Sitemap\Location $location */
            foreach ($sitemap->getLocations() as $location) {
                $this->printErrors($location, $output);
            }
        }
    }

    /**
     * Outputs errors for a $resource
     *
     * @param  ResourceInterface $resource
     */
    private function printErrors(ResourceInterface $resource, OutputInterface $output)
    {
        if ($resource->hasErrors()) {
            $this->hasErrors = true;
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
     * {@inheritDoc}
     */
    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }
}
