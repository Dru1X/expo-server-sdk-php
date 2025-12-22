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
 * @template TKey of array-key
 * @template TValue
 *
 * @template-implements IteratorAggregate<TKey, TValue>
 */
abstract class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    use ConvertsFromJson, ConvertsToJson;

    /**
     * The items contained in the collection
     *
     * @var array<TKey, TValue>
     */
    protected array $items;

    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
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
     * @param int|string $index
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
     * @return void
     */
    public function add(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Upsert an item to this collection at a specific index
     *
     * @param int   $index
     * @param mixed $item
     *
     * @return void
     */
    public function set(int $index, mixed $item): void
    {
        $this->items[$index] = $item;

    }

    /**
     * Break this collection into a set of smaller chunks
     *
     * @param int<1, max> $size
     *
     * @return array<array-key, static<TKey, TValue>>
     */
    public function chunk(int $size): array
    {
        $chunks = array_chunk($this->items, $size);

        return array_map(fn(array $chunk) => new static(...$chunk), $chunks);
    }

    /**
     * Filter this collection using the given callback
     *
     * @param callable(TValue $item, TKey $key): bool $callback
     *
     * @return static
     */
    public function filter(callable $callback): static
    {
        return new static(
            ...array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH)
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
    public function toArray(): array
    {
        return $this->items;
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