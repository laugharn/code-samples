# Site Scraping

We took on a client who's previous developer was unwilling to turn over the content from their CMS for us to import into WordPress. Luckily, the content of their pages, aside from the home page, was consistently structured, and they had an up to date page at /sitemap.html that could server as a manifest.

I fired up a simple Laravel application that was largely just two parts: an event that crawled, parsed, and enqueued the pages on /sitemap.html and the event to scrape each enqueued page, parse out the content, and populate it on a WordPress wp_posts table, complete with hierarchy and SEO meta.

I used Laravel's dispatch() function and Jobs class to enqueue and fire the event. The WordPress integration is handled with the [Corcel](https://github.com/jgrossi/corcel) library.
