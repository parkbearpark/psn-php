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
    public function search(string $query, int $limit = 50) : UsersIterator
    {
        return new UsersIterator($this->httpClient, $query, $limit);
    }

    public function find(string $onlineId)
    {
        return new User($this->httpClient, $onlineId);
    }

    public function me()
    {
        return new User($this->httpClient, 'me');
    }
}