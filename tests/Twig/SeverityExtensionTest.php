<?php

namespace Tests\Format;

use SeoAnalyser\Resource\Error;
use SeoAnalyser\Resource\Location;
use PHPUnit\Framework\TestCase;
use SeoAnalyser\Resource\Sitemap;
use SeoAnalyser\Twig\SeverityExtension;
use Tightenco\Collect\Support\Collection;

class SeverityExtensionTest extends TestCase
{
    public function testSeverityBadge()
    {
        $extension = new SeverityExtension;

        $this->assertEquals(
            '<span class="badge badge-danger">High</span>',
            $extension->severityBadge(Error::SEVERITY_HIGH)
        );

        $this->assertEquals(
            '<span class="badge badge-danger">Danger</span>',
            $extension->severityBadge(Error::SEVERITY_HIGH, 'Danger')
        );
    }

    public function testSeverityCounter()
    {
        $location = new Location('http://example.com', new Sitemap('http://example.com/sitemap.xml'));

        $errors = new Collection;
        $errors->push(new Error('Foo1', Error::SEVERITY_LOW));
        $errors->push(new Error('Foo2', Error::SEVERITY_NORMAL));
        $errors->push(new Error('Foo3', Error::SEVERITY_NORMAL));
        $errors->push(new Error('Foo4', Error::SEVERITY_NORMAL));
        $errors->push(new Error('Foo4', Error::SEVERITY_HIGH));
        $errors->push(new Error('Foo5', Error::SEVERITY_HIGH));

        $location->addErrors($errors);

        $extension = new SeverityExtension;
        $this->assertEquals(
            '<span class="badge badge-danger">High: 2</span> <span class="badge badge-warning">Normal: 3</span> '.
            '<span class="badge badge-info">Low: 1</span>',
            $extension->severityCounter($location)
        );
    }
}
