<?php
namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;

class Message extends Model
{
    private object $eventData;

    public function __construct(Client $client, object $eventData)
    {
        parent::__construct($client);

        $this->eventData = $eventData;
    }

    public function body() : string
    {
        return $this->eventData->messageDetail->body;
    }
}