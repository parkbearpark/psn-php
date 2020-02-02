<?php
namespace Tustin\PlayStation\Api;

use Carbon\Carbon;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Users;
use Tustin\Haste\Exception\NotFoundException;
use Tustin\PlayStation\Api\Model\MessageThread;
use Tustin\PlayStation\Iterator\MessageThreadsIterator;

class MessageThreads extends Api
{
    /**
     * Returns all message threads.
     *
     * @param integer $limit
     * @param Carbon|null $since
     * @return MessageThreadsIterator
     */
    public function all(int $limit = 20, ?Carbon $since = null) : MessageThreadsIterator
    {
        // @TODO: Maybe we should be saving this iterator instead of creating a new one each call??
        // - Tustin 2/1/2020
        return new MessageThreadsIterator($this->httpClient, $limit, $since);
    }

    /**
     * Returns message threads that contain the specified $onlineId.
     *
     * @param string $onlineId
     * @return \Generator
     */
    public function with(string $onlineId) : \Generator
    {
        foreach ($this->all() as $thread)
        {
            if ($thread->members()->contains($username))
            {
                yield $thread;
            }
        }
    }

    /**
     * Finds a specific thread by it's id.
     *
     * @param string $threadId
     * @return MessageThread
     */
    public function find(string $threadId) : MessageThread
    {
        foreach ($this->all() as $thread)
        {
            if ($thread->threadId() === $threadId)
            {
                return $thread;
            }
        }

        throw new NotFoundException("No such thread with thread id $threadId found.");
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

    public function latest() : MessageThread
    {
        // Shouldn't need to rewind the iterator since it's creating a new iterator instance.
        // Might need to change this in the future if we stick with a static iterator.
        return $this->all()->current();
    }
}