<?php

namespace Tustin\PlayStation\Api\Story;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\AbstractApi;
use Tustin\PlayStation\Api\User;
use Tustin\PlayStation\Api\Game;
use Tustin\PlayStation\Api\Story\Comment;

class Story extends AbstractApi 
{
    public const ACTIVITY_ENDPOINT    = 'https://activity.api.np.km.playstation.net/activity/api/';

    private $story;
    private $user;

    public function __construct(Client $client, object $story, User $user) 
    {
        parent::__construct($client);
        
        $this->story = $story;
        $this->user = $user;        
    }

    /**
     * Gets the info for the story.
     *
     * @return object
     */
    public function info() : \stdClass
    {
        return $this->story;
    }

    /**
     * Gets the user who posted the story.
     *
     * @return \Tustin\PlayStation\Api\User
     */
    public function user() : User
    {
        return $this->user;
    }

    /**
     * Gets the story ID.
     *
     * @return string
     */
    public function storyId() : string
    {
        return $this->info()->storyId;
    }

    /**
     * Gets the Story type.
     *
     * @return string
     */
    public function storyType() : string
    {
        return $this->info()->storyType;
    }

    /**
     * Gets the title ID for the game the story is for.
     *
     * @return string
     */
    public function titleId() : string
    {
        return $this->info()->titleId;        
    }

    /**
     * Checks if the logged in user has liked this story.
     *
     * @return bool
     */
    public function liked() : bool
    {
        return $this->info()->liked;        
    }

    /**
     * Gets the post date for the Story.
     *
     * @return \DateTime
     */
    public function postDate() : \DateTime
    {
        return new \DateTime($this->info()->date);
    }

    /**
     * Gets the amount of comments on the story.
     *
     * @return int
     */
    public function commentCount() : int
    {
        return $this->info()->commentCount;
    }

    /**
     * Gets the amount of likes on the story.
     *
     * @return int
     */
    public function likeCount() : int
    {
        return $this->info()->likeCount;
    }

    /**
     * Generates the caption shown on PlayStation.
     *
     * @return string
     */
    public function caption() : string
    {
        $template = $this->info()->captionTemplate;

        foreach ($this->info()->captionComponents as $variable) {
            $template = str_replace('$' . $variable->key, $variable->value, $template);
        }

        return $template;
    }

    /**
     * Gets the game the story is for.
     *
     * @return \Tustin\PlayStation\Api\Game
     */
    public function Game() : Game
    {
        return new Game($this->client, $this->titleId(), $this->user());
    }

    /**
     * Leave a comment on the story.
     *
     * @param string $message The comment.
     * @return \Tustin\PlayStation\Api\Comment|null
     */
    public function comment(string $message) : ?Comment
    {
        $comment = $this->postJson(sprintf(self::ACTIVITY_ENDPOINT . 'v1/users/me/comment/%s', $this->storyId()), [
            'commentString' => $message
        ]);

        // Since I couldn't find an endpoint that gave me a comment's info using just it's comment id, let's just grab the newest comment.
        $newest = $this->comments(0, 1, 'ASC');

        if (count($newest) === 0) return null;
        
        return $newest[0];
    }

    /**
     * Gets all the comments for the story.
     *
     * @param int $start Which comments to start from.
     * @param int $count How many comments to get.
     * @param string $sort How comments are sorted (ASC/DESC).
     * @return array Array of \Tustin\PlayStation\Api\Comment.
     */
    public function comments(int $start = 0, int $count = 10, string $sort = 'ASC') : array
    {
        $returnComments = [];

        if ($this->commentCount() === 0) return $returnComments;

        $comments = $this->get(sprintf(self::ACTIVITY_ENDPOINT . 'v1/users/%s/stories/%s/comments', $this->user()->onlineIdParameter(), $this->storyId()), [
            'start' => $start,
            'count' => $count,
            'sort' => $sort
        ]);

        foreach ($comments->userComments as $comment) {
            $returnComments[] = new Comment($this->client, $comment, $this);
        }
        
        return $returnComments;
    }

}