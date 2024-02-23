<?php

namespace GregPriday\APICrawler;

use Closure;
use Generator;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Crawler
{

    public PageQueue $urlQueue;
    private HttpKernel $kernel;
    private int $maxDepth;

    public function __construct(array $startingUrls = ['/'], $maxDepth = PHP_INT_MAX)
    {
        $this->urlQueue = new PageQueue($startingUrls);
        $this->maxDepth = $maxDepth;
    }

    /**
     * Generator function for
     *
     * @return \Generator
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function crawl(): LazyCollection
    {
        return new LazyCollection(function(){
            if(empty($this->kernel)) $this->kernel = app()->make(HttpKernel::class);

            while (!$this->urlQueue->isEmpty()) {
                $page = $this->urlQueue->shift();
                $currentDepth = $page->depth;

                // Get the response
                $symfonyRequest = SymfonyRequest::create($page->url);
                $request = Request::createFromBase($symfonyRequest);

                $response = $this->kernel->handle($request);
                if($response instanceof JsonResponse) {
                    if ($currentDepth < $this->maxDepth) {
                        $this->addUrlsFromResponse($response, $currentDepth + 1);
                    }
                    yield new Exchange($request, $response);
                }
            }
        });
    }

    /**
     * Set the HttpKernel to use for requests.
     *
     * @param \Illuminate\Contracts\Http\Kernel|null $kernel
     */
    public function setKernel(HttpKernel $kernel = null): static
    {
        $this->kernel = $kernel;
        return $this;
    }

    /**
     * Add any new URLs discovered in this response.
     *
     * @param Response $response
     */
    protected function addUrlsFromResponse(JsonResponse $response, int $currentDepth): void
    {
        // Decode the JSON response
        $data = $response->getData(true);

        // This recursive function will search for all 'href' keys in the nested arrays
        $urls = [];
        $iterator = function ($array) use (&$iterator, &$urls) {
            foreach ($array as $key => $value) {
                if ($key === 'url' && is_string($value)) {
                    $urls[] = $value; // Add the URL to the list
                } elseif (is_array($value)) {
                    $iterator($value); // Recurse into the array
                }
            }
        };

        // Start the recursion
        $iterator($data);

        // Add unique URLs to the queue with incremented depth
        $urls = array_unique($urls);
        $urls = array_filter($urls, fn($url) => ! preg_match('/\.(jpg|jpeg|png|gif|bmp|svg)$/i', $url));

        foreach ($urls as $url) {
            $this->urlQueue->push([new Page($url, $currentDepth + 1)]);
        }
    }
}
