<?php

namespace SiteOrigin\KernelCrawler\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SiteOrigin\KernelCrawler\Crawler;
use SiteOrigin\KernelCrawler\Exchange;
use SiteOrigin\KernelCrawler\Tests\App\Article;
use SiteOrigin\KernelCrawler\Tests\TestCase;

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
}