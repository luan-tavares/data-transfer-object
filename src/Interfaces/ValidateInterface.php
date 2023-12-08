<?php

namespace LTL\DataTransferObject\Interfaces;

interface ValidateInterface
{
    public function validate(mixed $value): void;
}
