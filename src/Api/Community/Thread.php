<?php

namespace Tustin\PlayStation\Api\Community;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\AbstractApi;
use Tustin\PlayStation\Api\User;

use Tustin\PlayStation\Resource\Image;

class Thread extends AbstractApi 
{
    private $thread;
    private $community;

    public function __construct(Client $client, object $thread, Community $community) 
    {
        parent::__construct($client);

        $this->thread = $thread;
        $this->community = $community;
    }

    public function info() : \stdClass
    {
        return $this->thread;
    }

    /**
     * Gets the id of the thread.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->info()->id;
    }

    /**
     * Gets the type of thread.
     * 
     * Typically this will be either `motd` or `default`.
     *
     * @return string
     */
    public function type() : string
    {
        return $this->info()->type;
    }

    /**
     * Gets the name of the thread.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->info()->name;
    }

    /**
     * Gets all the messages in the thread.
     *
     * @param integer $limit
     * @return array Array of \Tustin\PlayStation\Api\Community\Message
     */
    public function messages(int $limit = 100) : array
    {
        if ($limit > 100) {
            throw new \InvalidArgumentException('Limit can only have a maximum value of 100.');
        }

        $returnMessages = [];

        $messages = $this->get(sprintf(self::COMMUNITY_ENDPOINT . 'communities/%s/threads/%s', $this->community()->id(), $this->id()), [
            'limit' => $limit
        ]);

        if ($messages->size === 0) return $returnMessages;

        foreach ($messages->messages as $message) {
            $returnMessages[] = new Message($this->client, $message, $this);
        }

        return $returnMessages;
    }

    /**
     * Sends a message to the thread.
     * 
     * A message can be uploaded with an image.
     * 
     * TODO: Needs testing.
     * - Tustin 10/10/2019
     *
     * @param string $message The message contents.
     * @param object $images The images to upload.
     * @return \Tustin\PlayStation\Api\Community\Message
     */
    public function sendMessage(string $message, Image $image = null) : ?Message
    {
        if ($image !== null) {
            if ($image->type() !== 'image/jpeg') throw new \InvalidArgumentException("Image file type can only be JPEG.");

            // First step, we need to upload a blank message to get an id.
            $response = $this->postJson(sprintf(self::COMMUNITY_ENDPOINT . 'communities/%s/threads/%s/messages', $this->community()->id(), $this->id()), [
                'message' => '',
                'images' => []
            ]);
            
            if (!isset($response->id)) {
                return null;
            }

            // Now we upload the image to the CDN and get the satchel URL.
            $imageUrl = $this->community->uploadImage('communityWallImage', $image);
            

            // Finally, 'create' a message again but pass the blank message's id to effectively edit it with the full info.
            // TODO: Try attaching multiple photos here? The app doesn't seem to allow it but images being an array seems to indicate you can.
            $response = $this->postJson(sprintf(self::COMMUNITY_ENDPOINT . 'communities/%s/threads/%s/messages', $this->community()->id(), $this->id()), [
                'message' => $message,
                'images' => [
                    $imageUrl
                ],
                'id' => $response->id
            ]);
        } else {
            $response = $this->postJson(sprintf(self::COMMUNITY_ENDPOINT . 'communities/%s/threads/%s/messages', $this->community()->id(), $this->id()), [
                'message' => $message,
                'images' => []
            ]);
        }

        return new Message($this->client, $response, $this);
    }

    /**
     * Get the community that this thread is apart of.
     *
     * @return \Tustin\PlayStation\Api\Community\Community
     */
    public function community() : Community
    {
        return $this->community;
    }
}