<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use IteratorAggregate;
use InvalidArgumentException;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Enum\ConsoleType;
use Tustin\PlayStation\Enum\LanguageType;
use Tustin\PlayStation\Api\Model\TrophyTitle;
use Tustin\PlayStation\Exception\NoTrophiesException;
use Tustin\PlayStation\Iterator\TrophyTitlesIterator;
use Tustin\PlayStation\Interfaces\RepositoryInterface;
use Tustin\PlayStation\Exception\MissingPlatformException;
use Tustin\PlayStation\Iterator\Filter\TrophyTitle\TrophyTitleNameFilter;
use Tustin\PlayStation\Iterator\Filter\TrophyTitle\TrophyTitleHasGroupsFilter;

class TrophyTitlesRepository extends Api implements RepositoryInterface, IteratorAggregate
{
    protected $platforms = [];

    protected $language;

    protected $comparedUser = null;

    private $withName = '';

    private $hasTrophyGroups = null;

    /**
     * Sets the language you want to get the trophy titles in.
     * 
     * If this method isn't used, English will be used by default.
     *
     * @param LanguageType $language
     * @return TrophyTitlesRepository
     */
    public function setLanguage(LanguageType $language) : TrophyTitlesRepository
    {
        $this->language = $language;

        return $this;
    }

    /**
     * The user you want to get the trophy titles for.
     *
     * @param User $comparedUser
     * @return TrophyTitlesRepository
     */
    public function forUser(User $comparedUser) : TrophyTitlesRepository
    {
        $this->comparedUser = $comparedUser;
        
        return $this;
    }

    /**
     * Filters trophy titles only for the supplied platform(s).
     *
     * @param ConsoleType ...$platforms
     * @return TrophyTitlesRepository
     */
    public function platforms(ConsoleType ...$platforms) : TrophyTitlesRepository
    {
        $this->platforms = $platforms;

        return $this;
    }

    /**
     * Filters trophy titles that either have trophy groups or no trophy groups.
     *
     * @param boolean $value
     * @return TrophyTitlesRepository
     */
    public function hasTrophyGroups(bool $value = true) : TrophyTitlesRepository
    {
        $this->hasTrophyGroups = $value;

        return $this;
    }

    /**
     * Filters trophy titles to only get titles containing the supplied name.
     *
     * @param string $name
     * @return TrophyTitlesRepository
     */
    public function withName(string $name) : TrophyTitlesRepository
    {
        $this->withName = $name;
        
        return $this;
    }

    /**
     * Gets the iterator and applies any filters.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        if (empty($this->platforms))
        {
            throw new MissingPlatformException("TrophyTitles::platforms() must be called once with the specified platforms.");    
        }

        $iterator = new TrophyTitlesIterator($this);

        if ($this->withName)
        {
            $iterator = new TrophyTitleNameFilter($iterator, $this->withName);
        }

        if (!is_null($this->hasTrophyGroups))
        {
            $iterator = new TrophyTitleHasGroupsFilter($iterator, $this->hasTrophyGroups);
        }

        return $iterator;
    }

    /**
     * Gets the current platforms passed to this instance.
     *
     * @return array
     */
    public function getPlatforms() : array
    {
        return $this->platforms;
    }
    
    /**
     * Gets the current language passed to this instance.
     * 
     * If the language has not been set prior, this will return LanguageType::english().
     *
     * @return LanguageType
     */
    public function getLanguage() : LanguageType
    {
        return $this->language ?? LanguageType::english();
    }

    /**
     * Gets the current compared user (if exists) passed to this instance.
     * 
     * The compared user is used to get trophy titles for another user and is set via TrophyTitlesRepository::forUser.
     *
     * @return User|null
     */
    public function getComparedUser() : ?User
    {
        return $this->comparedUser;
    }

    /**
     * Gets the first trophy title in the collection.
     *
     * @return TrophyTitle
     */
    public function first() : TrophyTitle
    {
        try {
            return $this->getIterator()->current();
        }
        catch (InvalidArgumentException $e)
        {
            throw new NoTrophiesException("Client has no trophy titles.");
        }
    }
}