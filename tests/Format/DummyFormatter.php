<?php declare(strict_types=1);

namespace Tests\Format;

use SeoAnalyser\Sitemap\ResourceInterface;
use SeoAnalyser\Format\FormatterInterface;
use Tightenco\Collect\Support\Collection;
use Symfony\Component\Console\Output\OutputInterface;

class DummyFormatter implements FormatterInterface
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'dummy';
    }

    /**
     * {@inheritDoc}
     */
    public function extractErrors(Collection $sitemaps, OutputInterface $output)
    {
        /** @var \SeoAnalyser\Sitemap\Sitemap $sitemap */
        foreach ($sitemaps as $sitemap) {
            $this->storeErrors($sitemap);
            /** @var \SeoAnalyser\Sitemap\Location $location */
            foreach ($sitemap->getLocations() as $location) {
                $this->storeErrors($location);
            }
        }
    }

    /**
     * Outputs errors for a $resource
     *
     * @param  ResourceInterface $resource
     */
    private function storeErrors(ResourceInterface $resource)
    {
        if ($resource->hasErrors()) {
            $errors = [
                'url' => $resource->getUrl(),
                'errors' => []
            ];

            foreach ($resource->getErrors() as $error) {
                $errors['errors'] = [$error->getSeverity(), $error->getDescription()];
            }

            $this->errors[] = $errors;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
