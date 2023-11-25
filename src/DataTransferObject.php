<?php

namespace LTL\DataTransferObject;

use Countable;
use Error;
use IteratorAggregate;
use LTL\DataTransferObject\DTOCollection;
use LTL\DataTransferObject\Exceptions\DataTransferObjectException;
use LTL\DataTransferObject\Traits\Enumerable;
use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObject implements IteratorAggregate, Countable
{
    use Enumerable;

    private array|null $errors = null;

    private array $fields;

    public function __construct(object|array $data)
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if(error_reporting() === 0) {
                return;
            }

            if(error_reporting() & $severity) {
                throw new DataTransferObjectException($message);
            }
        });

        try {
            $this->resolve($data);
        } catch(Error $error) {
            throw new DataTransferObjectException($error->getMessage());
        }

        restore_error_handler();

        $this->fields = static::fields();

        $notInitialized = [];
       
        foreach ($this->fields as $field) {
            try {
                $this->{$field};
            } catch(Error) {
                $notInitialized[] = $field;
            }
        }

        if(!empty($notInitialized)) {
            $notInitialized = implode(', ', $notInitialized);

            throw new DataTransferObjectException("Properties \"{$notInitialized}\" was not initialized in ". static::class .'.');
        }
    }

    public function __get($name)
    {
        throw new DataTransferObjectException("Property \"{$name}\" does not exist in ". static::class .'.');
    }

    public function __set($name, $value)
    {
        $this->{$name};
    }

    abstract protected function resolve(object|array $data);

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

    public function setError(string $message): void
    {

        if(is_null($this->errors)) {
            $this->errors = [];
        }

        $this->errors[] = $message;
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
