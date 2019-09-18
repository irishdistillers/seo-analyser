<?php

namespace Tests\Sitemap;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Sitemap\Location;
use Tightenco\Collect\Support\Collection;

class LocationTest extends TestCase
{
    public function testGetUrl()
    {
        $expected = 'http://example.com';
        $location = new Location($expected);

        $this->assertEquals($expected, $location->getUrl());
    }

    public function testErrors()
    {
        $location = new Location('http://example.com');
        $this->assertFalse($location->hasErrors());

        $error = new Error('Foo', Error::SEVERITY_NORMAL);
        $location->addError($error);

        $this->assertTrue($location->hasErrors());

        // --

        $location = new Location('http://example.com');
        $error = new Error('Foo', Error::SEVERITY_NORMAL);

        $expected = new Collection([$error]);
        $location->addErrors($expected);
        $this->assertEquals($expected, $location->getErrors());
    }
}
