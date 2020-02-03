<?php

namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Model\Model;
use Tustin\PlayStation\Api\Model\Message;
use Tustin\PlayStation\Iterator\MembersIterator;
use Tustin\PlayStation\Iterator\MessagesIterator;
use Tustin\PlayStation\Api\Message\AbstractMessage;

class MessageThread extends Model
{
    private string $threadId;

    private array $members;

    public function __construct(Client $client, string $threadId, array $members = [])
    {
        parent::__construct($client);

        $this->threadId = $threadId;
        $this->members = $members;
    }

    public function members() : MembersIterator
    {
        return new MembersIterator(
            !empty($this->members) ? $this->members : $this->info()->threadMembers
        );
    }

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

    public function messages(int $count = 20) : MessagesIterator
    {
        return new MessagesIterator($this->httpClient, $this->threadId(), $count);
    }

    public function threadId() : string
    {
        return $this->threadId;
    }

    public function info(int $count = 1) : object
    {
        return $this->cache ??= $this->get('https://us-gmsg.np.community.playstation.net/groupMessaging/v1/threads/' . $this->threadId(), [
            'fields' => 'threadMembers,threadNameDetail,threadThumbnailDetail,threadProperty,latestTakedownEventDetail,newArrivalEventDetail,threadEvents',
            'count' => $count,
        ]);
    }
}