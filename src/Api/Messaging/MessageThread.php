<?php

namespace Tustin\PlayStation\Api\Messaging;

use Tustin\PlayStation\Client;
use Tustin\PlayStation\MessageType;

use Tustin\PlayStation\Resource\Image;
use Tustin\PlayStation\Resource\Audio;

use Tustin\PlayStation\Api\AbstractApi;
use Tustin\PlayStation\Api\User;

class MessageThread extends AbstractApi 
{
    const MESSAGE_THREAD_ENDPOINT    = 'https://us-gmsg.np.community.playstation.net/groupMessaging/v1/';

    private $messageThread;
    private $messageThreadId;

    public function __construct(Client $client, string $messageThreadId)
    {
        parent::__construct($client);

        $this->messageThreadId = $messageThreadId;
    }

    /**
     * Get the message thread info.
     *
     * @param int $count Amount of messages to return.
     * @param bool $force Force an update.
     * @return object
     */
    public function info(int $count = 1, bool $force = false) : \stdClass
    {
        if ($this->messageThread === null || $force) {
            $this->messageThread = $this->client->get(sprintf(self::MESSAGE_THREAD_ENDPOINT . 'threads/%s', $this->messageThreadId), [
                'fields' => 'threadMembers,threadNameDetail,threadThumbnailDetail,threadProperty,latestTakedownEventDetail,newArrivalEventDetail,threadEvents',
                'count' => $count,
            ]);
        }

        return $this->messageThread;
    }

    /**
     * Gets the message thread ID.
     *
     * @return string
     */
    public function messageThreadId() : string
    {
        return $this->messageThreadId;
    }

    /**
     * Get the amount of members in the message thread.
     *
     * @return int
     */
    public function memberCount() : int 
    {
        return count($this->info()->threadMembers);
    }

    /**
     * Get the name of the message thread.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->info()->threadNameDetail->threadName;
    }

    /**
     * Gets the message thread thumbnail URL.
     *
     * @return string|null
     */
    public function thumbnailUrl() : ?string
    {
        return $this->info()->threadThumbnailDetail->resourcePath;
    }

    /**
     * Gets the last time the message thread was modified.
     *
     * @return \DateTime
     */
    public function modifiedDate() : \DateTime
    {
        return new \DateTime($this->info()->threadModifiedDate);
    }

    /**
     * Leave the message thread.
     *
     * @return bool
     */
    public function leave() : bool
    {
        $this->client->delete(sprintf(self::MESSAGE_THREAD_ENDPOINT . 'threads/%s/users/me', $this->messageThreadId));

        return true;
    }

    /**
     * Get members in the message thread.
     *
     * @return array Array of \Tustin\PlayStation\Api\User.
     */
    public function members() : array
    {
        $members = [];

        if (!isset($this->info()->threadMembers) || $this->info()->threadMembers == 0) {
            return $members;
        }

        foreach ($this->info()->threadMembers as $member) {
            $members[] = new User($this->client, $member->onlineId);
        }

        return $members;
    }

    /**
     * Set the name of the message thread.
     *
     * @param string $name New name of the message thread.
     * @return bool
     */
    public function setName(string $name) : bool
    {
        $data = (object)[
            'threadNameDetail' => (object)[
                'threadName' => $name
            ]
        ];

        $this->client->putJson(sprintf(self::MESSAGE_THREAD_ENDPOINT . 'threads/%s/name', $this->messageThreadId), $data);

        return true;
    }

    /**
     * Favorite the message thread.
     *
     * @return bool
     */
    public function favorite() : bool
    {
        $data = (object)[
            'favoriteDetail' => (object)[
                'favoriteFlag' => true
            ]
        ];

        $this->client->putJson(sprintf(self::MESSAGE_THREAD_ENDPOINT . 'users/me/threads/%s/favorites', $this->messageThreadId), $data);
    
        return true;
    }

    /**
     * Unfavorite the message thread.
     *
     * @return bool
     */
    public function unfavorite() : bool
    {
        $data = (object)[
            'favoriteDetail' => (object)[
                'favoriteFlag' => false
            ]
        ];

        $this->client->putJson(sprintf(self::MESSAGE_THREAD_ENDPOINT . 'users/me/threads/%s/favorites', $this->messageThreadId), $data);
    
        return true;
    }

    /**
     * Send a text message to the message thread.
     *
     * @param string $message The message text to send.
     * @return \Tustin\PlayStation\Api\Message|null
     */
    public function sendMessage(string $message) : ?Message 
    {
        $data = (object)[
            'messageEventDetail' => (object)[
                'eventCategoryCode' => MessageType::Text, 
                'messageDetail' => (object)[
                    'body' => $message
                ]
            ]
        ];

        $parameters = [
            [
                'name' => 'messageEventDetail',
                'contents' => json_encode($data, JSON_PRETTY_PRINT),
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8'
                ]
            ]
        ];

        $response = $this->client->postMultiPart(sprintf(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/%s/messages', $this->messageThreadId), $parameters);

        $messageFields = $this->info(1, true);

        if (!isset($messageFields->threadEvents)) {
            return null;
        }

        $messageData = $messageFields->threadEvents[0];

        return new Message($this->client, $messageData->messageEventDetail, $this);
    }

    /**
     * Send an image message to the message thread.
     *
     * @param \Tustin\PlayStation\Resource\Image $image The image file.
     * @return \Tustin\PlayStation\Api\Message|null
     */
    public function sendImage(Image $image) : ?Message
    {
        if ($image->type() != 'image/png') {
            throw new \InvalidArgumentException("Image file type can only be PNG.");
        }
    
        $data = (object)[
            'messageEventDetail' => (object)[
                'eventCategoryCode' => MessageType::Image, 
                'messageDetail' => (object)[
                    'body' => ''
                ]
            ]
        ];

        $parameters = 
        [
            [
                'name' => 'messageEventDetail',
                'contents' => json_encode($data, JSON_PRETTY_PRINT),
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8'
                ]
            ],
            [
                'name' => 'imageData',
                'contents' => $image->data(),
                'headers' => [
                    'Content-Type' => 'image/png',
                    'Content-Transfer-Encoding' => 'binary',
                ]
            ]
        ];

        $response = $this->client->postMultiPart(sprintf(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/%s/messages', $this->messageThreadId), $parameters);

        $messageFields = $this->info(1, true);

        if (!isset($messageFields->threadEvents)) {
            return null;
        }

        $messageData = $messageFields->threadEvents[0];

        return new Message($this->client, $messageData->messageEventDetail, $this);
    }

    /**
     * Send an audio message to the message thread.
     *
     * @param \Tustin\PlayStation\Resource\Audio $audio The audio file.
     * @return \Tustin\PlayStation\Api\Message|null
     */
    public function sendAudio(Audio $audio) : ?Message
    {
        if ($audio->type() !== 'audio/3gpp') {
            throw new \InvalidArgumentException("Audio file type can only be audio/3gpp.");
        }

        $data = (object)[
            'messageEventDetail' => (object)[
                'eventCategoryCode' => MessageType::Audio, 
                'messageDetail' => (object)[
                    'body' => '',
                    'voiceDetail' => (object)[
                        'playbackTime' => $audioLengthSeconds
                    ]
                ]
            ]
        ];

        $parameters = 
        [
            [
                'name' => 'messageEventDetail',
                'contents' => json_encode($data, JSON_PRETTY_PRINT),
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8'
                ]
            ],
            [
                'name' => 'voiceData',
                'contents' => $audioContents,
                'headers' => [
                    'Content-Type' => 'audio/3gpp',
                    'Content-Transfer-Encoding' => 'binary',
                ]
            ]
        ];

        $response = $this->client->postMultiPart(sprintf(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/%s/messages', $this->messageThreadId), $parameters);

        $messageFields = $this->info(1, true);

        if (!isset($messageFields->threadEvents)) {
            return null;
        }

        $messageData = $messageFields->threadEvents[0];

        return new Message($this->client, $messageData->messageEventDetail, $this);
    }

    /**
     * Get all the messages in the message thread.
     *
     * @param int $count Amount of messages to get.
     * @return array Array of \Tustin\PlayStation\Api\Message.
     */
    public function messages(int $count = 200) : array
    {
        $messages = [];

        $messageFields = $this->info($count, true);

        foreach ($messageFields->threadEvents as $message) {
            $messages[] = new Message($this->client, $message->messageEventDetail, $this);
        }

        return $messages;
    }

    /**
     * Set the message thread thumbnail.
     *
     * @param \Tustin\PlayStation\Resource\Image $image The image file.
     * @return bool
     */
    public function setThumbnail(Image $image) : bool
    {
        if ($image->type() !== 'image/jpeg') {
            throw new \InvalidArgumentException("Image file type can only be JPEG.");
        }

        $parameters = [
            [
                'name' => 'threadThumbnail',
                'contents' => $image->data(),
                'headers' => [
                    'Content-Type' => 'image/jpeg',
                    'Content-Transfer-Encoding' => 'binary',
                ]
            ]
        ];

        $this->client->putMultiPart(sprintf(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/%s/thumbnail', $this->messageThreadId), $parameters);

        return true;
    }

    /**
     * Removes the message thread thumbnail.
     *
     * @return bool
     */
    public function removeThumbnail() : bool
    {
        $this->client->delete(sprintf(MessageThread::MESSAGE_THREAD_ENDPOINT . 'threads/%s/thumbnail', $this->messageThreadId));

        return true;
    }
}