<?php
namespace Tustin\PlayStation\Iterator\Filter\Trophy;

use Iterator;
use FilterIterator;
use Tustin\PlayStation\Enum\TrophyType;

class TrophyTypeFilter extends FilterIterator
{
    private array $types;
   
    public function __construct(Iterator $iterator, TrophyType ...$types)
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