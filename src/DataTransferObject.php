<?php

namespace LTL\DataTransferObject;

use Countable;
use Error;
use IteratorAggregate;
use LTL\DataTransferObject\DTOCollection;
use LTL\DataTransferObject\Exceptions\DataTransferObjectException;
use LTL\DataTransferObject\Factories\DTOFactory;
use LTL\DataTransferObject\Traits\Enumerable;
use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObject implements IteratorAggregate, Countable
{
    use Enumerable;

    public function __construct(array $data)
    {
        DTOFactory::build($this, $data);
    }

    public function __get($name)
    {
        throw new DataTransferObjectException("Property \"{$name}\" does not exist in ". static::class .'.');
    }

    public function __set($name, $value)
    {
        $this->{$name};
    }

    public function all(): array
    {
        $collection = $this->collection();
        
        return $collection->all();
    }

    public static function fields(): array
    {
        $reflection = new ReflectionClass(static::class);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_READONLY);

        return array_map(function (ReflectionProperty $property) {
            return $property->name;
        }, $properties);
    }

    private function collection(): DTOCollection
    {
        $collection = new DTOCollection;

        foreach ($this->fields as $field) {
            $collection->push($field, $this->{$field});
        }

        return $collection;
    }

    /** IteratorAggregate */

    public function getIterator(): DTOCollection
    {
        return $this->collection();
    }

    /** Countable */

    public function count(): int
    {
        return count($this->fields);
    }
}
