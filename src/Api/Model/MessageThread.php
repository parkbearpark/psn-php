<?php

namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Api\Model\Message;
use Tustin\PlayStation\Interfaces\Fetchable;
use Tustin\PlayStation\Api\MessagesRepository;
use Tustin\PlayStation\Api\Message\AbstractMessage;
use Tustin\PlayStation\Api\MessageThreadsRepository;
use Tustin\PlayStation\Api\MessageThreadMembersRepository;

class MessageThread extends Api implements Fetchable
{
    use Model;
    
    private string $threadId;

    private array $members;

    public function __construct(MessageThreadsRepository $messageThreadsRepository, string $threadId, array $members = [])
    {
        parent::__construct($messageThreadsRepository->httpClient);

        $this->threadId = $threadId;
        $this->members = $members;
    }

    public static function fromObject(MessageThreadsRepository $messageThreadsRepository, object $data)
    {
        $instance = new static($messageThreadsRepository, $data->threadId, $data->threadMembers);
        $instance->setCache($data);
        return $instance;
    }

    /**
     * Gets all the members in the message thread.
     *
     * @return MessageThreadMembersRepository
     */
    public function members() : MessageThreadMembersRepository
    {
        return new MessageThreadMembersRepository($this);
    }

    /**
     * Gets all the message thread members as an array.
     *
     * @return array
     */
    public function membersArray() : array
    {
        return $this->members ??= $this->pluck('threadMembers');
    }

    /**
     * Gets the member count in the message thread.
     *
     * @return integer
     */
    public function memberCount() : int
    {
        return count(
            $this->members()
        );
    }

    /**
     * Sends a message to the message thread.
     *
     * @param AbstractMessage $message
     * @return Message
     */
    public function sendMessage(AbstractMessage $message) : Message
    {
        $this->postMultiPart(
            'https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->id() . '/messages',
            $message->build()
        );

        return $this->messages()->first();
    }

    /**
     * Gets all messages in the message thread.
     *
     * @return MessagesRepository
     */
    public function messages() : MessagesRepository
    {
        return new MessagesRepository($this);
    }

    /**
     * Gets the message thread ID.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->threadId;
    }

    /**
     * Gets the thread info from the PlayStation API.
     *
     * @param integer $count
     * @return object
     */
    public function fetch(int $count = 1) : object
    {
        return $this->get('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->id(), [
            'fields' => implode(',', [
                'threadMembers',
                'threadNameDetail',
                'threadThumbnailDetail',
                'threadProperty',
                'latestTakedownEventDetail',
                'newArrivalEventDetail',
                'threadEvents'
            ]),
            'count' => $count,
        ]);
    }
}