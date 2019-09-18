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
composer require irishdistillers/seo-analyser
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