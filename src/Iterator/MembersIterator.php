<?php
namespace Tustin\PlayStation\Iterator;

class MembersIterator implements \IteratorAggregate
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
}