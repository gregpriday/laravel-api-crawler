<?php

namespace GregPriday\APICrawler\Commands;

use Closure;
use Illuminate\Console\Command;
use GregPriday\APICrawler\Crawler;
use GregPriday\APICrawler\Exchange;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\LazyCollection;

class CrawlerSync extends Command
{
    protected $signature = 'crawler:sync {--depth=} {--url=}';
    protected $description = "Crawl an API and sync it to Redis.";

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        // Create a new crawler with the home URL as the starting point

        $depth = $this->option('depth') ?? PHP_INT_MAX;
        $url = $this->option('url') ?? '/';

        $connection = Redis::connection('api');
        $crawler = new Crawler([$url], $depth);

        $this->output->progressStart();
        $crawler
            ->crawl()
            ->mapWithKeys(function(Exchange $exchange){
                // We want the full URL, without the host
                $key = $exchange->request->fullUrl();
                $key = str_replace($exchange->request->getSchemeAndHttpHost(), '', $key);
                $key = !empty($key) ? $key : '/';
                $data = $exchange->response->getData(true);

                return [$key => json_encode($data)];

            })
            ->chunk(20)
            ->each(function($data) use ($connection){
                $connection->mset($data->toArray());
                $this->output->progressAdvance($data->count());
            });

        $this->output->progressFinish();
    }
}
