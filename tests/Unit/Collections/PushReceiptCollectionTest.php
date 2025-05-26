<?php

namespace Dru1x\ExpoPush\Tests\Unit\Collections;

use Dru1x\ExpoPush\Collections\PushReceiptCollection;
use Dru1x\ExpoPush\Data\PushReceipt;
use Dru1x\ExpoPush\Enums\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PushReceiptCollectionTest extends TestCase
{
    #[Test]
    public function add_appends_receipt_to_collection(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
        );

        $collection->add(
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok)
        );

        $this->assertCount(2, $collection);

        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $collection->get(0)->id);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $collection->get(1)->id);
    }

    #[Test]
    public function set_inserts_receipt_to_collection_at_index(): void
    {
        $collection = new PushReceiptCollection();

        $collection->set(9, new PushReceipt(
            id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            status: PushStatus::Ok,
        ));

        $this->assertCount(1, $collection);
        $this->assertNull($collection->get(0));
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $collection->get(9)->id);
    }

    #[Test]
    public function set_replaces_receipt_in_collection_at_index(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok)
        );

        $collection->set(0, new PushReceipt(
            id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            status: PushStatus::Ok,
        ));

        $this->assertCount(1, $collection);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $collection->get(0)->id);
    }

    #[Test]
    public function get_by_id_returns_correct_receipt(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
        );

        $receipt = $collection->getById('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $this->assertInstanceOf(PushReceipt::class, $receipt);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $receipt->id);
        $this->assertEquals(PushStatus::Ok, $receipt->status);
    }

    #[Test]
    public function get_by_id_returns_null_if_receipt_not_found(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
        );

        $receipt = $collection->getById('ABCDEFGH-ABCDEF-ABCDEF-ABCDEF-ABCDEFGHIJKL');;

        $this->assertNull($receipt);
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
        );

        foreach ($collection as $receipt) {
            $this->assertInstanceOf(PushReceipt::class, $receipt);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_receipt_exists(): void
    {
        $receipt1 = new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok);
        $receipt2 = new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok);
        $receipt3 = new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok);

        $collection = new PushReceiptCollection($receipt1, $receipt2, $receipt3);

        $this->assertTrue($collection->contains($receipt1));
        $this->assertTrue($collection->contains($receipt2));
    }

    #[Test]
    public function contains_returns_false_when_push_receipt_doesnt_exist(): void
    {
        $receipt1 = new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok);
        $receipt2 = new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok);
        $receipt3 = new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok);

        $collection = new PushReceiptCollection($receipt1, $receipt2);

        $this->assertFalse($collection->contains($receipt3));
    }

    #[Test]
    public function get_returns_correct_push_receipt(): void
    {
        $receipt1 = new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok);
        $receipt2 = new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok);

        $collection = new PushReceiptCollection($receipt1, $receipt2);

        $receipt = $collection->get(1);

        $this->assertEquals($receipt2, $receipt);
    }

    #[Test]
    public function get_returns_null_if_push_receipt_not_found(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
        );

        $receipt = $collection->get(99);

        $this->assertNull($receipt);
    }

    #[Test]
    public function count_returns_correct_push_receipt_count(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
            new PushReceipt(id: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', status: PushStatus::Ok),
            new PushReceipt(id: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', status: PushStatus::Ok),
            new PushReceipt(id: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', status: PushStatus::Ok),
        );

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function to_array_returns_push_receipt_array(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $receipt) {
            $this->assertInstanceOf(PushReceipt::class, $receipt);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
        );

        $expectedJson = <<<JSON
[
  {
    "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
    "status": "ok"
  },
  {
    "id": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
    "status": "ok"
  },
  {
    "id": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ",
    "status": "ok"
  }
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushReceiptCollection(
            new PushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', status: PushStatus::Ok),
            new PushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', status: PushStatus::Ok),
            new PushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', status: PushStatus::Ok),
        );

        $expectedJson = <<<JSON
[
  {
    "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
    "status": "ok"
  },
  {
    "id": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
    "status": "ok"
  },
  {
    "id": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ",
    "status": "ok"
  }
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }
}