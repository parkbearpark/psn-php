<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use IteratorAggregate;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Iterator\FeedIterator;
use Tustin\PlayStation\Interfaces\RepositoryInterface;

class FeedRepository extends Api implements RepositoryInterface, IteratorAggregate
{

    /**
     * The current user for this feed.
     *
     * @var User
     */
    private $user = null;

    /**
     * Include comments with each story item.
     *
     * @var boolean
     */
    private $includeComments = false;

    /**
     * The user you want to get the feed of.
     *
     * @param User $user
     * @return FeedRepository
     */
    public function forUser(User $user) : FeedRepository
    {
        $this->user = $user;

        return $this;
    }
    
    /**
     * Set whether or not to include comments for the stories in the feed.
     *
     * @param boolean $value
     * @return FeedRepository
     */
    public function includeComments(bool $value) : FeedRepository
    {
        $this->includeComments = $value;

        return $this;
    }

    /**
     * Gets whether or not to include comments with each story in the feed.
     *
     * @return boolean
     */
    public function getIncludeComments() : bool
    {
        return $this->includeComments;
    }

    /**
     * Gets the current users this feed is for.
     * 
     * If not set, this FeedRepository will return the client's feed.
     *
     * @return User
     */
    public function getUser() : User
    {
        return $this->user ??= (new UsersRepository($this->httpClient))->me();
    }

    public function getIterator() : Iterator
    {
        $iterator = new FeedIterator($this);

        return $iterator;
    }
}