<?php

namespace Dru1x\ExpoPush\Support;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * An abstract collection class with some useful helpers
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @template-implements IteratorAggregate<TKey, TValue>
 *
 * @phpstan-consistent-constructor
 */
class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    use ConvertsFromJson, ConvertsToJson;

    /**
     * The items contained in the collection
     *
     * @var array<TKey, TValue>
     */
    protected array $items = [];

    /**
     * @template TNewKey of array-key
     * @template TNewValue
     *
     * @param iterable<TNewKey, TNewValue> $iterable
     *
     * @return static<TNewKey, TNewValue>
     */
    public static function fromIterable(iterable $iterable = []): static
    {
        // @phpstan-ignore-next-line
        return match(static::class === self::class) {
            true => self::base($iterable),
            false => new static(...iterator_to_array($iterable)),
        };
    }

    /**
     * @template TNewKey of array-key
     * @template TNewValue
     *
     * @param iterable<TNewKey, TNewValue> $iterable
     *
     * @return self<TNewKey, TNewValue>
     */
    public static function base(iterable $iterable = []): self
    {
        $self = new self;

        $self->items = iterator_to_array($iterable);

        return $self;
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
     * @return self<int, static<int, TValue>>
     */
    public function chunk(int $size): self
    {
        $chunks = array_chunk($this->items, $size);

        return static::base(
            array_map(fn(array $chunk) => static::fromIterable($chunk), $chunks),
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

        return static::fromIterable(
            array_filter($this->items, $callable, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * Merge a set of iterables into a single collection
     *
     * @param iterable<TKey, TValue> ...$iterables
     *
     * @return static<TKey, TValue>
     */
    public function merge(iterable ...$iterables): static
    {
        $arrays = array_map(
            fn(iterable $iterator) => iterator_to_array($iterator),
            $iterables,
        );

        return static::fromIterable(
            array_merge($this->items, ...$arrays),
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
     * Get a new collection with the keys reset to consecutive integers
     *
     * @return static<TKey, TValue>
     */
    public function values(): static
    {
        return static::fromIterable(array_values($this->items));
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
     * @return array<TKey, mixed>
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
     * @return static<TKey, TMap>
     */
    public function map(callable $callable): static
    {
        return static::fromIterable(
            array_map(fn(mixed $item) => $callable($item), $this->items)
        );
    }

    // Internals ----

    /**
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}