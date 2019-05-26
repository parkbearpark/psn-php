<?php

namespace PlayStation\Exception;

use PlayStation\Http\JsonStream;

class PlayStationApiException extends \Exception
{
    public function __construct(JsonStream $stream)
    {
        $data = $stream->jsonSerialize();

        // This dumb check is here because Sony is inconsistent with their error responses
        // Some of them are different depending on which API endpoint you call.
        // So we need to try to parse out which one it is.
        if (isset($data->error_description, $data->error_code))
        {
            $message = $data->error_description;
            $code = $data->error_code;
        }
        else if (isset($data->data, $data->code))
        {
            $message = $data->data;
            $code = $data->code;
        }

        if (isset($message, $code))
        {
            parent::__construct($message, $code);
        }
    }
}