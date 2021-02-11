<?php
namespace Tustin\PlayStation\Api;

use Iterator;
use Tustin\PlayStation\Api\Api;
use Tustin\PlayStation\Api\Model\User;
use Tustin\PlayStation\Iterator\FriendsIterator;
use Tustin\PlayStation\Interfaces\RepositoryInterface;

class FriendsRepository extends Api implements RepositoryInterface
{
    /**
     * The user to get friends of.
     *
     * @var User
     */
    private $user;

    /**
     * What field to sort by.
     *
     * @var string
     */
    private $sortBy;

    public function __construct(User $user)
    {
        parent::__construct($user->httpClient);
    }

    public function sortBy(string $field) : FriendsRepository
    {
        $this->sortBy = $field;
        
        return $this;
    }

    /**
     * Gets the sort by field.
     * 
     * If not set prior, will return onlineStatus.
     *
     * @return string
     */
    public function getSortBy() : string
    {
        return $this->sortBy ??= 'onlineStatus';
    }

    /**
     * Gets the user to get the friends of.
     *
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * Gets the iterator and applies any filters.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $iterator = new FriendsIterator($this);

        return $iterator;
    }
}