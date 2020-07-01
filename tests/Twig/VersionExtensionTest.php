<?php

namespace Tests\Format;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Twig\VersionExtension;

class VersionExtensionTest extends TestCase
{
    public function testGetVersion()
    {
        $extension = new VersionExtension;

        $this->assertMatchesRegularExpression('/^dev-.+@[a-f0-9]+$/', $extension->getVersion());
    }
}
