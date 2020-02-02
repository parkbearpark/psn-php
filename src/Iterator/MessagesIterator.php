<?php
namespace Tustin\PlayStation\Iterator;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Api\Model\Message;
use Tustin\PlayStation\Api\Model\MessageThread;

class MessagesIterator extends ApiIterator
{
    protected string $threadId;

    protected int $limit;

    protected string $maxEventIndexCursor;
        
    public function __construct(Client $client, string $threadId, int $limit = 20)
    {
        if (empty($threadId))
        {
            throw new \InvalidArgumentException('$threadId must not be empty.');
        }

        if ($limit <= 0)
        {
            throw new \InvalidArgumentException('$limit must be greater than zero.');
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
                throw new \InvalidArgumentException("$cursor must be a string.");
            }
       
            $params['maxEventIndex'] = $cursor;
        }

        $results = $this->get('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->threadId, $params);

        $this->setTotalResults($results->resultsCount);

        $this->maxEventIndexCursor = $results->maxEventIndexCursor;

        $this->cache = $results->threadEvents;
    }

    public function next()
    {
        $this->currentIndexer++;
        if (($this->currentIndexer % $this->limit) == 0)
        {
            $this->access($this->maxEventIndexCursor);
            $this->currentIndexer = 0;
        }
    }

    public function current()
    {
        return new Message(
            $this->httpClient,
            $this->cache[$this->currentIndexer]->messageEventDetail,
        );
    }
}