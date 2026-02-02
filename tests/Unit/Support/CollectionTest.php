<?php

namespace Dru1x\ExpoPush\Tests\Unit\Support;

use ArrayIterator;
use Dru1x\ExpoPush\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;
use Traversable;

class CollectionTest extends TestCase
{
    #[Test]
    #[DataProvider('constructProvider')]
    public function can_construct_a_collection(iterable $data): void
    {
        $this->assertCollection(
            [1, 2],
            Collection::fromIterable($data),
        );
    }

    #[Test]
    #[DataProvider('constructProvider')]
    public function can_construct_a_base_collection(iterable $data): void
    {
        $this->assertCollection(
            [1, 2],
            Collection::base($data),
        );
    }

    #[Test]
    public function can_count_a_collection(): void
    {
        $this->assertCount(
            2,
            Collection::fromIterable([1, 2]),
        );
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function can_check_if_a_collection_contains_a_given_value(mixed $item, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Collection::fromIterable([1, 2])->contains($item),
        );
    }

    #[Test]
    #[DataProvider('getProvider')]
    public function can_retrieve_a_collection_value_by_its_key(int|string $key, mixed $value): void
    {
        $this->assertSame(
            $value,
            Collection::fromIterable([1, 2, 3, 'foo' => 4, 5])->get($key),
        );
    }

    #[Test]
    #[DataProvider('addProvider')]
    public function can_add_an_item_to_a_collection(array $existing, mixed $value, array $result): void
    {
        $this->assertCollection(
            $result,
            Collection::fromIterable($existing)->add($value),
        );
    }

    #[Test]
    #[DataProvider('setProvider')]
    public function can_set_a_collection_value_at_the_given_key(int|string $key, mixed $value): void
    {
        $this->assertCollection(
            [$key => $value],
            Collection::fromIterable()->set($key, $value),
        );
    }

    #[Test]
    public function can_break_a_collection_into_chunks(): void
    {
        $this->assertCollection(
            [[1, 2], [3]],
            Collection::fromIterable([1, 2, 3])->chunk(2),
        );
    }

    #[Test]
    #[DataProvider('filterProvider')]
    public function can_filter_a_collection(array $items, ?callable $callable, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::fromIterable($items)->filter($callable),
        );
    }

    #[Test]
    #[DataProvider('mergeProvider')]
    public function can_merge_a_collection_with_provided_iterables(array $toMerge, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::fromIterable([1, 2])->merge(...$toMerge),
        );
    }

    #[Test]
    #[DataProvider('reduceProvider')]
    public function can_reduce_a_collection_to_a_single_value(array $items, callable $callable, mixed $initial, mixed $expected): void
    {
        $this->assertSame(
            $expected,
            Collection::fromIterable($items)->reduce($callable, $initial),
        );
    }

    #[Test]
    #[DataProvider('sumProvider')]
    public function can_sum_a_collection_with_provided_callable(array $items, ?callable $callable, mixed $initial, mixed $expected): void
    {
        $this->assertSame(
            $expected,
            Collection::fromIterable($items)->sum($callable, $initial),
        );
    }

    #[Test]
    #[DataProvider('valuesProvider')]
    public function can_create_a_consecutively_keyed_collection(array $items, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::fromIterable($items)->values(),
        );
    }

    #[Test]
    #[DataProvider('allProvider')]
    public function can_retrieve_all_items_from_a_collection(array $items): void
    {
        $this->assertSame(
            $items,
            Collection::fromIterable($items)->all(),
        );
    }

    #[Test]
    #[DataProvider('toArrayProvider')]
    public function can_recursively_convert_a_collection_to_an_array(array $items, array $expected): void
    {
        $this->assertSame(
            $expected,
            Collection::fromIterable($items)->toArray(),
        );
    }

    #[Test]
    public function can_map_over_a_collection(): void
    {
        $this->assertCollection(
            [1, 4, 9],
            Collection::fromIterable([1, 2, 3])->map(fn(int $item) => $item * $item),
        );
    }

    #[Test]
    #[DataProvider('getIteratorProvider')]
    public function can_create_an_iterator_from_a_collection(iterable $items): void
    {
        $array = $items = iterator_to_array($items);

        $iterator = Collection::fromIterable($items)->getIterator();

        $results = iterator_to_array($iterator);

        $this->assertInstanceOf(Traversable::class, $iterator);
        $this->assertSame($results, $array);
    }


    #[Test]
    public function can_check_if_a_collection_is_empty(): void
    {
        $empty = Collection::fromIterable();
        $notEmpty = Collection::fromIterable([0]);

        $this->assertTrue(
            $empty->isEmpty()
        );

        $this->assertFalse(
            $notEmpty->isEmpty()
        );
    }

    #[Test]
    #[DataProvider('rejectProvider')]
    public function can_reject_items_from_a_collection(array $items, ?callable $callable, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::fromIterable($items)->reject($callable),
        );
    }

    protected function assertCollection(array $expected, Collection $actual): void
    {
        self::assertThat(
            $actual->toArray(),
            new IsIdentical($expected),
        );
    }

    public static function constructProvider(): array
    {
        return [
            'array' => [[1, 2]],
            'iterable' => [new ArrayIterator([1, 2])],
            'generator' => [(function() {
                foreach([1, 2] as $item) {
                    yield $item;
                }
            })()],
        ];
    }

    public static function containsProvider(): array
    {
        return [
            'does' => [2, true],
            'doesnt' => [3, false],
            'doesnt loose' => ['2', false],
        ];
    }

    public static function getProvider(): array
    {
        return [
            'integer key present' => [2, 3],
            'integer key not present' => [5, null],
            'string key present' => ['foo', 4],
            'string key not present' => ['bar', null],
        ];
    }

    public static function addProvider(): array
    {
        return [
            'list' => [[1, 2], 3, [1, 2, 3]],
            'integer dictionary' => [[3 => 'foo', 1 => 'bar'], 'baz', [3 => 'foo', 1 => 'bar', 4 => 'baz']],
            'string dictionary' => [['foo' => 1, 'bar' => 2], 3, ['foo' => 1, 'bar' => 2, 0 => 3]],
        ];
    }

    public static function setProvider(): array
    {
        return [
            'integer key' => [2, 3],
            'string key' => ['foo', 4],
        ];
    }

    public static function filterProvider(): array
    {
        return [
            'passing callable' => [[1, 2, 3, 4, 5], fn(int $number) => $number % 2, [0 => 1, 2 => 3, 4 => 5]],
            'without passing callable' => [[0, 1, '', null, false, ' '], null, [1 => 1, 5 => ' ']],
            'using key' => [[1, 2, 3, 4, 5], fn(int $_, int $key) => $key % 2, [1 => 2, 3 => 4]],
        ];
    }

    public static function mergeProvider(): array
    {
        return [
            'single' => [[[3, 4]], [1, 2, 3, 4]],
            'multiple' => [[[3, 4], [5, 6]], [1, 2, 3, 4, 5, 6]],
            'iterables' => [[new ArrayIterator([3, 4]), new ArrayIterator([5, 6])], [1, 2, 3, 4, 5, 6]],
        ];
    }

    public static function reduceProvider(): array
    {
        return [
            'integers' => [[1, 2, 3], fn(?int $carry, int $item) => $carry + $item, null, 6],
            'integers with initial' => [[1, 2, 3], fn(int $carry, int $item) => $carry + $item, 4, 10],
            'strings' => [['one', 'two', 'three'], fn(string $carry, string $item) => $carry . $item, '', 'onetwothree'],
            'strings with initial' => [['one', 'two', 'three'], fn(string $carry, string $item) => $carry . $item, 'zero', 'zeroonetwothree'],
        ];
    }

    public static function sumProvider(): array
    {
        return [
            'integers' => [[1, 2, 3], null, null, 6],
            'integers with initial' => [[1, 2, 3], null, 4, 10],
            'custom callback' => [[1, 2, 3], fn(int $item) => $item * 2, null, 12],
            'custom callback with initial' => [[1, 2, 3], fn(int $item) => $item * 2, 4, 16],
            'arrays' => [[['foo' => 1], ['foo' => 2], ['baz' => 3]], null, [], ['foo' => 1, 'baz' => 3]],
            'arrays with initial' => [[['foo' => 1], ['foo' => 2], ['baz' => 3]], null, ['foo' => 4], ['foo' => 4, 'baz' => 3]],
        ];
    }

    public static function valuesProvider(): array
    {
        return [
            'list' => [[1, 2, 3], [1, 2, 3]],
            'integer dictionary' => [[3 => 'foo', 1 => 'bar', 2 => 'baz'], ['foo', 'bar', 'baz']],
            'string dictionary' => [['foo' => 1, 'bar' => 2, 'baz' => 3], [1, 2, 3]],
        ];
    }

    public static function allProvider(): array
    {
        return [
            'list' => [[1, 2, 3]],
            'integer dictionary' => [[3 => 'foo', 1 => 'bar', 2 => 'baz']],
            'string dictionary' => [['foo' => 1, 'bar' => 2, 'baz' => 3]],
        ];
    }

    public static function toArrayProvider(): array
    {
        return [
            'array' => [[1, 2, 3], [1, 2, 3]],
            'nested array' => [[1, [2], [[3]]], [1, [2], [[3]]]],
            'nested collection' => [[1, Collection::fromIterable([2]), Collection::fromIterable([Collection::fromIterable([3])])], [1, [2], [[3]]]],
        ];
    }

    public static function getIteratorProvider(): array
    {
        return [
            'array' => [[1, 2]],
            'iterable' => [new ArrayIterator([1, 2])],
            'generator' => [(function() {
                foreach([1, 2] as $item) {
                    yield $item;
                }
            })()],
        ];
    }

    public static function rejectProvider(): array
    {
        return [
            'passing callable' => [[1, 2, 3, 4, 5], fn(int $number) => $number % 2, [1 => 2, 3 => 4]],
            'without passing callable' => [[0, 1, '', null, false, ' '], null, [0 => 0, 2 => '', 3 => null, 4 => false]],
            'using key' => [[1, 2, 3, 4, 5], fn(int $_, int $key) => $key % 2, [0 => 1, 2 => 3, 4 => 5]],
        ];
    }
}