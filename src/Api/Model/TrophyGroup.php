<?php
namespace Tustin\PlayStation\Api\Model;

use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Trophies;
use Tustin\PlayStation\Traits\Model;
use Tustin\PlayStation\Contract\Fetchable;
use Tustin\PlayStation\Iterator\TrophyIterator;

class TrophyGroup
{
    use Model;

    private $trophyTitle;

    public function __construct(TrophyTitle $title, object $data)
    {
        $this->setCache($data);
        $this->trophyTitle = $title;
    }

    public function title() : TrophyTitle
    {
        return $this->trophyTitle;
    }
    
    /**
     * Gets all the trophies in the trophy group.
     *
     * @return Trophies
     */
    public function trophies() : Trophies
    {
        return new Trophies($this);
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
}