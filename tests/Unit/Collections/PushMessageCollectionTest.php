<?php

namespace Dru1x\ExpoPush\Tests\Unit\Collections;

use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Collections\PushTokenCollection;
use Dru1x\ExpoPush\Data\PushMessage;
use Dru1x\ExpoPush\Data\PushToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ValueError;

class PushMessageCollectionTest extends TestCase
{
    #[Test]
    public function notification_count_correctly_counts_resultant_notifications(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                new PushToken('ExponentPushToken[dddddddddddddddddddddd]'),
                new PushToken('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]'),
                new PushToken('ExponentPushToken[ffffffffffffffffffffff]'),
                new PushToken('ExponentPushToken[gggggggggggggggggggggg]'),
                new PushToken('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]'),
            ), title: 'Test Notification 6'),
        );

        $count = $collection->notificationCount();

        $this->assertEquals(11, $count);
    }

    #[Test]
    public function chunk_by_notifications_correctly_splits_up_push_messages(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                new PushToken('ExponentPushToken[dddddddddddddddddddddd]'),
                new PushToken('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]'),
                new PushToken('ExponentPushToken[ffffffffffffffffffffff]'),
                new PushToken('ExponentPushToken[gggggggggggggggggggggg]'),
                new PushToken('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]'),
            ), title: 'Test Notification 6'),
        );

        $chunks = $collection->chunkByNotifications(3);

        $this->assertCount(4, $chunks);

        $this->assertCount(3, $chunks[0]);
        $this->assertEquals('Test Notification 1', $chunks[0]->get(0)->title);
        $this->assertEquals('Test Notification 2', $chunks[0]->get(1)->title);
        $this->assertEquals('Test Notification 3', $chunks[0]->get(2)->title);

        $this->assertCount(3, $chunks[1]);
        $this->assertEquals('Test Notification 4', $chunks[1]->get(0)->title);
        $this->assertEquals('Test Notification 5', $chunks[1]->get(1)->title);
        $this->assertEquals('Test Notification 6', $chunks[1]->get(2)->title);

        $this->assertCount(1, $chunks[2]);
        $this->assertEquals('Test Notification 6', $chunks[2]->get(0)->title);
        $this->assertCount(3, $chunks[2]->get(0)->to);

        $this->assertCount(1, $chunks[3]);
        $this->assertEquals('Test Notification 6', $chunks[3]->get(0)->title);
        $this->assertCount(2, $chunks[3]->get(0)->to);
    }

    #[Test]
    public function chunk_by_notifications_throws_exception_if_size_is_less_than_one(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
        );

        $this->expectException(ValueError::class);

        $collection->chunkByNotifications(0);
    }

    #[Test]
    public function get_push_tokens_returns_correctly_ordered_push_token_collection(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                new PushToken('ExponentPushToken[dddddddddddddddddddddd]'),
                new PushToken('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]'),
                new PushToken('ExponentPushToken[ffffffffffffffffffffff]'),
                new PushToken('ExponentPushToken[gggggggggggggggggggggg]'),
                new PushToken('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]'),
            ), title: 'Test Notification 6'),
        );

        $tokens = $collection->getPushTokens();

        $this->assertCount(11, $tokens);

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $tokens->get(0)->toString());
        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $tokens->get(1)->toString());
        $this->assertEquals('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]', $tokens->get(2)->toString());
        $this->assertEquals('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]', $tokens->get(3)->toString());
        $this->assertEquals('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]', $tokens->get(4)->toString());
        $this->assertEquals('ExponentPushToken[cccccccccccccccccccccc]', $tokens->get(5)->toString());
        $this->assertEquals('ExponentPushToken[dddddddddddddddddddddd]', $tokens->get(6)->toString());
        $this->assertEquals('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]', $tokens->get(7)->toString());
        $this->assertEquals('ExponentPushToken[ffffffffffffffffffffff]', $tokens->get(8)->toString());
        $this->assertEquals('ExponentPushToken[gggggggggggggggggggggg]', $tokens->get(9)->toString());
        $this->assertEquals('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]', $tokens->get(10)->toString());
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        foreach ($collection as $message) {
            $this->assertInstanceOf(PushMessage::class, $message);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_message_exists(): void
    {
        $message1 = new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1');
        $message2 = new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2');

        $collection = new PushMessageCollection($message1, $message2);

        $this->assertTrue($collection->contains($message1));
        $this->assertTrue($collection->contains($message2));
    }

    #[Test]
    public function contains_returns_false_when_push_message_doesnt_exist(): void
    {
        $message1 = new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1');
        $message2 = new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2');
        $message3 = new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3');

        $collection = new PushMessageCollection($message1, $message2);

        $this->assertFalse($collection->contains($message3));
    }

    #[Test]
    public function get_returns_correct_push_message(): void
    {
        $message1 = new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1');
        $message2 = new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2');

        $collection = new PushMessageCollection($message1, $message2);

        $message = $collection->get(1);

        $this->assertEquals($message2, $message);
    }

    #[Test]
    public function get_returns_null_if_push_message_not_found(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
        );

        $message = $collection->get(99);

        $this->assertNull($message);
    }

    #[Test]
    public function count_returns_correct_push_message_count(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushToken('ExponentPushToken[cccccccccccccccccccccc]'), title: 'Test Notification 6'),
        );

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function to_array_returns_push_message_array(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $message) {
            $this->assertInstanceOf(PushMessage::class, $message);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $this->assertJsonStringEqualsJsonString(
            '[{"to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", "title": "Test Notification 1"}, {"to": "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]", "title": "Test Notification 2"}, {"to": "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]", "title": "Test Notification 3"}]',
            json_encode($collection),
        );
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $this->assertJsonStringEqualsJsonString(
            '[{"to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", "title": "Test Notification 1"}, {"to": "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]", "title": "Test Notification 2"}, {"to": "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]", "title": "Test Notification 3"}]',
            $collection->toJson(),
        );
    }
}
