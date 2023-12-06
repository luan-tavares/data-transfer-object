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

    private array|null $errors = null;

    private function __construct()
    {
    }

    public static function create(array $data): self
    {
        return DTOFactory::build($data, static::class);
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
        $data = [];
        
        $reflection = new ReflectionClass(static::class);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_READONLY);

        foreach ($properties as $property) {
            $data[] = $property->name;
        }
        
        return $data;
    }

    public function errors(): array|null
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !is_null($this->errors);
    }

    public function setError(string $property, string $message): void
    {
        if(is_null($this->errors)) {
            $this->errors = [];
        }

        $this->errors[$property] = $message;
    }

    private function collection(): DTOCollection
    {
        $collection = new DTOCollection(static::class);

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
