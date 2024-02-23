<?php

namespace GregPriday\APICrawler;

use Closure;
use Generator;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Crawler extends LazyCollection
{

    public PageQueue $urlQueue;
    private HttpKernel $kernel;

    public function __construct(array $startingUrls = ['/'])
    {
        $this->urlQueue = new PageQueue($startingUrls);
        parent::__construct(Closure::fromCallable([$this, 'crawl']));
    }

    /**
     * Generator function for
     *
     * @return \Generator
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function crawl(): Generator
    {
        if(empty($this->kernel)) $this->kernel = app()->make(HttpKernel::class);

        while (!$this->urlQueue->isEmpty()) {
            $item = $this->urlQueue->shift();

            // Get the response
            $symfonyRequest = SymfonyRequest::create(config('app.url') . $item->url);
            $request = Request::createFromBase($symfonyRequest);

            $response = $this->kernel->handle($request);
            if($response instanceof Response) {
                $this->addUrlsFromResponse($response);
                yield new Exchange($request, $response);
            }
        }
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
    protected function addUrlsFromResponse(Response $response): void
    {
        // Decode the JSON response
        $data = json_decode($response->getContent(), true);

        // This recursive function will search for all 'href' keys in the nested arrays
        $urls = [];
        $iterator = function ($array) use (&$iterator, &$urls) {
            foreach ($array as $key => $value) {
                if ($key === 'href' && is_string($value)) {
                    $urls[] = $value; // Add the URL to the list
                } elseif (is_array($value)) {
                    $iterator($value); // Recurse into the array
                }
            }
        };

        // Start the recursion
        $iterator($data);

        // Add unique URLs to the queue
        $this->urlQueue->push(array_unique($urls));
    }
}
