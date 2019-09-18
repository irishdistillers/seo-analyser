<?php declare(strict_types=1);

namespace SeoAnalyser\Processor;

use Psr\Http\Message\ResponseInterface;
use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Sitemap\ResourceInterface;

trait CreateErrorTrait
{
    /**
     * @param  ResourceInterface $resource
     * @param  ResponseInterface $response
     */
    private function createRequestError(ResourceInterface $resource, ResponseInterface $response)
    {
        $resource->addError(
            new Error(sprintf(
                'Failed to load with HTTP status code %d',
                $response->getStatusCode()
            ), Error::SEVERITY_HIGH)
        );
    }
}
