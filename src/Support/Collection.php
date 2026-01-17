<?php

namespace Dru1x\ExpoPush\Support;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * An abstract collection class with some useful helpers
 *
 * TODO: make it just int? separate data structure for dictionary!
 * @template TKey of array-key
 * @template TValue
 *
 * @template-implements IteratorAggregate<int, TValue>
 */
class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    use ConvertsFromJson, ConvertsToJson;

    /**
     * The items contained in the collection
     *
     * @var array<TKey, TValue>
     */
    protected array $items;

    /**
     * @param iterable<TKey, TValue> $items
     */
    public function __construct(iterable $items)
    {
        $this->items = iterator_to_array($items);
    }

    /**
     * @param iterable<TKey, TValue> $items
     *
     * @return static<TKey, TValue>
     */
    public static function make(iterable $items = []): static
    {
        return new static($items);
    }

    // Helpers ----

    /**
     * Get the size of this collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if this collection contains a given value
     *
     * @param TValue $value
     *
     * @return bool
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * Get an item from this collection by its key
     *
     * @param TKey $index
     *
     * @return TValue|null
     */
    public function get(int|string $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    /**
     * Add an item to this collection
     *
     * @param TValue $item
     *
     * @return $this
     */
    public function add(mixed $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Add an item to this collection at a specific index
     *
     * @param TKey $index
     * @param TValue $item
     *
     * @return $this
     */
    public function set(int|string $index, mixed $item): static
    {
        $this->items[$index] = $item;

        return $this;
    }

    /**
     * Break this collection into a set of smaller chunks
     *
     * @param int<1, max> $size
     *
     * @return static<int, <static>>
     */
    public function chunk(int $size, bool $preserveKeys = false): static
    {
        $chunks = array_chunk($this->items, $size, $preserveKeys);

        return new static(
            array_map(fn(array $chunk) => new static($chunk), $chunks)
        );
    }

    /**
     * Filter this collection using the given callback
     *
     * @param ?callable(TValue, TKey): bool $callable
     *
     * @return static
     */
    public function filter(?callable $callable): static
    {
        $callable ??= fn(mixed $item) => $item;

        return new static(
            array_filter($this->items, $callable, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * Merge a set of collections into a single collection
     *
     * @param static<TKey, TValue> ...$collections
     *
     * @return static<TKey, TValue>
     */
    public function merge(self ...$collections): static
    {
        $items = array_reduce(
            $collections,
            fn(array $carry, self $collection) => array_merge($carry, $collection->items),
            $this->items
        );

        return new static(...$items);
    }

    /**
     * Reduce the collection to a single value.
     *
     * @template TReduceReturnType
     *
     * @param  callable(TReduceReturnType, TValue): TReduceReturnType  $callback
     * @param  TReduceReturnType  $initial
     * @return TReduceReturnType
     */
    public function reduce(callable $callback, mixed $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Get the sum of the given values.
     *
     * @template TReturnType of array|float|int
     *
     * @param  (callable(TValue): TReturnType)|null $callable
     * @param TReturnType $initial
     * @return ($callable is callable ? TReturnType : TValue)
     */
    public function sum(?callable $callable = null, mixed $initial = 0): mixed
    {
        $callable ??= fn(mixed $item) => $item;

        return $this->reduce(fn ($carry, $item) => $carry + $callable($item), $initial);
    }

    /**
     * Get a new collection with the keys reset to consecutive integers
     *
     * @return static<TKey, TValue>
     */
    public function values(): static
    {
        return new static(...array_values($this->items));
    }

    /**
     * Convert this collection to an array
     *
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Recursively convert this collection to an array
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->map(fn (mixed $value) => is_iterable($value) ? $value->toArray() : $value)->all();
    }

    /**
     * @template TMap of mixed
     *
     * @param callable(TValue, TKey): TMap $callable
     *
     * @return static<TKey, TMap>
     */
    public function map(callable $callable): static
    {
        return static::make(
            array_map(fn(mixed $item) => $callable($item), $this->items)
        );
    }

    // Internals ----

    /**
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}