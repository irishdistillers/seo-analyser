<?php

namespace Tests\Format;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Exception\InvalidOptionException;
use SeoAnalyser\Format\FormatterFactory;

class FormatFactoryTest extends TestCase
{
    public function testGetFormatterSuccess()
    {
        $formatter = new DummyFormatter;
        $factory = new FormatterFactory;
        $factory->addFormatter($formatter);

        $this->assertSame($formatter, $factory->getFormatter('dummy'));
    }

    public function testGetFormatterNotFound()
    {
        $formatter = new DummyFormatter;
        $factory = new FormatterFactory;
        $factory->addFormatter($formatter);

        $this->expectException(InvalidOptionException::class);
        $factory->getFormatter('non-existent');
    }
}
