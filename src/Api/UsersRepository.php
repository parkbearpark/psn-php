<?php

namespace Tustin\PlayStation\Api;

use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Iterator\UsersSearchIterator;
use Tustin\PlayStation\Interfaces\RepositoryInterface;

class UsersRepository extends Api implements RepositoryInterface
{
    /**
     * Searches for a user.
     *
     * @param string $query
     * @param array $searchFields
     * @return UsersSearchIterator
     */
    public function search(string $query, array $searchFields = ['onlineId']) : UsersSearchIterator
    {
        return new UsersSearchIterator($this, $query, $searchFields);
    }

    /**
     * Find a specific user's profile by their onlineId.
     *
     * @param string $onlineId
     * @return User
     */
    public function find(string $onlineId) : User
    {
        return new User($this, $onlineId);
    }

    /**
     * Get the logged in user's profile.
     *
     * @return User
     */
    public function me() : User
    {
        return new User($this, 'me');
    }
}