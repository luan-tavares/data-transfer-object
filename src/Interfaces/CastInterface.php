<?php

namespace LTL\DataTransferObject\Interfaces;

interface CastInterface
{
    public function cast(string $property, mixed $value): mixed;
}
