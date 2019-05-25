<?php

namespace PlayStation\Exception;

use PlayStation\Http\JsonStream;

class PlayStationApiException extends \Exception
{
    public function __construct(JsonStream $stream)
    {
        $data = $stream->jsonSerialize();
        $message = $data->error_description;
        $code = $data->error_code;

        parent::__construct($message, $code);
    }
}