<?php

namespace LTL\DataTransferObject;

use ArrayAccess;
use Closure;
use Countable;
use Iterator;
use LTL\DataTransferObject\Exceptions\DataTransferObjectException;

class DTOCollection implements Countable, Iterator, ArrayAccess
{
    private array $data;

    public function __construct(private string $className, array $data = [])
    {
        $this->pushAll($data);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function pushAll(array $data): void
    {
        foreach ($data as $field => $item) {
            $this->push($field, $item);
        }
    }

    public function push(string $field, mixed $value): void
    {
        $this->data[$field] = $value;
    }

    /**
     * @param Closure(string $field, mixed $value): bool $callback
     */
    public function each(Closure $callback): self
    {
        foreach ($this->data as $field => $value) {
            $callback($field, $value);
        }
       
        return $this;
    }

    /**
     * @param Closure(string $field, mixed $value): mixed $callback
     */
    public function map(Closure $callback): self
    {
        $result = [];
        
        foreach ($this->data as $field => $value) {
            $result[$field] = $callback($field, $value);
        }

        return new self($this->className, $result);
    }

    /**
     * @param Closure(string $field, mixed $value): bool $callback
     */
    public function filter(Closure $callback): self
    {
        $result = [];
        
        foreach ($this->data as $field => $value) {
            if ($callback($field, $value)) {
                $result[$field] = $value;
            }
        }

        return new self($this->className, $result);
    }

    /**Iterator */

    public function rewind(): void
    {
        reset($this->data);
    }
    
    public function current(): mixed
    {
        return current($this->data);
    }
    
    public function key(): string
    {
        return key($this->data);
    }
    
    public function next(): void
    {
        next($this->data);
    }
    
    public function valid(): bool
    {
        return !is_null(key($this->data));
    }

    /** Countable */

    public function count(): int
    {
        return count($this->data);
    }

    /** ArrayAccess */

    public function offsetSet($offset, $value): void
    {
        throw new DataTransferObjectException("You can't add or change property  in {$this->className}");
    }
    
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }
    
    public function offsetUnset($offset): void
    {
        throw new DataTransferObjectException("You can't remove property  in {$this->className}");
    }
    
    public function offsetGet($offset): mixed
    {
        if(!$this->offsetExists($offset)) {
            throw new DataTransferObjectException("Property \"{$offset}\" does not exist in {$this->className}");
        }
    
        return $this->data[$offset];
    }
}
