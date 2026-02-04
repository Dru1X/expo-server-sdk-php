<?php

namespace Dru1x\ExpoPush\Support;

use ArrayIterator;
use Traversable;

/**
 * A collection class with some useful helpers
 *
 * @template TKey of array-key
 * @template TValue
 */
trait Collection
{
    use ConvertsToJson;

    /**
     * The items contained in the collection
     *
     * @var array<TKey, TValue>
     */
    protected array $items = [];

    // Creation ----

    /**
     * @param TValue ...$items
     */
    public function __construct(mixed ...$items)
    {
        $this->items = $items;
    }

    /**
     * @param TValue ...$items
     *
     * @return self<TKey, TValue>
     */
    public static function make(mixed ...$items): self
    {
        return new self(...$items);
    }

    /**
     * @param iterable<TKey, TValue> ...$items
     *
     * @return self<TKey, TValue>
     */
    public static function fromIterable(iterable $items): self
    {
        $array = iterator_to_array($items);

        $self = new self;
        $self->items = $array;

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
     * Get all items as an array
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->all();
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
     * @return GenericCollection<int, self<TKey, TValue>>
     */
    public function chunk(int $length): GenericCollection
    {
        $chunks = array_chunk($this->items, $length);

        return GenericCollection::fromIterable(
            array_map(fn(array $chunk) => new self(...$chunk), $chunks),
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
     * Check if the collection doesnt contains a given value
     *
     * @param TValue $value
     *
     * @return bool
     */
    public function doesntContain(mixed $value): bool
    {
        return ! $this->contains($value);
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
     * @return self<TKey, TValue>
     */
    public function filter(?callable $callable): self
    {
        $callable ??= fn(mixed $item) => $item;

        return self::fromIterable(
            array_filter($this->items, $callable, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * Check if the collection contains no items
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Check if the collection contains any items
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Create a new collection by running the provided callable on each of the items of this collection
     *
     * @template TMap of mixed
     *
     * @param callable(TValue): TMap $callable
     *
     * @return self<TKey, TValue>
     */
    public function map(callable $callable): self
    {
        return self::fromIterable(
            array_map(fn(mixed $item) => $callable($item), $this->items)
        );
    }

    /**
     * Merge a set of iterables into a single collection
     *
     * @param iterable<TKey, TValue> ...$iterables
     *
     * @return self<TKey, TValue>
     */
    public function merge(iterable ...$iterables): self
    {
        $arrays = array_map(
            fn(iterable $iterator) => iterator_to_array($iterator),
            $iterables,
        );

        return new self(
            ...array_merge($this->items, ...$arrays),
        );
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
     * Filter the collection to items where the callback returns a falsy value
     *
     * @param ?callable(TValue, TKey): bool $callable
     *
     * @return self<TKey, TValue>
     */
    public function reject(?callable $callable): self
    {
        $callable ??= fn(mixed $item, int|string $key) => $item;

        $callable = fn(mixed $item, int|string $key) => ! $callable($item, $key);

        return $this->filter($callable);
    }

    /**
     * Create a new collection with the same items but consecutive integer keys
     *
     * @return self<TKey, TValue>
     */
    public function values(): self
    {
        return new self(...array_values($this->items));
    }
}