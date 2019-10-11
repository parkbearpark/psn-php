<?php

namespace Tustin\PlayStation\Api\Community;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\AbstractApi;
use Tustin\PlayStation\Api\User;

use Tustin\PlayStation\Api\Community\Message;

class Message extends AbstractApi 
{
    private $message;
    private $thread;

    public function __construct(Client $client, object $message, Thread $thread) 
    {
        parent::__construct($client);

        $this->message = $message;
        $this->thread = $thread;
    }

    /**
     * Get the raw message JSON data.
     *
     * @return \stdClass
     */
    public function info() : \stdClass
    {
        return $this->message;
    }

    /**
     * Get the id of the message.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->info()->id;
    }

    /**
     * Get the message contents.
     *
     * @return string
     */
    public function message() : string
    {
        return $this->info()->message;
    }

    /**
     * Get the creation date and time of the message.
     *
     * @return \DateTime
     */
    public function posted() : \DateTime
    {
        return new \DateTime($this->info()->creationTimestamp);
    }

    /**
     * Get the user who posted the message.
     *
     * @return \Tustin\PlayStation\Api\User
     */
    public function poster() : User
    {
        return new User($this->client, $this->info()->author->onlineId);
    }

    /**
     * Get the thread that this message is apart of.
     *
     * @return \Tustin\PlayStation\Api\Community\Thread
     */
    public function thread() : Thread
    {
        return $this->thread;
    }

    /**
     * Delete this message.
     * 
     * You need permissions to delete the message (either need to be the original poster or a moderator of the community). 
     *
     * @return bool Whether the message was successfully deleted or not.
     */
    public function delete() : bool
    {
        // Note: This might throw a warning/error because permissions can apparently be empty? Need to look into this.
        if (!$this->info()->permissions->delete) return false;

        $this->delete(sprintf(self::COMMUNITY_ENDPOINT . 'communities/%s/threads/%s/messages/%s', $this->thread()->community()->id(), $this->thread()->id(), $this->id()));

        return true;
    }
}