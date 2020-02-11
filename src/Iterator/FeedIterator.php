<?php
namespace Tustin\PlayStation\Iterator;

use Iterator;
use RuntimeException;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Tustin\PlayStation\Api\Model\Story;
use Tustin\PlayStation\Filter\UserFilter;

class FeedIterator extends AbstractApiIterator
{
    protected bool $includeComments;

    protected int $limit;
    
    protected string $onlineId;
    
    public function __construct(Client $client, string $onlineId, bool $includeComments, int $limit)
    {
        if (empty($onlineId))
        {
            throw new InvalidArgumentException('$onlineId must contain a value.');
        }

        if ($limit <= 0)
        {
            throw new InvalidArgumentException('$limit must be greater than zero.');
        }

        parent::__construct($client);
        $this->onlineId = $onlineId;
        $this->includeComments = $includeComments;
        $this->limit = $limit;
        $this->access(0);
    }

    public function access($cursor)
    {
        // I don't think the API actually cares what page is set. It seems to just use the offset either way.
        $results = $this->get('https://activity.api.np.km.playstation.net/activity/api/v2/users/' . $this->onlineId . '/feed/1', [
            'includeComments' => $this->includeComments,
            'offset' => $cursor,
            'blockSize' => $this->limit
        ]);

        if ($results->lastBlock)
        {
            $this->lastBlock = true;
        }
        
        // Because why would you include the total amount of items Sony???
        $this->update(-1, $results->feed);
    }

    public function getTotalResults() : int
    {
        throw new RuntimeException("getTotalResults is not supported by the feed API.");
    }

    /**
     * Gets users whose onlineId contains the specified string.
     *
     * @param string $text
     * @return Iterator
     */
    public function containing(string $text) : Iterator
    {
        yield from new UserFilter($this, $text);
    }

    public function current()
    {
        return new Story(
            $this->getFromOffset($this->currentOffset),
        );
    }
}