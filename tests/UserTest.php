<?php

namespace Tustin\PlayStation\Tests;

class UserTest extends PlayStationApiTestCase
{
    public function testInvalidOnlineId()
    {
        $user = self::$client->user('something that should $$ never happen!!!');

        $this->expectException('\Tustin\PlayStation\Exception\NotFoundException');

        $user->games();
    }

    public function testValidOnlineId()
    {
        $this->assertEquals(self::$testUser->onlineId(), 'test');
    }

    public function testAreTestAndIFriends()
    {
        $this->assertFalse(self::$testUser->friend());
    }

    public function testAreTestAndICloseFriends()
    {
        $this->assertFalse(self::$testUser->closeFriend());
    }

    public function testAmIFollowingTest()
    {
        $this->assertFalse(self::$testUser->following());
    }

    public function testAmIVerified()
    {
        $this->assertFalse(self::$loggedInUser->verified());
    }

    public function testGetMyAboutMe()
    {
        $this->assertEquals(self::$loggedInUser->aboutMe(), 'Hello psn-php!');
    }

    public function testIsMyAvatarTheDefaultAvatar()
    {
        $this->assertStringContainsString('Defaultavatar', self::$loggedInUser->avatarUrl());
    }

    public function testGetMyFriends()
    {
        $friends = self::$loggedInUser->friends();

        $this->assertIsArray($friends);

        // This account is friends with my main account.
        $this->assertEquals(count($friends), 1);
    }

    public function testTryToGetUsersFriendsWithPrivacySettingsEnabled()
    {
        $this->expectException('\Tustin\PlayStation\Exception\AccessDeniedException');

        self::$testUser->friends();
    }
    
    public function testGetUsersFriendsWithPrivacySettingsDisabled()
    {
        $friends = self::$tustinUser->friends();

        $this->assertIsArray($friends);
        $this->assertEquals(count($friends), 36);

        $friend = $friends[0];

        $this->assertInstanceOf('\Tustin\PlayStation\Api\User', $friend);
    }

    public function testGetMyGames()
    {
        $games = self::$loggedInUser->games();

        $this->assertIsArray($games);

        // This user shouldn't have any games played.
        $this->assertEquals(count($games), 0);
    }

    public function testGetUsersGamesWithPrivacySettingsEnabled()
    {
        $this->expectException('\Tustin\PlayStation\Exception\AccessDeniedException');

        self::$testUser->games();
    }

    public function testGetUsersGamesWithPrivacySettingsDisabled()
    {
        $games = self::$tustinUser->games();

        $this->assertIsArray($games);
        $this->assertEquals(count($games), 100);

        $game = $games[0];

        $this->assertInstanceOf('\Tustin\PlayStation\Api\Game', $game);
    }

    // public function testGetCommunitiesUserIsInWithPrivacySettingsEnabled()
    // {
    //     $this->expectException('\Tustin\PlayStation\Exception\AccessDeniedException');

    //     self::$testUser->communities();
    // }

    public function testTryToGetCommunitiesUserIsExpectNone()
    {
        $communities = self::$loggedInUser->communities();

        $this->assertIsArray($communities);

        $this->assertEmpty($communities);
    }

    public function testGetCommunitiesUserIsInExpectMultiple()
    {
        $communities = self::$tustinUser->communities();

        $this->assertIsArray($communities);

        $this->assertNotEmpty($communities);

        $community = $communities[0];

        $this->assertInstanceOf('\Tustin\PlayStation\Api\Community\Community', $community);
    }
}