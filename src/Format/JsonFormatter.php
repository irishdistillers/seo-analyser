<?php declare(strict_types=1);

namespace SeoAnalyser\Format;

use Tightenco\Collect\Support\Collection;
use Symfony\Component\Console\Output\OutputInterface;

class JsonFormatter implements FormatterInterface
{
    private $hasErrors = false;
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'json';
    }

    /**
     * {@inheritDoc}
     */
    public function extractErrors(Collection $sitemaps, OutputInterface $output)
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $output->writeln($serializer->serialize($sitemaps->all(), 'json'));

        
        /** @var \SeoAnalyser\Sitemap\Sitemap $sitemap */
        foreach ($sitemaps as $sitemap) {
            if ($sitemap->hasErrors()) {
                $this->hasErrors = true;
                break;
            }

            /** @var \SeoAnalyser\Sitemap\Location $location */
            foreach ($sitemap->getLocations() as $location) {
                if ($location->hasErrors()) {
                    $this->hasErrors = true;
                    break 2;
                }
            }
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
