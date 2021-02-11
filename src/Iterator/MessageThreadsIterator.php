<?php
namespace Tustin\PlayStation\Iterator;

use Tustin\PlayStation\Api\Model\MessageThread;
use Tustin\PlayStation\Api\MessageThreadsRepository;

class MessageThreadsIterator extends AbstractApiIterator
{
    /**
     * The message threads repository.
     *
     * @var MessageThreadsRepository
     */
    private $messageThreadsRepository;
    
    public function __construct(MessageThreadsRepository $messageThreadsRepository)
    {
        parent::__construct($messageThreadsRepository->httpClient);

        $this->messageThreadsRepository = $messageThreadsRepository;
        $this->limit = 20;

        $this->access(0);
    }

    public function access($cursor) : void
    {
        $results = $this->get('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/', [
            'fields' => 'threadMembers',
            'limit' => $this->limit,
            'offset' => $cursor,
            'sinceReceivedDate' => $this->messageThreadsRepository->getSinceDate()->toIso8601ZuluString()
        ]);

        $this->update($results->totalSize, $results->threads);
    }

    public function current()
    {
        return MessageThread::fromObject(
            $this->messageThreadsRepository,
            $this->getFromOffset($this->currentOffset)
        );
    }
}