<?php

namespace Tustin\PlayStation\Api;

use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Iterator\UsersSearchIterator;

class Users extends Api 
{
    /**
     * Searches for a user.
     *
     * @param string $query
     * @param array $searchFields
     * @param integer $limit
     * @return UsersSearchIterator
     */
    public function search(string $query, array $searchFields = ['onlineId'], int $limit = 50) : UsersSearchIterator
    {
        return new UsersSearchIterator($this->httpClient, $query, $searchFields, $limit);
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