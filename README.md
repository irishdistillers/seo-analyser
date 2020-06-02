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

```
Usage:
  analyse [options] [--] <sitemap_url>

Arguments:
  sitemap_url              Sitemap URL

Options:
  -a, --auth=AUTH          user:pwd@host (multiple values allowed)
  -c, --checkers=CHECKERS  List of checkers to use (see the `list-checkers` command for a full list) (multiple values allowed)
  -f, --format[=FORMAT]    Output format (text, xml, json, html) [default: "text"]
  -o, --output[=OUTPUT]    Write to file
```

Adding new checks
-----------------

Create a new class in `src\Checker` that implements `SeoAnalyser\Checker\CheckerInterface`.

The `check()` method receives a `Symfony\Component\DomCrawler\Crawler` with the HTML of a page and should return a
`Tightenco\Collect\Support\Collection` of `SeoAnalyser\Resource\Error`, one for each infraction. If no errors found
simply return an empty collection. Each `Error` contains a description and a severity: `SEVERITY_LOW`,
`SEVERITY_NORMAL`, or `SEVERITY_HIGH`.