<?php
namespace Tustin\PlayStation\Iterator;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Api\Model\MessageThread;

class MessageThreadsIterator extends ApiIterator
{
    protected Carbon $since;
    
    public function __construct(Client $client, int $limit = 20, ?Carbon $since = null)
    {
        if ($limit <= 0)
        {
            throw new \InvalidArgumentException('$limit must be greater than zero.');
        }
        
        parent::__construct($client);
        $this->since = $since ?? Carbon::createFromTimestamp(0);
        $this->limit = $limit;
        $this->access(0);
    }

    public function access($cursor)
    {
        $results = $this->get('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/', [
            'fields' => 'threadMembers',
            'limit' => $this->limit,
            'offset' => $cursor,
            'sinceReceivedDate' => $this->since->toIso8601ZuluString()
        ]);

        $this->update($results->totalSize, $results->threads);
    }

    public function current()
    {
        $it = $this->getFromOffset($this->currentOffset);
        
        return new MessageThread(
            $this->httpClient,
            $it->threadId,
            $it->threadMembers
        );
    }
}