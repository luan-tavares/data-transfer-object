<?php


require_once __DIR__ .'/__bootstrap.php';


use LTL\DataTransferObject\DataTransferObject;
use LTL\DataTransferObject\Examples\LessOrMoreValidate;
use LTL\DataTransferObject\Examples\TestCast;
use LTL\DataTransferObject\Exceptions\ValidationDTOException;

class ExampleDTO extends DataTransferObject
{
    #[LessOrMoreValidate(10)]
    public readonly int|null $a;

    public readonly int $b;

    #[TestCast]
    public readonly string $c;

    public readonly array $d;

    public static function createFrom(array $data): self
    {
 

        return new static($data);
    }
}

try {

    $example1 = ExampleDTO::createFrom([
        'a' => 1,
        'b' => 5,
    ]);
} catch(ValidationDTOException $exception) {
    dump($exception->errors());
}

    $example = ExampleDTO::createFrom([
        'a' => 15,
        'b' => 5,
        'c' => [],
        'd' => 'as5'
    ]);
