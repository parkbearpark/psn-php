<?php

namespace Tustin\PlayStation\Api;

use Tustin\PlayStation\Client;
use Tustin\PlayStation\Http\HttpClient;
use Tustin\PlayStation\Http\ResponseParser;

abstract class AbstractApi 
{
    protected $client;

    public function __construct(Client $client) 
    {
        $this->client = $client;
    }
}