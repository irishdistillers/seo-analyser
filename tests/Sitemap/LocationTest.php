<?php

namespace Tests\Sitemap;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Sitemap\Error;
use SeoAnalyser\Sitemap\Location;
use SeoAnalyser\Sitemap\Sitemap;
use Tightenco\Collect\Support\Collection;

class LocationTest extends TestCase
{
    public function testGetUrl()
    {
        $expected = 'http://example.com/page';
        $location = new Location($expected, new Sitemap('http://example.com/parent'));

        $this->assertEquals($expected, $location->getUrl());
    }

    public function testErrors()
    {
        $sitemap = new Sitemap('http://example.com/parent');
        $location = new Location('http://example.com', $sitemap);
        $this->assertFalse($location->hasErrors());

        $error = new Error('Foo', Error::SEVERITY_NORMAL);
        $location->addError($error);

        $this->assertTrue($location->hasErrors());

        // --

        $location = new Location('http://example.com', $sitemap);
        $error = new Error('Foo', Error::SEVERITY_NORMAL);

        $expected = new Collection([$error]);
        $location->addErrors($expected);
        $this->assertEquals($expected, $location->getErrors());
    }
}
