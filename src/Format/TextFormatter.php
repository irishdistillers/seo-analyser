<?php declare(strict_types=1);

namespace SeoAnalyser\Format;

use SeoAnalyser\Resource\ResourceInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;

class TextFormatter implements FormatterInterface
{
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
        foreach ($sitemaps as $sitemap) {
            $this->printErrors($sitemap, $output);
            foreach ($sitemap->getLocations() as $location) {
                $this->printErrors($location, $output);
            }
        }
    }

    /**
     * Outputs errors for a $resource
     *
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
}
