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

    public function __construct(array $data = [])
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
            $result[] = $callback($field, $value);
        }

        return new self($result);
    }

    /**
     * @param Closure(string $field, mixed $value): array<string, mixed> $callback
     */
    public function mapWithKeys(Closure $callback): self
    {
        $result = [];

        $i = 0;
        
        foreach ($this->data as $field => $value) {
            $return = $callback($field, $value);

            if($i === 0) {
                $this->verifyMapWithKeys($return);
            }

            $result[key($return)] = current($return);
            
            $i++;
        }

        return new self($result);
    }

    private function verifyMapWithKeys($return): void
    {
        if(!is_array($return)) {
            throw new DataTransferObjectException('Incorrect mapWithKeys(...) in '. static::class .'.');
        }

        if(count($return) !== 1) {
            throw new DataTransferObjectException('Incorrect mapWithKeys(...) in '. static::class .'.');
        }
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

        return new self($result);
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
        throw new DataTransferObjectException("You can't add or change property  in ". static::class .'.');
    }
    
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }
    
    public function offsetUnset($offset): void
    {
        throw new DataTransferObjectException("You can't remove property  in ". static::class .'.');
    }
    
    public function offsetGet($offset): mixed
    {
        if(!$this->offsetExists($offset)) {
            throw new DataTransferObjectException("Property \"{$offset}\" does not exist in ". static::class .'.');
        }
    
        return $this->data[$offset];
    }
}
