<?php
namespace Tustin\PlayStation\Iterator\Filter;

use Iterator;
use FilterIterator;

class UserFilter extends FilterIterator
{
    private array $onlineIds;
   
    public function __construct(Iterator $iterator, string ...$onlineIds)
    {
        parent::__construct($iterator);
        $this->onlineIds = $onlineIds;
    }
   
    public function accept()
    {
        $user = $this->current();

        return count(array_filter($this->onlineIds, function($onlineId) use ($user){
            return stripos($user->onlineId(), $onlineId) !== false;
        })) > 0;
        
    }
}