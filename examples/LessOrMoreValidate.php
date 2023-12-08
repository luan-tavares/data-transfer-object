<?php

namespace LTL\DataTransferObject\Examples;

use LTL\DataTransferObject\Exceptions\ValidationDTOException;
use LTL\DataTransferObject\Interfaces\ValidateInterface;

class LessOrMoreValidate implements ValidateInterface
{
    public function __construct(private int $less)
    {
    }

    public function validate(mixed $value): void
    {
        if($value < $this->less) {
            throw new ValidationDTOException("LESS_THAN_{$this->less}");
        }
    }
}