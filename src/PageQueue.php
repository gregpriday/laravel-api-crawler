<?php

namespace GregPriday\APICrawler;

class PageQueue
{
    private array $pages;
    private array $urls;

    public function __construct(array $urls)
    {
        $this->pages = [];
        $this->urls = [];

        if(!is_array($urls)) $urls = [$urls];

        $this->push(array_map(fn($url) => new Page($url), $urls));
    }

    /**
     * @param array|mixed $urls
     * @return \GregPriday\APICrawler\PageQueue
     */
    public function push(array $pages): PageQueue
    {
        foreach ($pages as $page) {
            // Skip URLs that are already in the queue
            if(isset($this->urls[$page->url])) continue;

            $this->pages[] = $page;
            $this->urls[$page->url] = $page;
        }

        return $this;
    }

    public function shift(): Page
    {
        return array_shift($this->pages);
    }

    public function isEmpty(): bool
    {
        return empty($this->pages);
    }

}
