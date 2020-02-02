<?php

namespace Tustin\PlayStation\Api;

use Tustin\PlayStation\Client;

use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Game;

use Tustin\PlayStation\Api\Session;

use Tustin\PlayStation\SessionType;

use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Resource\Audio;

use Tustin\PlayStation\Resource\Image;

use Tustin\PlayStation\Api\Story\Story;
use Tustin\PlayStation\Api\Trophy\Trophy;
use Tustin\PlayStation\Api\Messaging\Message;
use Tustin\PlayStation\Api\Trophy\TrophyGroup;
use Tustin\PlayStation\Iterator\UsersIterator;
use Tustin\PlayStation\Api\Community\Community;
use Tustin\PlayStation\Api\Messaging\MessageThread;

class Users extends Api 
{
    /**
     * Search for a user.
     *
     * @param string $query
     * @param integer $limit
     * @param array $searchFields
     * @return UsersIterator
     */
    public function search(string $query, array $searchFields = ['onlineId'], int $limit = 50) : UsersIterator
    {
        return new UsersIterator($this->httpClient, $query, $searchFields, $limit);
    }

    /**
     * Find a specific user's profile by their onlineId.
     *
     * @param string $onlineId
     * @return User
     */
    public function find(string $onlineId) : User
    {
        return new User($this->httpClient, $onlineId);
    }

    /**
     * Get the logged in user's profile.
     *
     * @return User
     */
    public function me() : User
    {
        return new User($this->httpClient, 'me');
    }
}