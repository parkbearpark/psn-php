<?php
namespace Tustin\PlayStation\Iterator\Filter\Story;

use Iterator;
use FilterIterator;
use Tustin\PlayStation\Enum\StoryType;

class StoryTypeFilter extends FilterIterator
{
    private array $types;
   
    public function __construct(Iterator $iterator, StoryType ...$types)
    {
        parent::__construct($iterator);
        $this->types = $types;
    }
   
    public function accept()
    {
        return in_array(
            $this->current()->type(),
            $this->types
        );
    }
}