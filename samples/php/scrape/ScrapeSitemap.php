<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Jobs\ScrapePage;
use GuzzleHttp\Client;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeSitemap extends Job
{
    use SerializesModels;

    /**
     * The filter we want to use to acquire our page
     *
     * @var string
     */
    protected $filter;

    /**
     * The URL of the sitemap
     *
     * @var string
     */
    protected $url;

    /**
     * Creates a new job instance.
     *
     * @return void
     */
    public function __construct($url, $filter)
    {
        $this->filter = $filter;
        $this->url = $url;
    }

    /**
     * Executes the job.
     *
     * @return void
     */
    public function handle(Client $guzzle)
    {
        // Make the HTTP GET request to the sitemap
        $response = $guzzle->get($this->url);

        // Parse the HTML response into an object
        $crawler = new Crawler((string) $response->getBody());

        // Collect and iterate through the pages in the sitemap
        collect($crawler->filter($this->filter)->extract(['href', '_text']))->each(function($link) {

            // Enqueue each page to be scraped individually
            dispatch(new ScrapePage([
                'slug' => $link[0],
                'title' => $link[1]
            ]));
        });
    }
}
