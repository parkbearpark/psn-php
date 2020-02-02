<?php
namespace Tustin\PlayStation\Iterator;

class MembersIterator implements \IteratorAggregate, \Countable
{
    private array $members = [];

    public function __construct(array $members = [])
    {
        $this->members = $members;
    }

    public function contains(string $member)
    {
        foreach ($this->members as $value)
        {
            return $value->onlineId === $member;
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    public function count()
    {
        return count($this->members);
    }
}