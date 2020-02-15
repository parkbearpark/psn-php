<?php
namespace Tustin\PlayStation\Tests;

use BadMethodCallException;
use InvalidArgumentException;
use Tustin\PlayStation\Api\TrophyTitles;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Exception\NoTrophiesException;
use Tustin\PlayStation\Iterator\TrophyTitlesIterator;
use Tustin\PlayStation\Exception\MissingPlatformException;

class TrophyTest extends PlayStationApiTestCase
{
    public function testEnsureTrophyTitlesIsATrophyTitlesInstance()
    {
        $titles = self::$client->trophyTitles();

        $this->assertInstanceOf(TrophyTitles::class, $titles);
    }

    public function testEnsureTrophyTitlesIsIterable()
    {
        $titles = self::$client->trophyTitles();

        $this->assertTrue(is_iterable($titles));
    }

    public function testTryToGetTrophyTitlesWithoutGivingAPlatform()
    {
        $this->expectException(MissingPlatformException::class);
        
        $titles = self::$client->trophyTitles();

        $titles->first();
    }

    public function testClientShouldHaveNoTrophies()
    {
        $this->expectException(NoTrophiesException::class);

        $titles = self::$client->trophyTitles()->platforms(ConsoleType::ps4());

        $titles->first();
    }

    public function testInvalidConsoleType()
    {
        $this->expectException(BadMethodCallException::class);

        $titles = self::$client->trophyTitles()->platforms(ConsoleType::ps2());
    }

    public function testCanGetAnotherUsersPublicTrophyTitles()
    {
        $titles = self::$tustinUser->trophyTitles()->platforms(ConsoleType::ps4());

        $this->assertInstanceOf(TrophyTitle::class, $titles->first());
    }

    /**
     * @depends testCanGetAnotherUsersPublicTrophyTitles
     */
    public function testFilterByTrophyGroupsOnlyHasTitlesWithGroups()
    {
        // $titles = self::$tustinUser->trophyTitles()->platforms(ConsoleType::ps4());
    }
}