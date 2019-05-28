<?php

namespace PlayStation\Tests;

use PlayStation\Tests\PlayStationApiTestCase;

class UserTest extends PlayStationApiTestCase
{
    public function testInvalidOnlineId()
    {
        $user = self::$client->user('something that should $$ never happen!!!');

        $this->expectException('\PlayStation\Exception\NotFoundException');

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
        $this->assertContains('Defaultavatar', self::$loggedInUser->avatarUrl());
    }

    public function testGetMyFriends()
    {
        $friends = self::$loggedInUser->friends();

        $this->assertInternalType('array', $friends);

        // This account is friends with my main account.
        $this->assertEquals(count($friends), 1);
    }

    public function testTryToGetUsersFriendsWithPrivacySettingsEnabled()
    {
        $this->expectException('\PlayStation\Exception\AccessDeniedException');

        self::$testUser->friends();
    }
    
    public function testGetUsersFriendsWithPrivacySettingsDisabled()
    {
        $friends = self::$tustinUser->friends();

        $this->assertInternalType('array', $friends);
        $this->assertEquals(count($friends), 36);

        $friend = $friends[0];

        $this->assertInstanceOf('\PlayStation\Api\User', $friend);
    }

    public function testGetMyGames()
    {
        $games = self::$loggedInUser->games();

        $this->assertInternalType('array', $games);

        // This user shouldn't have any games played.
        $this->assertEquals(count($games), 0);
    }

    public function testGetUsersGamesWithPrivacySettingsEnabled()
    {
        $this->expectException('\PlayStation\Exception\AccessDeniedException');

        self::$testUser->games();
    }

    public function testGetUsersGamesWithPrivacySettingsDisabled()
    {
        $games = self::$tustinUser->games();

        $this->assertInternalType('array', $games);
        $this->assertEquals(count($games), 100);

        $game = $games[0];

        $this->assertInstanceOf('\PlayStation\Api\Game', $game);
    }
}