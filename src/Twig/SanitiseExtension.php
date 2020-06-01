<?php declare(strict_types=1);

namespace SeoAnalyser\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SanitiseExtension extends AbstractExtension
{
    /** @codeCoverageIgnoreStart */
    public function getFilters()
    {
        return [
            new TwigFilter('sanitise_id', [$this, 'sanitiseId']),
        ];
    }
    /** @codeCoverageIgnoreEnd */

    /**
     * @param  string $text
     * @return string
     */
    public function sanitiseId(string $text): string
    {
        return preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
    }
}
