<?php

namespace Tustin\PlayStation\Api\Model;

use GuzzleHttp\Client;
use Tustin\PlayStation\Api\Model\Model;


class User extends Model
{
    private string $onlineIdParameter;

    public function __construct(Client $client, string $onlineId)
    {
        parent::__construct($client);

        $this->onlineIdParameter = $onlineId;
    }

    public function test() : string
    {
        return $this->onlineIdParameter;
    }

    public function profile() : object
    {
        return $this->cache ??= $this->get('https://us-prof.np.community.playstation.net/userProfile/v1/users/' . $this->onlineIdParameter . '/profile2', [
            'fields' => implode(',', [
                'aboutMe',
                'accountId',
                'avatarUrls',
                'blocking',
                'followerCount',
                'following',
                'friendRelation',
                'isOfficiallyVerified',
                'languagesUsed',
                'mutualFriendsCount',
                'onlineId',
                'plus',
                'requestMessageFlag',
                'trophySummary(@default,progress,earnedTrophies)'
            ]),
        ]);
    }
}