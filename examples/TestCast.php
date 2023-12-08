<?php

namespace LTL\DataTransferObject\Examples;

use LTL\DataTransferObject\Exceptions\ValidationDTOException;
use LTL\DataTransferObject\Interfaces\CastInterface;

class TestCast implements CastInterface
{
    public static function cast(mixed $value): string
    {
        if(true) {
            throw new ValidationDTOException('ERROR_A');
        }

        return 'a';
    }
}
