<?php

namespace Tustin\PlayStation\Api\Messaging;

use Tustin\PlayStation\Client;
use Tustin\PlayStation\Api\User;

class Message extends AbstractApi 
{
    private $message;
    private $messageThread;

    public function __construct(Client $client, object $message, MessageThread $messageThread)
    {
        parent::__construct($client);

        $this->message = $message;
        $this->messageThread = $messageThread;
    }

    /**
     * Get the sender of the message.
     *
     * @return \Tustin\PlayStation\Api\User
     */
    public function sender() : User
    {
        return new User($this->client, $this->message->sener->onlineId);
    }

    /**
     * Get the message thread the message is in.
     *
     * @return \Tustin\PlayStation\Api\MessageThread
     */
    public function thread() : MessageThread
    {
        return $this->messageThread;
    }

    /**
     * Get the message body text.
     *
     * @return string
     */
    public function body() : string
    {
        return $this->message->messageDetail->body;
    }

    /**
     * Get the date and time when the message was sent.
     *
     * @return \DateTime
     */
    public function sendDate() : \DateTime
    {
        return new \DateTime($this->message->postDate);
    }
}