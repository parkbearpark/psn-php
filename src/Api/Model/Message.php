<?php
namespace Tustin\PlayStation\Api\Model;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Enum\MessageType;

class Message extends Api
{
    use Model;
    
    public function __construct(Client $client, object $eventData)
    {
        parent::__construct($client);

        $this->setCache($eventData);
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
            return new MessageType($this->pluck('eventCategoryCode'));
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
        return $this->pluck('attachedMediaPath');
    }

    /**
     * Gets the message body.
     *
     * @return string
     */
    public function body() : string
    {
        return $this->pluck('messageDetail.body');
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
        return $this->pluck('eventIndex');
    }

    /**
     * Gets the date and time when the message was posted.
     *
     * @return Carbon
     */
    public function date() : Carbon
    {
        // @NeedsTesting
        return Carbon::parse($this->pluck('postDate'))->setTimezone('UTC');
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
            $this->pluck('sender.onlineId')
        );
    }
}