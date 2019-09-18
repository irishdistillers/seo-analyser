<?php

namespace Tests\Sitemap;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Sitemap\Error;

class ErrorTest extends TestCase
{
    public function testGetters()
    {
        $description = 'Some sort of error';
        $severity = Error::SEVERITY_NORMAL;

        $error = new Error($description, $severity);

        $this->assertEquals($description, $error->getDescription());
        $this->assertEquals($severity, $error->getSeverity());
    }
}
