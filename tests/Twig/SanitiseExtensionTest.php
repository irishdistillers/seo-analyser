<?php

namespace Tests\Format;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Twig\SanitiseExtension;

class SanitiseExtensionTest extends TestCase
{
    public function testSanitise()
    {
        $extension = new SanitiseExtension;

        $this->assertEquals(
            'https-www-ballantines-com-es-ES-article-boiler-room-ballantines-nairobi',
            $extension->sanitiseId('https://www.ballantines.com/es-ES/article/boiler-room-ballantines-nairobi')
        );
    }
}
