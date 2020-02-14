<?php
namespace Tustin\PlayStation\Iterator;

use RuntimeException;
use InvalidArgumentException;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Api\Model\Story;

class FeedIterator extends AbstractApiIterator
{
    protected bool $includeComments = false;

    protected int $limit = 10;
    
    protected User $user;
    
    public function __construct(User $user, bool $includeComments, int $limit = 10)
    {
        if ($limit <= 0)
        {
            throw new InvalidArgumentException('$limit must be greater than zero.');
        }

        parent::__construct($user->httpClient);
        $this->user = $user;
        $this->includeComments = $includeComments;
        $this->limit = $limit;
        $this->access(0);
    }

    public function access($cursor) : void
    {
        // I don't think the API actually cares what page is set. It seems to just use the offset either way.
        $results = $this->get('https://activity.api.np.km.playstation.net/activity/api/v2/users/' . $this->user->onlineId() . '/feed/1', [
            'includeComments' => $this->includeComments,
            'offset' => $cursor,
            'blockSize' => $this->limit
        ]);

        $this->lastBlock = $results->lastBlock;

        // Because why would you include the total amount of items Sony???
        $this->update(-1, $results->feed);
    }

    /**
     * Do not use. Total results is not possible in the FeedIterator.
     * 
     * Will always throw RuntimeException.
     *
     * @return integer
     * @throws RuntimeException
     */
    public function getTotalResults() : int
    {
        throw new RuntimeException("getTotalResults is not supported by the feed API.");
    }

    public function offsetExists($offset) : bool
    {
        return !$this->lastBlock;
    }

    public function current()
    {
        return new Story(
            $this->getFromOffset($this->currentOffset),
        );
    }
}