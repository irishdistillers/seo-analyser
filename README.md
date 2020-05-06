SEO Analyser
============

Installation
------------

Install globaly with

```
composer require -g irishdistillers/seo-analyser
```

or locally for a project with

```
composer require --dev irishdistillers/seo-analyser
```

Usage
-----

Run by pointing at the root sitemap file:

```
bin/seo-analyser analyse http://example.com/sitemap.xml
```

You can provide auth settings on a per-domain basis like

```
bin/seo-analyser analyse http://example.com/sitemap.xml -a user:pwd@domain.com -a user:pwd@subdomain.example.com
```

so that any requests to `domain.com` or `subdomain.example.com` will have basic authentication.

You can pick which checkers to use with the `-c` flag:

```
bin/seo-analyser analyse http://example.com/sitemap.xml -c H1 -c Title
```

Adding new checks
-----------------

Create a new class in `src\Checker` that implements `SeoAnalyser\Checker\CheckerInterface`.

The `check()` method receives a `Symfony\Component\DomCrawler\Crawler` with the HTML of a page and should return a
`Tightenco\Collect\Support\Collection` of `SeoAnalyser\Sitemap\Error`, one for each infraction. If no errors found
simply return an empty collection. Each `Error` contains a description and a severity: `SEVERITY_LOW`,
`SEVERITY_NORMAL`, or `SEVERITY_HIGH`.