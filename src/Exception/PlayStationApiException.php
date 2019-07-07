<?php

namespace Tustin\PlayStation\Exception;

use Tustin\PlayStation\Http\JsonStream;

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
        else if (isset($data->errors) && is_array($data->errors) && count($data->errors) > 0)
        {
            // Just grab the first error.
            $error = $data->errors[0];

            $code = $error->code;
            $message = $error->message;
        }

        if (isset($message, $code))
        {
            parent::__construct($message, $code);
        }
    }
}