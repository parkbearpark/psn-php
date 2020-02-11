<?php
namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Enum\TrophyType;

class Trophy
{
    use Model;
    
    public function __construct(object $data)
    {
        $this->setCache($data);
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