<?php
namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Api\Enum\TrophyType;

class Trophy extends Model
{
    public function __construct(object $trophy)
    {
        $this->cache = $trophy;
    }

    public function name() : string
    {
        return $this->info()->trophyName;
    }

    public function type() : TrophyType
    {
        return new TrophyType($this->info()->trophyType);
    }

    public function hidden() : bool
    {
        return $this->info()->trophyHidden;
    }

    public function info() : ?object
    {
        return $this->cache;
    }
}