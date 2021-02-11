<?php

namespace Tustin\PlayStation\Tests;

use Tustin\PlayStation\Iterator\FriendsIterator;
use Tustin\Haste\Exception\NotFoundHttpException;
use Tustin\Haste\Exception\AccessDeniedHttpException;

class UserTest extends PlayStationApiTestCase
{
    public function testTryToFindUserByInvalidOnlineId()
    {
        $this->expectException(NotFoundHttpException::class);

        $user = self::$client->users()->find('something that should $$ never happen!!!');

        $user->onlineId();
    }

    public function testIsTestUserOnlineIdValid()
    {
        $this->assertEquals(self::$testUser->onlineId(), 'test');
    }

    public function testIsTheLoggedInUserFollowingTestUser()
    {
        $this->assertFalse(self::$testUser->isFollowing());
    }

    public function testIsLoggedInUserVerified()
    {
        $this->assertFalse(self::$loggedInUser->isVerified());
    }

    public function testIsLoggedInUserBlockingTustinUser()
    {
        $this->assertFalse(self::$tustinUser->isBlocking());
    }

    public function testEnsureTustinFollowerCountIsGreaterThanZero()
    {
        $this->assertGreaterThan(0, self::$tustinUser->followerCount());
    }

    public function testEnsureLanguagesIsAnArray()
    {
        $this->assertIsArray(self::$tustinUser->languages());
    }

    public function testEnsureLoggedInUserHasNegativeOneMutualFriends()
    {
        $this->assertEquals(-1, self::$loggedInUser->mutualFriendCount());
    }

    public function testEnsureLoggedInUserHasNoMutualFriendsWithTestUser()
    {
        $this->assertFalse(self::$testUser->hasMutualFriends());
    }

    public function testEnsureLoggedInUserIsNotCloseFriendsWithTestUser()
    {
        $this->assertFalse(self::$testUser->isCloseFriend());
    }

    public function testEnsureLoggedInUserHasNotFriendRequestedTestUser()
    {
        $this->assertFalse(self::$testUser->hasFriendRequested());
    }

    public function testEnsureLoggedInUserIsNotOnline()
    {
        $this->assertFalse(self::$loggedInUser->isOnline());
    }

    public function testEnsureLoggedInUserDoesNotHavePlus()
    {
        $this->assertFalse(self::$loggedInUser->hasPlus());
    }

    public function testValidateLoggedInUserAboutMe()
    {
        $this->assertEquals(self::$loggedInUser->aboutMe(), 'Hello psn-php!');
    }

    public function testEnsureLoggedInUserHasDefaultAvatar()
    {
        $this->assertStringContainsString('default', self::$loggedInUser->avatarUrl());
    }
    
    public function testEnsureTustinAccountHasAnAvatar()
    {
        // This might change
        $this->assertStringContainsString('psn-rsc', self::$tustinUser->avatarUrl());
    }

    public function testEnsureLoggedInUserHasOneFriend()
    {
        $friends = self::$loggedInUser->friends();

        $this->assertInstanceOf(FriendsIterator::class, $friends);

        // This account is friends with my main account.
        $this->assertEquals($friends->getTotalResults(), 1);
    }

    public function testTryToGetUsersFriendsWithPrivacySettingsEnabled()
    {
        $this->expectException(AccessDeniedHttpException::class);

        self::$testUser->friends();
    }
    
    public function testGetUsersFriendsWithPrivacySettingsDisabled()
    {
        $friends = self::$tustinUser->friends();

        $this->assertInstanceOf(FriendsIterator::class, $friends);
    }
}