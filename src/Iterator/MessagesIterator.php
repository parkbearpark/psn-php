<?php
namespace Tustin\PlayStation\Iterator;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Tustin\PlayStation\Api\Model\Message;

class MessagesIterator extends AbstractApiIterator
{
    protected string $threadId;

    protected int $limit;

    protected string $maxEventIndexCursor;
        
    public function __construct(Client $client, string $threadId, int $limit = 20)
    {
        if (empty($threadId))
        {
            throw new InvalidArgumentException('$threadId must not be empty.');
        }

        if ($limit <= 0)
        {
            throw new InvalidArgumentException('$limit must be greater than zero.');
        }

        parent::__construct($client);
        $this->threadId = $threadId;
        $this->limit = $limit;
        $this->access(null);
    }

    public function access($cursor)
    {
        $params = [
            'fields' => 'threadEvents',
            'count' => $this->limit,
        ];

        if ($cursor != null)
        {
            if (!is_string($cursor))
            {
                throw new InvalidArgumentException("$cursor must be a string.");
            }
       
            $params['maxEventIndex'] = $cursor;
        }

        $results = $this->get(
            'https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->threadId, 
            $params
        );

        $this->maxEventIndexCursor = $results->maxEventIndexCursor;

        $this->update($results->resultsCount, $results->threadEvents);
    }

    public function next()
    {
        $this->currentOffset++;
        if (($this->currentOffset % $this->limit) == 0)
        {
            $this->access($this->maxEventIndexCursor);
        }
    }

    public function current()
    {
        return new Message(
            $this->httpClient,
            $this->getFromOffset($this->currentOffset)->messageEventDetail
        );
    }
}