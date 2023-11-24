<?php


require_once __DIR__ .'/__bootstrap.php';


use LTL\DataTransferObject\DataTransferObject;

class ExampleDTO extends DataTransferObject
{
    public readonly int|null $a;

    public readonly string $b;

    public readonly array $c;

    public readonly int $d;

    public readonly int $f;

    protected function resolve(object|array $data)
    {
        $this->a = null;

        $this->b = 'aaa';

        $this->c = [5];

        $this->d = 88;

        $this->f = 100;
    }
}

$example = new ExampleDTO([]);

dd(
    $example->filter(function ($field, $value) {
        return is_numeric($value);
    })->map(function ($field, $value) {
        
        return [0, 1, $value];
    })->all()
);
