<?php

namespace LTL\DataTransferObject\Traits;

use Closure;
use LTL\DataTransferObject\DTOCollection;

trait Enumerable
{
    public function all(): array
    {
        $collection = $this->collection();
        
        return $collection->all();
    }

    /**
     * @param Closure(string $field, mixed $value): bool $callback
     */
    public function each(Closure $callback): DTOCollection
    {
        $collection = $this->collection();
        
        return $collection->each($callback);
    }

    /**
     * @param Closure(string $field, mixed $value): mixed $callback
     */
    public function map(Closure $callback): DTOCollection
    {
        $collection = $this->collection();
        
        return $collection->map($callback);
    }

    /**
     * @param Closure(string $field, mixed $value): array<string, mixed> $callback
     */
    public function mapWithKeys(Closure $callback): DTOCollection
    {
        $collection = $this->collection();
        
        return $collection->map($callback);
    }

    /**
     * @param Closure(string $field, mixed $value): bool $callback
     */
    public function filter(Closure $callback): DTOCollection
    {
        $collection = $this->collection();
        
        return $collection->filter($callback);
    }
}