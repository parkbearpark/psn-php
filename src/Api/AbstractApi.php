<?php

namespace PlayStation\Api;

use PlayStation\Client;
use PlayStation\Http\HttpClient;
use PlayStation\Http\ResponseParser;

abstract class AbstractApi 
{
    protected $client;

    public function __construct(Client $client) 
    {
        $this->client = $client;
    }
}