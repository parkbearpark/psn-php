<?php
namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Enum\TrophyType;

class Trophy
{
    use Model;

    /**
     * The trophy group this trophy is in.
     *
     * @var TrophyGroup
     */
    private $trophyGroup;
    
    public function __construct(TrophyGroup $trophyGroup, object $data)
    {
        $this->setCache($data);
        
        $this->trophyGroup = $trophyGroup;
    }

    public static function fromObject(TrophyGroup $trophyGroup, object $data) : Trophy
    {
        return new static($trophyGroup, $data);
    }
    /**
     * Gets the trophy name.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->pluck('trophyName');
    }

    /**
     * Gets the trophy details.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->pluck('trophyDetail');
    }

    /**
     * Gets the trophy type. (platinum, bronze, silver, gold)
     *
     * @return TrophyType
     */
    public function type() : TrophyType
    {
        return new TrophyType($this->pluck('trophyType'));
    }

    /**
     * Get the trophy earned rate.
     *
     * @return float
     */
    public function earnedRate() : float
    {
        return $this->pluck('trophyEarnedRate');
    }

    /**
     * Check if the trophy is hidden.
     *
     * @return boolean
     */
    public function hidden() : bool
    {
        return $this->pluck('trophyHidden');
    }
}