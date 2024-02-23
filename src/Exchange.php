<?php

namespace GregPriday\APICrawler;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Exchange
{
    public Request $request;
    public JsonResponse $response;

    public function __construct(Request $request, JsonResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
