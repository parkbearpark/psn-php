<?php

namespace PlayStation\Tests;

use PlayStation\Api\Game;

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
        $this->assertTrue($game->comparing());
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

        $this->assertInternalType('array', $games);

        // There should be at least 4
        // Base game, Classified, Dead of the Night, Ancient Evil
        // This should only increase as time goes on and more DLC is released
        $this->assertGreaterThanOrEqual(count($groups), 4);

        $baseTrophyGroup = $groups[0];

        $this->assertInstanceOf('\PlayStation\Api\TrophyGroup', $baseTrophyGroup);

        return $trophyGroup;
    }

    /**
     * @depends testCheckThatGameHasTrophyGroups
     */
    public function testEnsureThatTrophyGroupIsBaseTrophyGroup(TrophyGroup $group)
    {
        $this->assertEqual($group->id(), 0);
    }

    /**
     * @depends testCheckThatGameHasTrophyGroups
     */
    public function testEnsureThatTrophyGroupNameIsCorrect(TrophyGroup $group)
    {
        
    }
}