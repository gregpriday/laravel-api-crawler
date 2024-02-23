<?php

namespace GregPriday\APICrawler;

use Illuminate\Support\Str;

class Page
{
    public string $url;
    public int $depth; // Add depth attribute

    public function __construct(string $url, int $depth = 0)
    {
        $this->url = self::urlToPath($url);
        $this->depth = $depth; // Initialize depth
    }

    /**
     * Converts a URL to a path, if it's relative to the
     *
     * @param $url
     * @return string|bool
     */
    public static function urlToPath($url)
    {
        // Remove the http://host part from any url using parse_url, keep the path and query string
        $url = parse_url($url);
        $url = (!empty($url['path']) ? $url['path'] : '/') .
            (!empty($url['query']) ? '?' . $url['query'] : '');

        if(empty($url)) return '/';
        else return $url;
    }
}
