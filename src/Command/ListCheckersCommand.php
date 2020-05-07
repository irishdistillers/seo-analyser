<?php declare(strict_types=1);

namespace SeoAnalyser\Command;

use SeoAnalyser\Processor\LocationProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCheckersCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'list-checkers';

    /**
     * @var \SeoAnalyser\Processor\LocationProcessor
     */
    private $locationProcessor;

    /**
     * @param LocationProcessor $locationProcessor
     */
    public function __construct(
        LocationProcessor $locationProcessor
    ) {
        parent::__construct();

        $this->locationProcessor = $locationProcessor;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Available checkers:');
        $this->locationProcessor->getCheckers()->each(function ($checker) use ($output) {
            $output->writeln(' - '.$checker->getName());
        });
    }
}
