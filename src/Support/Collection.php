<?php

namespace Dru1x\ExpoPush\Support;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * A collection class with some useful helpers
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

    // Creation ----

    /**
     * Create a collection from the provided iterable
     *
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
     * Create a base collection from the provided iterable
     *
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

    // Altering ----

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
     * Add an item to this collection at a specific key
     *
     * @param TKey $key
     * @param TValue $item
     *
     * @return $this
     */
    public function set(int|string $key, mixed $item): static
    {
        $this->items[$key] = $item;

        return $this;
    }

    // Retrieving ----

    /**
     * Get an item from the collection by its key, or null if not present
     *
     * @param TKey $key
     *
     * @return TValue|null
     */
    public function get(int|string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    /**
     * Get all items as an array
     *
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Recursively get all items as an array
     *
     * @return array<TKey, mixed>
     */
    public function toArray(): array
    {
        return $this->map(fn (mixed $value) => $value instanceof self ? $value->toArray() : $value)->all();
    }

    /**
     * Retrieve an iterator for the collection
     *
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    // Helpers ----

    /**
     * Break the collection into chunks of the provided length
     *
     * @param int<1, max> $length
     *
     * @return self<int, static<int, TValue>>
     */
    public function chunk(int $length): self
    {
        $chunks = array_chunk($this->items, $length);

        return static::base(
            array_map(fn(array $chunk) => static::fromIterable($chunk), $chunks),
        );
    }

    /**
     * Check if the collection contains a given value
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
     * Get the number of items in the collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Filter the collection to items where the callback returns a truthy value
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
     * Create a new collection by running the provided callable on each of the items of this collection
     *
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
     * Sum the items in the collection
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
     * Reduce the collection into a single value
     *
     * @template TReturnType
     *
     * @param  callable(TReturnType, TValue): TReturnType  $callback
     * @param  TReturnType  $initial
     * @return TReturnType
     */
    public function reduce(callable $callback, mixed $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Create a new collection with the same items but consecutive integer keys
     *
     * @return static<TKey, TValue>
     */
    public function values(): static
    {
        return static::fromIterable(array_values($this->items));
    }
}