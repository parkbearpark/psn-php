<?php

namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Api\Model\Message;
use Tustin\PlayStation\Contract\Fetchable;
use Tustin\PlayStation\Iterator\MembersIterator;
use Tustin\PlayStation\Iterator\MessagesIterator;
use Tustin\PlayStation\Api\Message\AbstractMessage;

class MessageThread extends Api implements Fetchable
{
    use Model;
    
    private string $threadId;

    private array $members;

    public function __construct(Client $client, string $threadId, array $members = [])
    {
        parent::__construct($client);

        $this->threadId = $threadId;
        $this->members = $members;
    }

    /**
     * Gets all the members in the message thread.
     *
     * @return MembersIterator
     */
    public function members() : MembersIterator
    {
        return new MembersIterator($this->httpClient,
            !empty($this->members) ? $this->members : $this->pluck('threadMembers')
        );
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
        $response = $this->postMultiPart(
            'https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->threadId() . '/messages',
            $message->build()
        );

        return $this->messages(1)->current();
    }

    /**
     * Gets all messages in the message thread.
     *
     * @param integer $count
     * @return MessagesIterator
     */
    public function messages(int $count = 20) : MessagesIterator
    {
        return new MessagesIterator($this->httpClient, $this->threadId(), $count);
    }

    /**
     * Gets the message thread ID.
     *
     * @return string
     */
    public function threadId() : string
    {
        return $this->threadId;
    }

    /**
     * Gets the thread info from the PlayStation API.
     *
     * @param integer $count
     * @return ?object
     */
    public function fetch(int $count = 1) : object
    {
        return $this->get('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->threadId(), [
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