<?php
namespace Tustin\PlayStation\Traits;

use RuntimeException;

trait OperandParser
{
    protected function parse($lhs, $rhs)
    {
        if (!$this->operand)
        {
            throw new RuntimeException('No such property [operand] exists on class [' . get_class($this) . '], which uses OperandParser.');
        }

        if (!is_string($this->operand))
        {
            throw new RuntimeException('Operand value is not a string.');
        }

        switch ($this->operand)
        {
            case '=':
            return $lhs === $rhs;
            case '>':
            return $lhs > $rhs;
            case '>=':
            return $lhs >= $rhs;
            case '<':
            return $lhs < $rhs;
            case '<=':
            return $lhs <= $rhs;
            case '!=':
            return $lhs !== $rhs;
            default:
            throw new RuntimeException("Operand value [$this->operand] is not supported.");
        }

    }
}