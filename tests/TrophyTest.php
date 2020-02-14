<?php
namespace Tustin\PlayStation\Tests;

use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Iterator\TrophyTitlesIterator;

class UserTest extends PlayStationApiTestCase
{
    public function testEnsureAllTrophyTitlesReturnsIterator()
    {
        $titles = self::$client->trophyTitles()->all();

        $this->assertInstanceOf(TrophyTitlesIterator::class, $titles);
    }

    public function testTryToGetTrophyTitlesForPs3Only()
    {
        $titles = self::$client->trophyTitles()->platform(ConsoleType::ps3());

        $this->assertInstanceOf(TrophyTitlesIterator::class, $titles);

        // $title = $titles->

    }

}