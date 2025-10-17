<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushMessage;

use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
    public function json_encode_returns_value(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
        );

        $expectedJson = <<<JSON
{
    "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
    "title": "Test Notification", 
    "body": "This is a test notification"
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
        );

        $expectedJson = <<<JSON
{
    "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
    "title": "Test Notification", 
    "body": "This is a test notification"
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
        $this->expectException(JsonException::class);

        PushMessage::fromJson(null);
    }
}
