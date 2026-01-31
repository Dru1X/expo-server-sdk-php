<?php

namespace Dru1x\ExpoPush\Tests\Unit\Support;

use ArrayIterator;
use Dru1x\ExpoPush\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    #[Test]
    #[DataProvider('constructProvider')]
    public function can_construct(iterable $data): void
    {
        $this->assertCollection(
            [1, 2],
            Collection::fromIterable($data),
        );
    }

    #[Test]
    public function can_count(): void
    {
        $this->assertCount(
            2,
            Collection::fromIterable([1, 2]),
        );
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function can_contains(mixed $item, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Collection::fromIterable([1, 2])->contains($item),
        );
    }

    #[Test]
    #[DataProvider('getProvider')]
    public function can_get(int|string $key, mixed $value): void
    {
        $this->assertSame(
            $value,
            Collection::fromIterable([1, 2, 3, 'foo' => 4, 5])->get($key),
        );
    }

    #[Test]
    #[DataProvider('addProvider')]
    public function can_add(array $existing, mixed $value, array $result): void
    {
        $this->assertCollection(
            $result,
            Collection::fromIterable($existing)->add($value),
        );
    }

    #[Test]
    #[DataProvider('setProvider')]
    public function can_set(int|string $key, mixed $value): void
    {
        $this->assertCollection(
            [$key => $value],
            Collection::fromIterable()->set($key, $value),
        );
    }

    #[Test]
    public function can_chunk(): void
    {
        $this->assertCollection(
            [[1, 2], [3]],
            Collection::fromIterable([1, 2, 3])->chunk(2),
        );
    }

    #[Test]
    #[DataProvider('filterProvider')]
    public function can_filter(array $items, ?callable $callable, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::fromIterable($items)->filter($callable),
        );
    }

    #[Test]
    #[DataProvider('mergeProvider')]
    public function can_merge(array $toMerge, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::fromIterable([1, 2])->merge(...$toMerge),
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
}