<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use Carbon\Carbon;
use IteratorAggregate;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Users;
use Tustin\PlayStation\Api\Model\MessageThread;
use Tustin\PlayStation\Iterator\MessageThreadsIterator;
use Tustin\PlayStation\Iterator\Filter\ThreadMembersFilter;

class MessageThreads extends Api implements IteratorAggregate
{
    private $with = [];
    private $only = false;
    private ?Carbon $since = null;

    /**
     * Filters threads that contains the user(s).
     * 
     * Chain this with MessageThreads::only to ensure you only get threads with these exact users.
     *
     * @param string ...$onlineIds
     * @return MessageThreads
     */
    public function with(string ...$onlineIds) : MessageThreads
    {
        $this->with = array_merge($this->with, $onlineIds);

        return $this;
    }

    /**
     * Should be used with the MessageThreads::with method.
     * 
     * Will return threads that contain ONLY the users passed to MessageThreads::with.
     *
     * @return MessageThreads
     */
    public function only() : MessageThreads
    {
        $this->only = true;

        return $this;
    }

    /**
     * Returns message threads that have only been active since the given date.
     *
     * @param Carbon $date
     * @return MessageThreads
     */
    public function since(Carbon $date) : MessageThreads
    {
        $this->since = $date;

        return $this;
    }

    /**
     * Gets the iterator and applies any filters.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $iterator = new MessageThreadsIterator($this->httpClient, $this->since);

        if ($this->with)
        {
            $iterator = new ThreadMembersFilter($iterator, $this->with, $this->only);
        }

        return $iterator;
    }

    public function first() : MessageThread
    {
        return $this->getIterator()->current();
    }

    /**
     * Creates a new message thread.
     * 
     * Will return an existing message thread if a thread already exists containing the same users you pass to this method.
     *
     * @param string ...$onlineIds
     * @return MessageThread
     */
    public function create(string ...$onlineIds) : MessageThread
    {
        // We need our onlineId when creating a new group.
        $clientOnlineId = (new Users($this->httpClient))->me()->onlineId();

        $membersToAdd = [];

        $membersToAdd[] = ['onlineId' => $clientOnlineId];

        foreach ($onlineIds as $onlineId)
        {
            $membersToAdd[] = ['onlineId' => $onlineId];
        }

        $response = $this->postMultiPart('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/', [
            [
                'name' => 'threadDetail',
                'contents' => json_encode([
                    'threadDetail' => [
                        'threadMembers' => $membersToAdd
                    ]
                ], JSON_PRETTY_PRINT),
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8'
                ]
            ]
        ]);

        return new MessageThread($this->httpClient, $response->threadId);
    }
}