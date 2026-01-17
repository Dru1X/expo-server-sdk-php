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
            Collection::make($data),
        );
    }

    #[Test]
    public function can_count(): void
    {
        $this->assertCount(
            2,
            Collection::make([1, 2]),
        );
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function can_contains(mixed $item, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Collection::make([1, 2])->contains($item),
        );
    }

    #[Test]
    #[DataProvider('getProvider')]
    public function can_get(int|string $key, mixed $value): void
    {
        $this->assertSame(
            $value,
            Collection::make([1, 2, 3, 'foo' => 4, 5])->get($key),
        );
    }

    #[Test]
    #[DataProvider('addProvider')]
    public function can_add(array $existing, mixed $value, array $result): void
    {
        $this->assertCollection(
            $result,
            Collection::make($existing)->add($value),
        );
    }

    #[Test]
    #[DataProvider('setProvider')]
    public function can_set(int|string $key, mixed $value): void
    {
        $this->assertCollection(
            [$key => $value],
            Collection::make()->set($key, $value),
        );
    }

    #[Test]
    #[DataProvider('chunkProvider')]
    public function can_chunk(array $items, bool $preserverKeys, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::make($items)->chunk(2, $preserverKeys),
        );
    }

    #[Test]
    #[DataProvider('filterProvider')]
    public function can_filter(array $items, ?callable $callable, array $expected): void
    {
        $this->assertCollection(
            $expected,
            Collection::make($items)->filter($callable),
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

    public static function chunkProvider(): array
    {
        return [
            'dont preserve keys' => [[1, 2, 3], false, [[1, 2], [3]]],
            'preserve keys' => [[1, 2, 3], true, [[1, 2], [2 => 3]]],
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
}