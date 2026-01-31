<?php

namespace Dru1x\ExpoPush\Support;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * An abstract collection class with some useful helpers
 *
 * @template TValue
 *
 * @template-implements IteratorAggregate<int, TValue>
 *
 * @phpstan-consistent-constructor
 */
abstract class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    use ConvertsFromJson, ConvertsToJson;

    /**
     * The items contained in the collection
     *
     * @var list<TValue>
     */
    protected array $items;

    /**
     * @param iterable<int, TValue> $iterable
     *
     * @return static<TValue>
     */
    public static function fromIterable(iterable $iterable = []): static
    {
        $array = iterator_to_array($iterable);
        $list = array_values($array);

        return new static($list);
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
     * Break this collection into a set of smaller chunks
     *
     * @param int<1, max> $size
     *
     * @return static<static>
     */
    public function chunk(int $size): static
    {
        $chunks = array_chunk($this->items, $size);

        return new static(
            array_map(fn(array $chunk) => new static($chunk), $chunks)
        );
    }

    /**
     * Filter this collection using the given callback
     *
     * @param ?callable(TValue, int): bool $callable
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
     * Merge a set of iterables into a single collection
     *
     * @param iterable<int, TValue> ...$iterables
     *
     * @return static<TValue>
     */
    public function merge(iterable ...$iterables): static
    {
        $arrays = array_map(
            fn(iterable $iterator) => iterator_to_array($iterator),
            $iterables,
        );

        $lists = array_map(
            fn(array $arrays) => array_values($arrays),
            $arrays,
        );

        return new static(
            array_merge($this->items, ...$lists),
        );
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
     * Convert this collection to an array
     *
     * @return list<TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Recursively convert this collection to an array
     *
     * @return list<mixed>
     */
    public function toArray(): array
    {
        return $this->map(fn (mixed $value) => $value instanceof self ? $value->toArray() : $value)->all();
    }

    /**
     * @template TMap of mixed
     *
     * @param callable(TValue): TMap $callable
     *
     * @return static<TMap>
     */
    public function map(callable $callable): static
    {
        return new static(
            array_map(fn(mixed $item) => $callable($item), $this->items)
        );
    }

    // Internals ----

    /**
     * @return ArrayIterator<int, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}