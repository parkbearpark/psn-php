<?php
namespace Tustin\PlayStation\Api\Message;

use Tustin\PlayStation\Api\Enum\MessageType;

abstract class AbstractMessage
{
    private MessageType $type;

    private array $messageData = [];

    protected function __construct(MessageType $type)
    {
        $this->type = $type;
    }

    public abstract function build() : array;

    public final function scaffold(array $messageDetail, array ...$parts) : array
    {
        $data = [
            'messageEventDetail' => [
                'eventCategoryCode' => $this->type,
                'messageDetail' => $messageDetail
            ]
        ];

        $primaryData = [
            [
                'name' => 'messageEventDetail',
                'contents' => json_encode($data, JSON_PRETTY_PRINT),
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8'
                ]
            ]
        ];

        if (isset($parts) && !empty($parts))
        {
            array_push($primaryData, $parts);
        }

        return $primaryData;
    }
}