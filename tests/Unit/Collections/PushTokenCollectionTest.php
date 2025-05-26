<?php

namespace Collections;

use Dru1x\ExpoPush\Collections\PushTokenCollection;
use Dru1x\ExpoPush\Data\PushToken;
use Dru1x\ExpoPush\Enums\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PushTokenCollectionTest extends TestCase
{
    #[Test]
    public function add_appends_token_to_collection(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
        );

        $collection->add(
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]')
        );

        $this->assertCount(2, $collection);

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $collection->get(0)->toString());
        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $collection->get(1)->toString());
    }

    #[Test]
    public function set_inserts_token_to_collection_at_index(): void
    {
        $collection = new PushTokenCollection();

        $collection->set(9, new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'));

        $this->assertCount(1, $collection);
        $this->assertNull($collection->get(0));
        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $collection->get(9)->toString());
    }

    #[Test]
    public function set_replaces_token_in_collection_at_index(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
        );

        $collection->set(0, new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'));

        $this->assertCount(1, $collection);
        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $collection->get(0)->toString());
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        foreach ($collection as $token) {
            $this->assertInstanceOf(PushToken::class, $token);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_token_exists(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');
        $token3 = new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]');

        $collection = new PushTokenCollection($token1, $token2, $token3);

        $this->assertTrue($collection->contains($token1));
        $this->assertTrue($collection->contains($token2));
    }

    #[Test]
    public function contains_returns_false_when_push_token_doesnt_exist(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');
        $token3 = new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]');

        $collection = new PushTokenCollection($token1, $token2);

        $this->assertFalse($collection->contains($token3));
    }

    #[Test]
    public function get_returns_correct_push_token(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');

        $collection = new PushTokenCollection($token1, $token2);

        $token = $collection->get(1);

        $this->assertEquals($token2, $token);
    }

    #[Test]
    public function get_returns_null_if_push_token_not_found(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
        );

        $token = $collection->get(99);

        $this->assertNull($token);
    }

    #[Test]
    public function count_returns_correct_push_token_count(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
            new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
        );

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function values_returns_collection_with_consecutive_keys(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
        );

        $collection->set(9, new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'));

        $newCollection = $collection->values();

        $this->assertIsList($newCollection->toArray());
    }

    #[Test]
    public function to_array_returns_push_token_array(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $token) {
            $this->assertInstanceOf(PushToken::class, $token);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $expectedJson = <<<JSON
[
  "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
  "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]",
  "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]"
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $expectedJson = <<<JSON
[
  "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
  "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]",
  "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]"
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }
}