<?php
namespace Tustin\PlayStation\Iterator\Filter\TrophyGroup;

use Iterator;
use FilterIterator;

class NameFilter extends FilterIterator
{
    private string $name;
   
    public function __construct(Iterator $iterator, string $name)
    {
        parent::__construct($iterator);
        $this->name = $name;
    }
   
    public function accept()
    {
        return stripos($this->current()->name(), $this->name) !== false;
    }
}