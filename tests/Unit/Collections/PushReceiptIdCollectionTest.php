<?php

namespace Collections;

use Dru1x\ExpoPush\Collections\PushReceiptIdCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PushReceiptIdCollectionTest extends TestCase
{
    #[Test]
    public function add_appends_receipt_id_to_collection(): void
    {
        $collection = new PushReceiptIdCollection('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');

        $collection->add('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $this->assertCount(2, $collection);

        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $collection->get(0));
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $collection->get(1));
    }

    #[Test]
    public function set_inserts_receipt_id_to_collection_at_index(): void
    {
        $collection = new PushReceiptIdCollection();

        $collection->set(9, 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');

        $this->assertCount(1, $collection);
        $this->assertNull($collection->get(0));
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $collection->get(9));
    }

    #[Test]
    public function set_replaces_receipt_id_in_collection_at_index(): void
    {
        $collection = new PushReceiptIdCollection('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');

        $collection->set(0, 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $this->assertCount(1, $collection);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $collection->get(0));
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        foreach ($collection as $receipt) {
            $this->assertIsString($receipt);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_receipt_id_exists(): void
    {
        $receipt1 = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
        $receipt2 = 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY';
        $receipt3 = 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ';

        $collection = new PushReceiptIdCollection($receipt1, $receipt2, $receipt3);

        $this->assertTrue($collection->contains($receipt1));
        $this->assertTrue($collection->contains($receipt2));
    }

    #[Test]
    public function contains_returns_false_when_push_receipt_id_doesnt_exist(): void
    {
        $receipt1 = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
        $receipt2 = 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY';
        $receipt3 = 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ';

        $collection = new PushReceiptIdCollection($receipt1, $receipt2);

        $this->assertFalse($collection->contains($receipt3));
    }

    #[Test]
    public function get_returns_correct_push_receipt_id(): void
    {
        $receipt1 = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
        $receipt2 = 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY';

        $collection = new PushReceiptIdCollection($receipt1, $receipt2);

        $receipt = $collection->get(1);

        $this->assertEquals($receipt2, $receipt);
    }

    #[Test]
    public function get_returns_null_if_push_receipt_id_not_found(): void
    {
        $collection = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        );

        $receipt = $collection->get(99);

        $this->assertNull($receipt);
    }

    #[Test]
    public function count_returns_correct_push_receipt_id_count(): void
    {
        $collection = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
        );

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function to_array_returns_push_receipt_id_array(): void
    {
        $collection = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $receipt) {
            $this->assertIsString($receipt);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $expectedJson = <<<JSON
[
  "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
  "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
  "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ"
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $expectedJson = <<<JSON
[
  "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
  "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
  "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ"
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }
}