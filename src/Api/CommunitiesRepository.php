<?php

namespace Tustin\PlayStation\Api;

use Iterator;
use IteratorAggregate;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Enum\LanguageType;
use Tustin\PlayStation\Api\Model\Community;
use Tustin\PlayStation\Iterator\CommunitiesIterator;
use Tustin\PlayStation\Interfaces\RepositoryInterface;

class CommunitiesRepository extends Api implements RepositoryInterface, IteratorAggregate
{
    /**
     * The languages that will be used when searching for communities.
     *
     * @var LanguageType
     */
    private $npLanguage;

    /**
     * The query to search for.
     *
     * @var string
     */
    private $query;

    /**
     * The user to get the communities of.
     *
     * @var User|null
     */
    private $user = null;

    /**
     * The fields to include in the community response objects.
     * 
     * @var string[]
     */
    private $includeFields = ['parties', 'gameSessions'];

    /**
     * Sets the language to use when searching for a community.
     * 
     * This won't return results in this language, but rather only find communities that use this language.
     * If not set, the community search will return communities that have no language set.
     *
     * @param LanguageType $language
     * @return CommunitiesRepository
     */
    public function setLanguage(LanguageType $language) : CommunitiesRepository
    {
        $this->npLanguage = $language;
        
        return $this;
    }

    /**
     * The query to search for.
     *
     * @param string $query
     * @return CommunitiesRepository
     */
    public function setQuery(string $query) : CommunitiesRepository
    {
        $this->query = $query;

        return $this;
    }

    /**
     * The fields to include in the search.
     *
     * @param string ...$fields
     * @return CommunitiesRepository
     */
    public function setIncludeFields(string ...$fields) : CommunitiesRepository
    {
        $this->includeFields = array_merge($fields, $this->includeFields);

        return $this;
    }

    /**
     * Sets the user who you want to get the communities for.
     *
     * @param User $user
     * @return CommunitiesRepository
     */
    public function forUser(User $user) : CommunitiesRepository
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the include fields.
     *
     * @return string[]
     */
    public function getIncludeFields() : array
    {
        return $this->includeFields;
    }

    /**
     * Gets the search query.
     *
     * @return string
     */
    public function getQuery() : string
    {
        return $this->query;
    }

    /**
     * Gets the current language type.
     *
     * @return LanguageType|null
     */
    public function getLanguage() : ?LanguageType
    {
        // @Redundant?? What's the default value of Enum?
        return $this->npLanguage ?? null;
    }

    /**
     * Gets the user who you want to get the communities for.
     * 
     * If not set, this CommunitiesRepository will return the client's communities.
     *
     * @return User|null
     */
    public function getUser() : ?User
    {
        return $this->user;
    }

    /**
     * Finds a specific community by it's community ID.
     *
     * @param string $communityId
     * @return Community
     */
    public function find(string $communityId) : Community
    {
        return new Community($this, $communityId);
    }

    /**
     * Gets the iterator to iterate over all the community search results.
     *
     * @return Iterator
     */
    public function getIterator() : Iterator
    {
        $iterator =  new CommunitiesIterator($this);

        return $iterator;
    }
}