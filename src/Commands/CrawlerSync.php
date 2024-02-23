<?php

namespace GregPriday\APICrawler\Commands;

use Illuminate\Console\Command;
use GregPriday\APICrawler\Crawler;
use GregPriday\APICrawler\Exchange;

class CrawlerSync extends Command
{
    protected $signature = 'crawler:sync';
    protected $description = "Crawl an API and sync it to Redis.";

    public function handle()
    {
        // Create a new crawler with the home URL as the starting point
        $crawler = new Crawler();
        $crawler
            ->each(function(Exchange $exchange){
                $this->info('Crawled: ' . $exchange->request->url());
            })
            ->each(function(Exchange $exchange){
                // This is where we will save to Redis.
            });
    }
}
