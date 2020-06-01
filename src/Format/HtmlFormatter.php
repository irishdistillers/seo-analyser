<?php declare(strict_types=1);

namespace SeoAnalyser\Format;

use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;
use Twig\Environment;

class HtmlFormatter implements FormatterInterface
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'html';
    }

    /**
     * {@inheritDoc}
     */
    public function extractErrors(Collection $sitemaps, OutputInterface $output)
    {
        $template = $this->twig->load('report.html.twig');

        $output->write($template->render(['sitemaps' => $sitemaps]));
    }
}
