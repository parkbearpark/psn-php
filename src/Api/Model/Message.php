<?php
namespace Tustin\PlayStation\Api\Model;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Enum\MessageType;

class Message extends Model
{
    private object $eventData;

    public function __construct(Client $client, object $eventData)
    {
        parent::__construct($client);

        $this->eventData = $eventData;
    }

    /**
     * Gets the type of message.
     * 
     * Returns MessageType::unknown on unmapped message types. If you receive this type, open a PR/issue.
     *
     * @return MessageType
     */
    public function type() : MessageType
    {
        try
        {
            return new MessageType($this->eventData->eventCategoryCode);
        }
        catch (\UnexpectedValueException $e)
        {
            return MessageType::unknown();
        }
    }

    /**
     * Gets the media URL if the message contains some piece of media (image, audio).
     *
     * @return string|null
     */
    public function mediaUrl() : ?string
    {
        // @NeedsTesting
        return $this->attachedMediaPath;
    }

    /**
     * Gets the message body.
     *
     * @return string
     */
    public function body() : string
    {
        return $this->eventData->messageDetail->body;
    }

    /**
     * Gets the event index ID for the message.
     * 
     * Used as a cursor for pagination.
     *
     * @return string
     */
    public function eventIndex() : string
    {
        return $this->eventData->eventIndex;
    }

    /**
     * Gets the date and time when the message was posted.
     *
     * @return Carbon
     */
    public function date() : Carbon
    {
        // @NeedsTesting
        return Carbon::parse($this->eventData->postDate)->setTimezone('UTC');
    }

    /**
     * Gets the message sender.
     *
     * @return User
     */
    public function sender() : User
    {
        return new User(
            $this->httpClient, 
            $this->eventData->sender->onlineId
        );
    }
}