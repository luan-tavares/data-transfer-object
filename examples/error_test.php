<?php


require_once __DIR__ .'/__bootstrap.php';


use LTL\DataTransferObject\DataTransferObject;
use LTL\DataTransferObject\Examples\Test2Cast;
use LTL\DataTransferObject\Examples\TestCast;

class ExampleDTO extends DataTransferObject
{
    public readonly int|null $a;

    public readonly string $b;

    #[TestCast]
    public int $c;
}

$example = ExampleDTO::create([
    'a' => null,
    'b' => '2',
    'c' => 5
]);

dd($example, $example::fields());
