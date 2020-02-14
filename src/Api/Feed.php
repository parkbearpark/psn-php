<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use GuzzleHttp\Client;
use IteratorAggregate;
use InvalidArgumentException;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Iterator\FeedIterator;

class Feed extends Api implements IteratorAggregate
{
    /**
     * Current user's feed.
     *
     * @var string|User
     */
    private $user = null;

    private $includeComments = false;
    
    /**
     * @param Client $client
     * @param string|User $user
     * @throws InvalidArgumentException
     */
    public function __construct(Client $client, $user)
    {
        parent::__construct($client);

        if (is_string($user))
        {
            $this->user = new User($client, $user);
        }
        else if ($user instanceof User)
        {
            $this->user = $user;
        }
        else
        {
            throw new InvalidArgumentException(
                "User parameter [$user] should be a string or instance of [" . User::class . "], is [" . get_class($user) . "]."
            );
        }
    }

    /**
     * Set whether or not to include comments for the stories in the feed.
     *
     * @param boolean $value
     * @return Feed
     */
    public function includeComments(bool $value) : Feed
    {
        $this->includeComments = $value;

        return $this;
    }

    public function getIterator() : Iterator
    {
        $iterator = new FeedIterator($this->user, $this->includeComments);

        return $iterator;
    }
}