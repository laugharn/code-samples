<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Page;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class ScrapePage extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * The slug of the page to scrape
     *
     * @var string
     */
    protected $slug;

    /**
     * The title of the page as seen in the sitemap <ul>
     *
     * @var string
     */
    protected $title;

    /**
     * Creates a new job instance.
     *
     * @return void
     */
    public function __construct($link)
    {
        $this->slug =  $link['slug'];
        $this->title = $link['title'];
    }

    /**
     * Executes the job.
     *
     * @return void
     */
    public function handle(Client $guzzle)
    {
        // The home page is too delicate, we'll handle it manually.
        if ($this->title !== 'Home') {

            // Explodes and collects the slug, throwing out any empty values, and removing the file extension.
            $slug_exploded = collect(explode('/', $this->slug))->reject(function ($value, $key) {
                return empty($value);
            })->map(function ($value, $key) {
                return str_slug(collect(explode('.', $value))->first());
            });

            // Gets the post_name attribute for the page.
            $post_name = $slug_exploded->last();

            // Makes an HTTP request of the live version of the page, and converts the response to a DOMCrawler object.
            $response = $guzzle->get(env('SITE_URL') . $this->slug);
            $crawler = new Crawler((string) $response->getBody());

            // The attributes for the page.
            $attributes = [
                'post_author' => 1,
                'post_content' => $crawler->filter('article.content')->first()->html(),
                'post_name' => $post_name,
                'post_title' => $this->title,
                'post_type' => 'page'
            ];

            // The meta properties for the page.
            $meta = [
                'description' => ($crawler->filterXpath('//meta[@name="description"]')->count() ? $crawler->filterXpath('//meta[@name="description"]')->attr('content') : NULL),
                'keywords' => ($crawler->filterXpath('//meta[@name="keywords"]')->count() ? $crawler->filterXpath('//meta[@name="keywords"]')->attr('content') : NULL),
                'old_slug' => $this->slug,
                'robotsmeta' => 'index,follow',
                'title' => collect(explode(' | ', $crawler->filter('title')->first()->text()))->first()
            ];

            // Initializes the page object
            $page = new Page;

            // Iterates through the page attributes and assigns them to the page object.
            collect($attributes)->each(function($value, $key) use($page){
                $page->{$key} = $value;
            });

            // Saves the page object to the database, allows us to attach meta.
            $page->save();

            // Iterates through the page meta properties and assigns them to the page object.
            collect($meta)->each(function($value, $key) use($page){
                $page->meta->{$key} = $value;
            });

            // Saves the page object again because you can never be too careful with these things.
            $page->save();

            // Checks to see if a parent page slug exists, if not returns an empty string.
            $parent_post_name = $slug_exploded->take(-2)->reject(function($value, $key) use($post_name){
                return $value == $post_name;
            })->first();

            // Makes a database lookup to find a parent page object, returns null if nothing is found.
            $parent = Page::where('post_name', $parent_post_name)->first();

            // Attaches the ID of the parent page object if it exists, and saves again for good measure.
            $page->post_parent = ($parent->ID ?? 0);
            $page->save();
        }
    }
}
