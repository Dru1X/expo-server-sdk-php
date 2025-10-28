<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushMessage;

use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class PushMessageTest extends TestCase
{
    #[Test]
    public function instantiates_with_single_recipient(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
        );

        $this->assertInstanceOf(PushToken::class, $message->to);
    }

    #[Test]
    public function instantiates_with_multiple_recipients(): void
    {
        $message = new PushMessage(
            to: new PushTokenCollection(
                new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            ),
            title: 'Test Notification',
            body: 'This is a test notification',
        );

        $this->assertInstanceOf(PushTokenCollection::class, $message->to);
        $this->assertCount(3, $message->to);
    }

    #[Test]
    public function instantiates_with_data_list(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            data: ['list', 'of', 'values'],
        );

        $this->assertIsList($message->data);
    }

    #[Test]
    public function instantiates_with_data_map(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            data: ['key' => 'value', 'foo' => 'bar'],
        );

        $this->assertIsArray($message->data);
        $this->assertArrayHasKey('key', $message->data);
        $this->assertEquals('value', $message->data['key']);
        $this->assertArrayHasKey('foo', $message->data);
        $this->assertEquals('bar', $message->data['foo']);
    }

    #[Test]
    public function instantiates_with_data_object(): void
    {
        $dataObject = new stdClass();
        $dataObject->key = 'value';
        $dataObject->foo = 'bar';

        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            data: $dataObject,
        );

        $this->assertIsObject($message->data);
        $this->assertObjectHasProperty('key', $message->data);
        $this->assertEquals('value', $message->data->key);
        $this->assertObjectHasProperty('foo', $message->data);
        $this->assertEquals('bar', $message->data->foo);
    }

    #[Test]
    public function json_encode_returns_value(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
            data: ['list', 'of', 'values'],
        );

        $expectedJson = <<<JSON
{
    "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
    "title": "Test Notification", 
    "body": "This is a test notification",
    "data": {"0":  "list", "1": "of", "2": "values"}
}
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($message));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
            data: ['list', 'of', 'values'],
        );

        $expectedJson = <<<JSON
{
    "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
    "title": "Test Notification", 
    "body": "This is a test notification",
    "data": {"0":  "list", "1": "of", "2": "values"}
}
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $message->toJson());
    }

    #[Test]
    public function from_array_with_dictionary_returns_instance(): void
    {
        $array = [
            'to' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'richContent' => [
                'image' => 'https://example.com',
            ],
        ];

        $message = PushMessage::fromArray($array);

        $this->assertInstanceOf(PushMessage::class, $message);
        $this->assertSame('This is a test notification', $message->body);
        $this->assertSame('https://example.com', $message->richContent->image);
    }

    #[Test]
    public function from_json_returns_instance(): void
    {
        $json = <<<JSON
{
    "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
    "title": "Test Notification", 
    "body": "This is a test notification",
    "richContent": {"image":  "https://example.com"}
}
JSON;

        $message = PushMessage::fromJson($json);

        $this->assertInstanceOf(PushMessage::class, $message);
        $this->assertSame('This is a test notification', $message->body);
        $this->assertSame('https://example.com', $message->richContent->image);
    }

    #[Test]
    public function from_json_with_multiple_tokens_returns_instance(): void
    {
        $json = <<<JSON
{
    "to": [
      "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
      "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]"
    ], 
    "title": "Test Notification", 
    "body": "This is a test notification"
}
JSON;

        $message = PushMessage::fromJson($json);

        $this->assertInstanceOf(PushMessage::class, $message);
        $this->assertInstanceOf(PushTokenCollection::class, $message->to);
    }

    #[Test]
    public function from_json_with_null_throws_error(): void
    {
        $this->expectException(TypeError::class);

        PushMessage::fromJson(null);
    }
}
