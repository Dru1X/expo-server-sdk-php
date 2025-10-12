<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushMessage;

use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
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
    public function can_be_constructed_from_a_dictionary(): void
    {
        $array = [
            'to' => new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
        ];

        $message = PushMessage::fromArray($array);

        $this->assertInstanceOf(PushMessage::class, $message);
        $this->assertSame('This is a test notification', $message->body);
    }

    #[Test]
    public function can_be_constructed_from_a_list(): void
    {
        $array = [
            // to
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            // title
            'Test Notification',
            // subtitle
            null,
            // body
            'This is a test notification',
        ];

        $message = PushMessage::fromArray($array);

        $this->assertInstanceOf(PushMessage::class, $message);
        $this->assertSame('This is a test notification', $message->body);
    }
}
