<?php

namespace GregPriday\APICrawler\Tests\Unit;

use GregPriday\APICrawler\Crawler;
use GregPriday\APICrawler\Exchange;
use GregPriday\APICrawler\Tests\App\Article;
use GregPriday\APICrawler\Tests\TestCase;

class CrawlerTest extends TestCase
{
    public function test_crawling_site()
    {
        Article::factory()->count(10)->create();
        $crawler = new Crawler();
        $urls = [];
        $crawler->each(function(Exchange $r) use (& $urls){
            $urls[] = $r->request->url();
        });

        $this->assertCount(17, $urls);
    }

    public function test_crawl_command()
    {
        Article::factory()->count(10)->create();
        $c = $this->artisan('crawler:start', []);

        // Check at least 2 of the pages were crawled
        $c->expectsOutput('Crawled: ' . route('home'));
        $c->expectsOutput('Crawled: ' . route('articles.show', Article::all()->first()));
    }
}
