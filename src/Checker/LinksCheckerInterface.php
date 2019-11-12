<?php

declare(strict_types=1);

namespace SeoAnalyser\Checker;

use SeoAnalyser\Http\Client;

interface LinksCheckerInterface
{
    /**
     * @param Client $client
     */
    public function setClient(Client $client);

    /**
     * @param string $url
     */
    public function setCurrentUrl(string $url);

    /**
     * @return string
     */
    public function getBaseUrl(): string;
}
