<?php

namespace LTL\DataTransferObject\Interfaces;

interface CastInterface
{
    public static function cast(mixed $value): mixed;
}
