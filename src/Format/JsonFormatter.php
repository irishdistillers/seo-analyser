<?php declare(strict_types=1);

namespace SeoAnalyser\Format;

use JMS\Serializer\SerializerBuilder;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;

class JsonFormatter implements FormatterInterface
{
    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerBuilder $builder)
    {
        $this->serializer = $builder->build();
    }

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
        $output->writeln($this->serializer->serialize($sitemaps->all(), 'json'));
    }
}
