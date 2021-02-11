<?php
namespace Tustin\PlayStation\Iterator\Filter\Trophy;

use Iterator;
use FilterIterator;

class TrophyHiddenFilter extends FilterIterator
{
    private bool $flag;
   
    public function __construct(Iterator $iterator, bool $flag)
    {
        parent::__construct($iterator);
        $this->flag = $flag;
    }
   
    public function accept()
    {
        return $this->current()->hidden() === $this->flag;
    }
}