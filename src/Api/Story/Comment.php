<?php

namespace Tustin\PlayStation\Api\Story;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\AbstractApi;
use Tustin\PlayStation\Api\User;
use Tustin\PlayStation\Api\Story\Story;

class Comment extends AbstractApi 
{
    private $comment;
    private $story;

    public function __construct(Client $client, object $comment, Story $story) 
    {
        parent::__construct($client);
        $this->comment = $comment;

        $this->story = $story;        
    }

    /**
     * Gets the comment's parent story.
     *
     * @return \Tustin\PlayStation\Api\Story
     */
    public function story() : Story 
    {
        return $this->story;
    }

    /**
     * Gets the info for the Comment.
     *
     * @return object
     */
    public function info() : \stdClass
    {
        return $this->comment;
    }

    /**
     * Gets the comment poster.
     *
     * @return \Tustin\PlayStation\Api\User
     */
    public function user() : User
    {
        return new User($this->client, $this->info()->onlineId);
    }

    /**
     * Gets the comment message text.
     *
     * @return string
     */
    public function comment() : string
    {
        return $this->info()->commentString;
    }

    /**
     * Gets the comment ID.
     *
     * @return string
     */
    public function commentId() : string
    {
        return $this->info()->commentId;
    }

    /**
     * Gets the date and time the comment was posted.
     *
     * @return \DateTime
     */
    public function postDate() : \DateTime
    {
        return new \DateTime($this->info()->date);
    }
}