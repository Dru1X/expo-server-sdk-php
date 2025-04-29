<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Feature;

use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Collections\PushReceiptIdCollection;
use Dru1x\ExpoPush\Data\PushMessage;
use Dru1x\ExpoPush\Data\PushToken;
use Dru1x\ExpoPush\Enums\PushStatus;
use Dru1x\ExpoPush\ExpoPushClient;
use Dru1x\ExpoPush\Requests\GetReceiptsRequest;
use Dru1x\ExpoPush\Requests\SendNotificationsRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;

class ExpoPushClientTest extends TestCase
{
    protected MockClient $mockClient;
    protected ExpoPushClient $expoPush;

    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        $this->mockClient = new MockClient();
        $this->expoPush   = new ExpoPushClient()->withMockClient($this->mockClient);

        Config::preventStrayRequests();
    }

    #[Test]
    public function send_notifications_handles_push_message_collection(): void
    {
        $responseBody = [
            'data' => [
                ['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'],
                ['status' => 'ok', 'id' => 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'],
                ['status' => 'ok', 'id' => 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseBody,
                headers: ['Content-Type' => 'application/json']
            )
        );

        $messages = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification'),
        );

        $tickets = $this->expoPush->sendNotifications($messages);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertCount(3, $tickets);

        foreach ($tickets as $index => $ticket) {
            $this->assertEquals(PushStatus::Ok, $ticket->status);
            $this->assertEquals($responseBody['data'][$index]['id'], $ticket->receiptId);
            $this->assertEmpty($ticket->message);
            $this->assertEmpty($ticket->details);
        }
    }

    #[Test]
    public function send_notifications_handles_large_push_message_collection(): void
    {
        $messages = $this->generatePushMessages(1000);

        foreach ($messages->chunk(100) as $messageChunk) {

            $responseBody = [
                'data' => array_map(fn(PushMessage $message) => [
                    'status' => 'ok',
                    'id'     => $this->generatePushReceiptId(),
                ], $messageChunk->toArray()),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json']
                )
            );
        }

        $tickets = $this->expoPush->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(1000, $tickets);
    }

    #[Test]
    public function send_notifications_handles_push_message_array(): void
    {
        $responseData = [
            'data' => [
                ['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'],
                ['status' => 'ok', 'id' => 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'],
                ['status' => 'ok', 'id' => 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseData,
                headers: ['Content-Type' => 'application/json']
            ),
        );

        $messages = [
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification'),
        ];

        $tickets = $this->expoPush->sendNotifications($messages);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertCount(3, $tickets);

        foreach ($tickets as $index => $ticket) {
            $this->assertEquals(PushStatus::Ok, $ticket->status);
            $this->assertEquals($responseData['data'][$index]['id'], $ticket->receiptId);
            $this->assertEmpty($ticket->message);
            $this->assertEmpty($ticket->details);
        }
    }

    #[Test]
    public function send_notifications_handles_large_push_message_array(): void
    {
        $messages = $this->generatePushMessages(1000)->toArray();

        foreach (array_chunk($messages, 100) as $messageChunk) {

            $responseBody = [
                'data' => array_map(fn(PushMessage $message) => [
                    'status' => 'ok',
                    'id'     => $this->generatePushReceiptId(),
                ], $messageChunk),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json']
                )
            );
        }

        $tickets = $this->expoPush->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(1000, $tickets);
    }

    #[Test]
    public function get_receipts_handles_push_receipt_id_collection(): void
    {
        $responseData = [
            'data' => [
                'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => ['status' => 'ok'],
                'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => ['status' => 'ok'],
                'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => ['status' => 'ok'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseData,
                headers: ['Content-Type' => 'application/json']
            ),
        );

        $receiptIds = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $receipts = $this->expoPush->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(1, GetReceiptsRequest::class);

        $this->assertCount(3, $receipts);

        foreach ($receipts as $receipt) {
            $this->assertEquals(PushStatus::Ok, $receipt->status);
            $this->assertTrue($receiptIds->contains($receipt->id));;
            $this->assertEmpty($receipt->message);
            $this->assertEmpty($receipt->details);
        }
    }

    #[Test]
    public function get_receipts_handles_push_receipt_id_array(): void
    {
        $responseData = [
            'data' => [
                'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => ['status' => 'ok'],
                'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => ['status' => 'ok'],
                'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => ['status' => 'ok'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseData,
                headers: ['Content-Type' => 'application/json']
            ),
        );

        $receiptIds = [
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ];

        $receipts = $this->expoPush->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(1, GetReceiptsRequest::class);

        $this->assertCount(3, $receipts);

        foreach ($receipts as $receipt) {
            $this->assertEquals(PushStatus::Ok, $receipt->status);
            $this->assertTrue(in_array($receipt->id, $receiptIds));
            $this->assertEmpty($receipt->message);
            $this->assertEmpty($receipt->details);
        }
    }

    #[Test]
    public function sdk_version_returns_correct_version(): void
    {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__, 2) . '/composer.json')
        );

        $this->assertEquals($composer->version, $this->expoPush->sdkVersion());
    }

    // Helpers ----

    protected function generatePushMessages(int $count): PushMessageCollection
    {
        $messages = [];

        for ($i = 0; $i < $count; $i++) {
            $tokenValue = bin2hex(random_bytes(11));

            $messages[] = new PushMessage(
                to: new PushToken("ExponentPushToken[$tokenValue]"),
                title: "Test Notification $i",
                body: "A simple test push notification"
            );
        }

        return new PushMessageCollection(...$messages);
    }

    protected function generatePushReceiptId(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
