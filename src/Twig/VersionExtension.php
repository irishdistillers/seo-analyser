<?php declare(strict_types=1);

namespace SeoAnalyser\Twig;

use Jean85\PrettyVersions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VersionExtension extends AbstractExtension
{
    /** @codeCoverageIgnoreStart */
    public function getFunctions()
    {
        return [
            new TwigFunction('get_version', [$this, 'getVersion']),
        ];
    }
    /** @codeCoverageIgnoreEnd */

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $version = 'Version unknown';
        try {
            $version = PrettyVersions::getVersion('irishdistillers/seo-analyser')->getPrettyVersion();
        } catch (\OutOfBoundsException $e) {
        }

        return $version;
    }
}
