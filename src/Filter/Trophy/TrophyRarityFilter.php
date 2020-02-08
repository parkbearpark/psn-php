<?php
namespace Tustin\PlayStation\Filter\Trophy;

use Iterator;
use FilterIterator;

class TrophyRarityFilter extends FilterIterator
{
    private float $value;
    private bool $lessThan;
   
    public function __construct(Iterator $iterator, float $value, bool $lessThan)
    {
        parent::__construct($iterator);
        $this->value = $value;
        $this->lessThan = $lessThan;
    }
   
    public function accept() : bool
    {
        return $this->lessThan ?
        $this->current()->trophyEarnedRate <= $this->value :
        $this->current()->trophyEarnedRate >= $this->value;
    }
}