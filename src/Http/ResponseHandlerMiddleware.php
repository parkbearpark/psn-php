<?php

namespace Tustin\PlayStation\Http;

use Tustin\PlayStation\Http\ResponseParser;
use GuzzleHttp\Psr7\Response;

use Tustin\PlayStation\Exception\PlayStationApiException;
use Tustin\PlayStation\Exception\UnauthorizedException;
use Tustin\PlayStation\Exception\NotFoundException;
use Tustin\PlayStation\Exception\AccessDeniedException;

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

    /**
     * Checks if the HTTP status code is successful.
     *
     * @param Response $response The response
     * @return bool
     */
    public function isSuccessful(Response $response) : bool
    {
        return $response->getStatusCode() < 400;
    }

    /**
     * Handles unsuccessful error codes by throwing the proper exception.
     *
     * @param Response $response The response
     * @return void
     */
    public function handleErrorResponse(Response $response, JsonStream $stream) : void
    {
        switch ($response->getStatusCode())
        {
            case 400:
                throw new PlayStationApiException($stream);
            case 401:
                throw new UnauthorizedException;
            case 403:
                throw new AccessDeniedException;
            case 404:
                throw new NotFoundException;
        }
    }
}