<?php

namespace GregPriday\APICrawler;

use Illuminate\Support\ServiceProvider;
use GregPriday\APICrawler\Commands\CrawlerSync;

class CrawlerServiceProvider extends ServiceProvider
{

    public function register()
    {
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/crawler.php' => config_path('crawler.php'),
        ], 'config');
    }
}
