<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use IteratorAggregate;
use Tustin\PlayStation\Api\Model\Message;
use Tustin\PlayStation\Api\Model\MessageThread;
use Tustin\PlayStation\Iterator\MessagesIterator;
use Tustin\PlayStation\Api\Message\AbstractMessage;

class MessagesRepository extends Api implements IteratorAggregate
{
    /**
     * The message thread for these messages.
     *
     * @var MessageThread
     */
    private $thread;
    
    public function __construct(MessageThread $thread)
    {
        parent::__construct($thread->httpClient);

        $this->thread = $thread;
    }

    /**
     * Gets the iterator and applies any filters.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $iterator = new MessagesIterator($this->thread);

        return $iterator;
    }

    /**
     * Gets the first message in the message thread.
     *
     * @return Message
     */
    public function first() : Message
    {
        return $this->getIterator()->current();
    }

    /**
     * Creates and sends a new message in the message thread.
     *
     * @param AbstractMessage $message
     * @return Message
     */
    public function create(AbstractMessage $message) : Message
    {
        return $this->thread->sendMessage($message);
    }
}