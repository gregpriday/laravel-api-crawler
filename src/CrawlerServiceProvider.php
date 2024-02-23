<?php

namespace GregPriday\APICrawler;

use Illuminate\Support\ServiceProvider;
use GregPriday\APICrawler\Commands\CrawlerSync;
use GregPriday\APICrawler\Commands\GenerateSitemap;

class CrawlerServiceProvider extends ServiceProvider
{

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CrawlerSync::class,
                GenerateSitemap::class
            ]);
        }
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/crawler.php' => config_path('crawler.php'),
        ], 'config');
    }
}
