<?php
namespace Tustin\PlayStation\Api;

use Carbon\Carbon;
use Tustin\PlayStation\Api\Api;
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
     * Returns message threads that contain the specificed onlineId.
     *
     * @param string $username
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

    public function latest() : MessageThread
    {
        // Shouldn't need to rewind the iterator since it's creating a new iterator instance.
        // Might need to change this in the future if we stick with a static iterator.
        return $this->all()->current();
    }
}