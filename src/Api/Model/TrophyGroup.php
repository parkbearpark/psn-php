<?php
namespace Tustin\PlayStation\Api\Model;

use InvalidArgumentException;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Enum\TrophyType;
use Tustin\PlayStation\Api\TrophiesRepository;

class TrophyGroup
{
    use Model;

    private $trophyTitle;

    public function __construct(TrophyTitle $trophyTitle, object $data)
    {
        $this->setCache($data);
        $this->trophyTitle = $trophyTitle;
    }

    public static function fromObject(TrophyTitle $trophyTitle, object $data) : TrophyGroup
    {
        return new static($trophyTitle, $data);
    }

    public function title() : TrophyTitle
    {
        return $this->trophyTitle;
    }
    
    /**
     * Gets all the trophies in the trophy group.
     *
     * @return TrophiesRepository
     */
    public function trophies() : TrophiesRepository
    {
        return new TrophiesRepository($this);
    }

    /**
     * Gets the trophy group name.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->pluck('trophyGroupName');
    }

    /**
     * Gets the trophy group detail.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->pluck('trophyGroupDetail');
    }

    /**
     * Gets the trophy group ID.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->pluck('trophyGroupId');
    }

    /**
     * Gets the trophy group icon URL.
     *
     * @return string
     */
    public function iconUrl() : string
    {
        return $this->pluck('trophyGroupIconUrl');
    }

    public function definedTrophies() : array
    {
        return $this->pluck('definedTrophies');
    }

    public function bronzeTrophyCount() : int
    {
        return $this->pluck('definedTrophies.bronze');
    }

    public function silverTrophyCount() : int
    {
        return $this->pluck('definedTrophies.silver');
    }

    public function goldTrophyCount() : int
    {
        return $this->pluck('definedTrophies.gold');
    }

    public function hasPlatinum() : bool
    {
        return $this->pluck('definedTrophies.platinum') == 1;
    }

    public function trophyCount(TrophyType $trophyType) : int
    {
        switch ($trophyType)
        {
            case TrophyType::bronze():
            return $this->bronzeTrophyCount();
            case TrophyType::silver():
            return $this->silverTrophyCount();
            case TrophyType::gold():
            return $this->goldTrophyCount();
            case TrophyType::platinum():
            return (int)$this->hasPlatinum();
            default:
            throw new InvalidArgumentException("Trophy type [$trophyType] does not contain a count method.");
        }
    }

    public function totalTrophyCount() : int
    {
        $count = $this->bronzeTrophyCount() + $this->silverTrophyCount() + $this->goldTrophyCount();

        return $this->hasPlatinum() ? ++$count : $count;
    }
}