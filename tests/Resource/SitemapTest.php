<?php

namespace Tests\Resource;

use PHPUnit\Framework\TestCase;
use SeoAnalyser\Resource\Error;
use SeoAnalyser\Resource\Location;
use SeoAnalyser\Resource\Sitemap;
use Tightenco\Collect\Support\Collection;

class SitemapTest extends TestCase
{
    public function testGetUrl()
    {
        $expected = 'http://example.com';
        $sitemap = new Sitemap($expected);

        $this->assertEquals($expected, $sitemap->getUrl());
    }

    public function testLocations()
    {
        $sitemap = new Sitemap('http://example.com');
        $this->assertEmpty($sitemap->getLocations());

        $location = new Location('http://example.com/location', $sitemap);
        $sitemap->addLocation($location);

        $this->assertCount(1, $sitemap->getLocations());
        $this->assertEquals($location, $sitemap->getLocations()->pop());
    }

    public function testErrors()
    {
        $sitemap = new Sitemap('http://example.com');
        $this->assertFalse($sitemap->hasErrors());

        $error = new Error('Foo', Error::SEVERITY_NORMAL);
        $sitemap->addError($error);

        $this->assertTrue($sitemap->hasErrors());

        // --

        $sitemap = new Sitemap('http://example.com');
        $error = new Error('Foo', Error::SEVERITY_NORMAL);

        $expected = new Collection([$error]);
        $sitemap->addErrors($expected);
        $this->assertEquals($expected, $sitemap->getErrors());
    }

    public function testParent()
    {
        $parent = new Sitemap('http://example.com/sitemap.xml');
        $child = new Sitemap('http://example.com/en-EN/sitemap.xml');

        $this->assertFalse($child->hasParent());
        $child->setParent($parent);
        $this->assertTrue($child->hasParent());
        $this->assertSame($parent, $child->getParent());
    }
}
