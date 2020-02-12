<?php
namespace Tustin\PlayStation\Iterator;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Iterator\AbstractInternalIterator;

class MembersIterator extends AbstractInternalIterator
{
    public function __construct(Client $client, array $members = [])
    {
        $this->create(function ($member) use ($client) {
            return new User($client, $member->onlineId, true);
        }, $members);
    }

    public function contains(string $onlineId) : bool
    {
        foreach ($this as $member)
        {
            return strcasecmp($member->onlineId(), $onlineId) === 0;
        }
    }
}