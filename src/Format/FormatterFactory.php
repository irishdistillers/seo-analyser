<?php declare(strict_types=1);

namespace SeoAnalyser\Format;

use SeoAnalyser\Exception\InvalidOptionException;

class FormatterFactory
{
    /**
     * @var array
     */
    private $formatters = [];

    /**
     * @param FormatterInterface $formatter
     */
    public function addFormatter(FormatterInterface $formatter)
    {
        $this->formatters[$formatter->getName()] = $formatter;
    }

    /**
     * @param  string $type
     * @return FormatterInterface
     */
    public function getFormatter(string $type): FormatterInterface
    {
        if (empty($this->formatters[$type])) {
            throw new InvalidOptionException(sprintf('Unrecognized format "%s"', $type));
        }

        return $this->formatters[$type];
    }
}
