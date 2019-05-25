<?php

namespace PlayStation\Http;

use PlayStation\Http\ResponseParser;
use GuzzleHttp\Psr7\Response;

use PlayStation\Exception\PlayStationApiException;
use PlayStation\Exception\UnauthorizedException;
use PlayStation\Exception\NotFoundException;

final class ResponseHandlerMiddleware
{
    private $accessToken;

    public function __invoke(Response $response, array $options = [])
    {
        $jsonStream = new JsonStream($response->getBody());

        if ($this->isSuccessful($response)) {
            return $response->withBody($jsonStream);
        }

        $this->handleErrorResponse($response, $jsonStream);
    }

    public function isSuccessful(Response $response)
    {
        return $response->getStatusCode() < 400;
    }

    /**
     * Handles unsuccessful error codes by throwing the proper exception.
     *
     * @param Response $response
     * @return void
     */
    public function handleErrorResponse(Response $response, JsonStream $stream)
    {
        switch ($response->getStatusCode())
        {
            case 400:
                throw new PlayStationApiException($stream);
            case 401:
                throw new UnauthorizedException;
            case 404:
                throw new NotFoundException;
        }
    }
}