<?php

namespace Dru1x\ExpoPush\Collections;

use ArrayIterator;
use Countable;
use Dru1x\ExpoPush\Traits\ConvertsToJson;
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
    use ConvertsToJson;

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
     * Get the size of this collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Break this collection into a set of smaller chunks
     *
     * @param int $size
     *
     * @return array<array-key, static<TKey, TValue>>
     */
    public function chunk(int $size): array
    {
        $chunks = array_chunk($this->items, $size);

        return array_map(fn(array $chunk) => new static(...$chunk), $chunks);
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