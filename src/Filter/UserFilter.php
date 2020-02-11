<?php
namespace Tustin\PlayStation\Filter;

use Iterator;
use FilterIterator;

class UserFilter extends FilterIterator
{
    private string $onlineId;
    private bool $regex;
   
    public function __construct(Iterator $iterator, string $onlineId, bool $regex = false)
    {
        parent::__construct($iterator);
        $this->onlineId = $onlineId;
        $this->regex = $regex;
    }
   
    public function accept()
    {
        $user = $this->getInnerIterator()->current();

        return $this->regex ?
        preg_match($this->onlineId, $user->onlineId()) === 1 :
        strcasecmp($user->onlineId(), $this->onlineId) === 0;
    }
}