<?php

namespace LTL\DataTransferObject\Examples;

use LTL\DataTransferObject\Exceptions\CastDTOException;
use LTL\DataTransferObject\Interfaces\CastInterface;

class TestCast implements CastInterface
{
    public function cast(string $property, mixed $value): array
    {
        if($value > 4) {
            
            throw new CastDTOException("{$property} is greater 4");
            
        }

        return $value + 1;
    }
}
