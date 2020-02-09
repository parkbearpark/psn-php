<?php
namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Enum\TrophyType;

class Trophy extends Model
{
    public function __construct(object $trophy)
    {
        // Since there is no endpoint to get an invidival trophy's detail, this model should just receive the raw object from the API. 
        $this->cache = $trophy;
    }

    /**
     * Gets the trophy name.
     *
     * @return string
     */
    public function name() : string
    {
        return $this->info()->trophyName;
    }

    /**
     * Gets the trophy details.
     *
     * @return string
     */
    public function detail() : string
    {
        return $this->info()->trophyDetail;
    }

    /**
     * Gets the trophy type. (platinum, bronze, silver, gold)
     *
     * @return TrophyType
     */
    public function type() : TrophyType
    {
        return new TrophyType($this->info()->trophyType);
    }

    /**
     * Get the trophy earned rate.
     *
     * @return float
     */
    public function earnedRate() : float
    {
        return $this->info()->trophyEarnedRate;
    }

    /**
     * Check if the trophy is hidden.
     *
     * @return boolean
     */
    public function hidden() : bool
    {
        return $this->info()->trophyHidden;
    }

    /**
     * Gets the raw trophy data from the PlayStation API.
     *
     * @return object|null
     */
    public function info() : ?object
    {
        return $this->cache;
    }
}