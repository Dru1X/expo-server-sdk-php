<?php

namespace Dru1x\ExpoPush\Tests\Unit\Support;

use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\Result\GetReceiptsResult;
use Dru1x\ExpoPush\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    #[Test]
    #[DataProvider('collectionsProvider')]
    public function can_chunk(callable $factory): void
    {
        $result = $factory([1, 2, 3, 4, 5])->chunk(2);

        $this->assertCollection([
            [0 => 1, 1 => 2],
            [0 => 3, 1 => 4],
            [0 => 5],
        ], $result);
    }

    #[Test]
    #[DataProvider('collectionsProvider')]
    public function can_reduce(callable $factory): void
    {
        $result = $factory([1, 2, 3, 4, 5])->reduce(fn(int $carry, int $item) => $carry + $item, 0);

        $this->assertSame(15, $result);
    }

    #[Test]
    #[DataProvider('collectionsProvider')]
    public function can_sum(callable $factory): void
    {
        $resultOne = $factory([1, 2, 3, 4, 5])->sum(fn(int $item) => $item * 2);
        $resultTwo = $factory([1, 2, 3, 4, 5])->sum();

        $this->assertSame(30, $resultOne);
        $this->assertSame(15, $resultTwo);
    }

    public static function collectionsProvider(): array
    {
        return [
            'array collection' => [fn(array $items) => self::collection($items)],
            'variadic collection' => [fn(array $items) => self::variadicCollection($items)],
        ];
    }

    protected function assertCollection(array $expected, Collection $actual): void
    {
        self::assertThat(
            $actual->toArray(),
            new IsIdentical($expected),
        );
    }

    protected static function collection(array $items): Collection
    {
        return new class($items) extends Collection {};
    }

    protected static function variadicCollection(array $numbers): Collection
    {
        return new class(...$numbers) extends Collection
        {
            public function __construct(int ...$number)
            {
                parent::__construct($number);
            }
        };
    }
}