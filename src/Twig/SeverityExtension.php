<?php declare(strict_types=1);

namespace SeoAnalyser\Twig;

use SeoAnalyser\Resource\Error;
use SeoAnalyser\Resource\ResourceInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeverityExtension extends AbstractExtension
{
    /** @codeCoverageIgnoreStart */
    public function getFunctions()
    {
        return [
            new TwigFunction('severity_badge', [$this, 'severityBadge'], ['is_safe' => ['html']]),
            new TwigFunction('severity_counter', [$this, 'severityCounter'], ['is_safe' => ['html']])
        ];
    }
    /** @codeCoverageIgnoreEnd */

    /**
     * @param  string      $severity
     * @param  string|null $text
     * @return string
     */
    public function severityBadge(string $severity, string $text = null): string
    {
        $map = [
            Error::SEVERITY_LOW => 'info',
            Error::SEVERITY_NORMAL => 'warning',
            Error::SEVERITY_HIGH => 'danger',
        ];

        return sprintf(
            '<span class="badge badge-%s">%s</span>',
            $map[$severity],
            !empty($text) ? $text : $severity
        );
    }

    /**
     * @param  ResourceInterface $resource
     * @return string
     */
    public function severityCounter(ResourceInterface $resource): string
    {
        $badges = [];
        foreach ([Error::SEVERITY_HIGH, Error::SEVERITY_NORMAL, Error::SEVERITY_LOW] as $severity) {
            $count = $resource->getErrors()->sum(function (Error $error) use ($severity) {
                return $severity === $error->getSeverity() ? 1 : 0;
            });

            if ($count > 0) {
                $badges[] = $this->severityBadge($severity, sprintf("%s: %d", $severity, $count));
            }
        }

        return implode(' ', $badges);
    }
}
