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

    public readonly string $b;

    #[TestCast]
    public string $c;

    public static function createFrom(array $data): self
    {
        return new static($data);
    }
}

try {

    $example = ExampleDTO::createFrom([
        'a' => 1,
        'b' => 5,
        'c' => []
    ]);

} catch(ValidationDTOException $exception) {
    dd($exception->errors());
}

dd($example, $example::fields());
