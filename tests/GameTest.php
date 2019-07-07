<?php

namespace Tustin\PlayStation\Tests;

use Tustin\PlayStation\Api\Game;
use Tustin\PlayStation\Api\TrophyGroup;
use Tustin\PlayStation\Api\Trophy;

class GameTest extends PlayStationApiTestCase
{
    public function testGetLoggedInUsersPlayedGamesWhichShouldBeEmpty()
    {
        $games = self::$loggedInUser->games();

        $this->assertInternalType('array', $games);

        $this->assertEquals(count($games), 0);
    }

    public function testGetUsersPlayedGamesWithPrivacySettingsEnabled()
    {
        $this->expectException('\PlayStation\Exception\AccessDeniedException');

        self::$testUser->games();
    }

    public function testGetUsersPlayedGamesWithPrivacySettingsDisabled()
    {
        $games = self::$tustinUser->games(50);

        $this->assertInternalType('array', $games);

        $this->assertEquals(count($games), 50);

        // This will need to be changed in the future to ensure that this game
        // will always been returned within the first 50 games.
        $bo4Array = array_filter($games, function($game) {
            return $game->titleId() == 'CUSA11100_00';
        });

        $this->assertEquals(count($bo4Array), 1);

        $bo4 = $bo4Array[0];

        $this->assertInstanceOf('\PlayStation\Api\Game', $bo4);

        return $bo4;
    }

    /**
     * @depends testGetUsersPlayedGamesWithPrivacySettingsDisabled
     */
    public function testGameShouldHaveTrophes(Game $game)
    {
        $this->assertTrue($game->hasTrophies());
    }

    /**
     * @depends testGetUsersPlayedGamesWithPrivacySettingsDisabled
     */
    public function testGameIsBeingCompared(Game $game)
    {
        $this->assertTrue($game->isComparing());
    }

    /**
     * @depends testGetUsersPlayedGamesWithPrivacySettingsDisabled
     */
    public function testEnsureGameNameIsBlackOps4(Game $game)
    {
        $this->assertEquals($game->name(), 'Call of Duty®: Black Ops 4');
    }

    /**
     * @depends testGetUsersPlayedGamesWithPrivacySettingsDisabled
     */
    public function testPlatinumShouldNotBeEarned(Game $game)
    {
        $this->assertFalse($game->earnedPlatinum());
    }

    /**
     * @depends testGetUsersPlayedGamesWithPrivacySettingsDisabled
     */
    public function testCheckThatGameHasTrophyGroups(Game $game)
    {
        $groups = $game->trophyGroups();

        $this->assertInternalType('array', $groups);

        // There should be at least 4
        // Base game, Classified, Dead of the Night, Ancient Evil
        // This should only increase as time goes on and more DLC is released
        $this->assertGreaterThanOrEqual(count($groups), 4);

        $baseTrophyGroup = $groups[0];

        $this->assertInstanceOf('\PlayStation\Api\TrophyGroup', $baseTrophyGroup);

        return $baseTrophyGroup;
    }

    /**
     * @depends testCheckThatGameHasTrophyGroups
     */
    public function testEnsureThatTrophyGroupIsBaseTrophyGroup(TrophyGroup $group)
    {
        $this->assertEquals($group->id(), 'default');
    }

    /**
     * @depends testCheckThatGameHasTrophyGroups
     */
    public function testEnsureThatTrophyGroupNameIsCorrect(TrophyGroup $group)
    {
        $this->assertEquals($group->name(), 'Call of Duty®: Black Ops 4');
    }

    /**
     * @depends testCheckThatGameHasTrophyGroups
     */
    public function testEnsureThatTrophyGroupDetailIsCorrect(TrophyGroup $group)
    {
        $this->assertEquals($group->detail(), 'Call of Duty®: Black Ops 4');
    }

    /**
     * @depends testCheckThatGameHasTrophyGroups
     */
    public function testEnsureThatTrophyGroupHasCorrectAmountOfTrophies(TrophyGroup $group)
    {
        $this->assertEquals($group->trophyCount(), 53);
    }

    /**
     * @depends testCheckThatGameHasTrophyGroups
     */
    public function testGetAllTrophiesInGroupAndReturnPlatinumTrophy(TrophyGroup $group)
    {
        $trophies = $group->trophies();

        $this->assertInternalType('array', $trophies);

        // Should be the same as the above test.
        $this->assertGreaterThanOrEqual(count($trophies), 53);

        $platinumTrophy = $trophies[0];

        $this->assertInstanceOf('\PlayStation\Api\Trophy', $platinumTrophy);

        $this->assertEquals($platinumTrophy->type(), 'platinum');

        return $platinumTrophy;
    }

    /**
     * @depends testGetAllTrophiesInGroupAndReturnPlatinumTrophy
     */
    public function testEnsureThatTrophyIdMatchesPlatinumTrophyId(Trophy $trophy)
    {
        $this->assertEquals($trophy->id(), 0);
    }

    /**
     * @depends testGetAllTrophiesInGroupAndReturnPlatinumTrophy
     */
    public function testEnsurePlatinumTrophyIsNotHidden(Trophy $trophy)
    {
        $this->assertFalse($trophy->hidden());
    }

    /**
     * @depends testGetAllTrophiesInGroupAndReturnPlatinumTrophy
     */
    public function testEnsureTrophyNameMatchesPlatinumTrophyName(Trophy $trophy)
    {
        $this->assertEquals($trophy->name(), 'Platinum');
    }

    /**
     * @depends testGetAllTrophiesInGroupAndReturnPlatinumTrophy
     */
    public function testEnsureTrophyDetailMatchesPlatinumTrophyDetail(Trophy $trophy)
    {
        $this->assertEquals($trophy->detail(), 'Awarded when all other trophies have been unlocked');
    }
}