<?php

/** @noinspection PhpUnhandledExceptionInspection */


use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Collections\PushReceiptIdCollection;
use Dru1x\ExpoPush\Data\PushMessage;
use Dru1x\ExpoPush\Data\PushReceipt;
use Dru1x\ExpoPush\Data\PushTicket;
use Dru1x\ExpoPush\Data\PushToken;
use Dru1x\ExpoPush\Data\SuccessfulPushTicket;
use Dru1x\ExpoPush\Enums\PushErrorCode;
use Dru1x\ExpoPush\Enums\PushStatus;
use Dru1x\ExpoPush\ExpoPush;
use Dru1x\ExpoPush\ExpoPushConnector;
use Dru1x\ExpoPush\Requests\GetReceiptsRequest;
use Dru1x\ExpoPush\Requests\SendNotificationsRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class ExpoPushTest extends TestCase
{
    protected MockClient $mockClient;
    protected ExpoPushConnector $connector;

    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        $this->mockClient = new MockClient();
        $this->connector  = new ExpoPushConnector()->withMockClient($this->mockClient);
        $this->service    = new ExpoPush($this->connector);

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

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertCount(3, $result->tickets);
        $this->assertFalse($result->hasErrors());

        foreach ($result->tickets as $index => $ticket) {
            $this->assertInstanceOf(SuccessfulPushTicket::class, $ticket);
            $this->assertEquals(PushStatus::Ok, $ticket->status);
            $this->assertEquals($responseBody['data'][$index]['id'], $ticket->receiptId);
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

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(1000, $result->tickets);
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

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertCount(3, $result->tickets);
        $this->assertFalse($result->hasErrors());

        foreach ($result->tickets as $index => $ticket) {
            $this->assertInstanceOf(SuccessfulPushTicket::class, $ticket);
            $this->assertEquals(PushStatus::Ok, $ticket->status);
            $this->assertEquals($responseData['data'][$index]['id'], $ticket->receiptId);
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

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(1000, $result->tickets);
    }

    #[Test]
    public function send_notifications_leaves_index_gaps_for_request_errors(): void
    {
        $messages = $this->generatePushMessages(1000);

        foreach ($messages->chunk(100) as $index => $messageChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_EXPERIENCE_IDS',
                                    'message' => 'You are trying to send push notifications to different Expo experiences',
                                ],
                            ],
                        ],
                        status: 400,
                        headers: ['Content-Type' => 'application/json']
                    )
                );

                continue;
            }

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

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(900, $result->tickets);

        $this->assertInstanceOf(PushTicket::class, $result->tickets->get(499));
        $this->assertNull($result->tickets->get(500));
        $this->assertNull($result->tickets->get(599));
        $this->assertInstanceOf(PushTicket::class, $result->tickets->get(600));

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyExperienceIds, $result->errors->get(0)->code);
    }

    #[Test]
    public function send_notifications_exposes_push_errors_for_each_request_error(): void
    {
        $messages = $this->generatePushMessages(1000);

        foreach ($messages->chunk(100) as $index => $messageChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_EXPERIENCE_IDS',
                                    'message' => 'You are trying to send push notifications to different Expo experiences',
                                ],
                            ],
                        ],
                        status: 400,
                        headers: ['Content-Type' => 'application/json']
                    )
                );

                continue;
            }

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

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyExperienceIds, $result->errors->get(0)->code);
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

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(1, GetReceiptsRequest::class);

        $this->assertCount(3, $result->receipts);

        foreach ($result->receipts as $receipt) {
            $this->assertEquals(PushStatus::Ok, $receipt->status);
            $this->assertTrue($receiptIds->contains($receipt->id));;
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

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(1, GetReceiptsRequest::class);

        $this->assertCount(3, $result->receipts);

        foreach ($result->receipts as $receipt) {
            $this->assertEquals(PushStatus::Ok, $receipt->status);
            $this->assertTrue(in_array($receipt->id, $receiptIds));
        }
    }

    #[Test]
    public function get_receipts_leaves_index_gaps_for_request_errors(): void
    {
        $receiptIds = $this->generatePushReceiptIds(10000);

        foreach ($receiptIds->chunk(1000) as $index => $receiptIdChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_RECEIPTS',
                                    'message' => 'You are trying to get more than 1000 push receipts in one request',
                                ],
                            ],
                        ],
                        status: 400,
                        headers: ['Content-Type' => 'application/json']
                    )
                );

                continue;
            }

            $responseBody = [
                'data' => array_combine(
                    $receiptIdChunk->toArray(),
                    array_fill(0, count($receiptIdChunk), ['status' => 'ok'])
                ),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json']
                )
            );
        }

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(10, GetReceiptsRequest::class);

        $this->assertCount(9000, $result->receipts);

        $this->assertInstanceOf(PushReceipt::class, $result->receipts->get(499));
        $this->assertNull($result->receipts->get(5000));
        $this->assertNull($result->receipts->get(5999));
        $this->assertInstanceOf(PushReceipt::class, $result->receipts->get(600));

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyReceipts, $result->errors->get(0)->code);
    }

    #[Test]
    public function get_receipts_exposes_push_errors_for_each_request_error(): void
    {
        $receiptIds = $this->generatePushReceiptIds(10000);

        foreach ($receiptIds->chunk(1000) as $index => $receiptIdChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_RECEIPTS',
                                    'message' => 'You are trying to get more than 1000 push receipts in one request',
                                ],
                            ],
                        ],
                        status: 400,
                        headers: ['Content-Type' => 'application/json']
                    )
                );

                continue;
            }

            $responseBody = [
                'data' => array_combine(
                    $receiptIdChunk->toArray(),
                    array_fill(0, count($receiptIdChunk), ['status' => 'ok'])
                ),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json']
                )
            );
        }

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(10, GetReceiptsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyReceipts, $result->errors->get(0)->code);
    }

    #[Test]
    public function sdk_version_returns_correct_version(): void
    {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__, 2) . '/composer.json')
        );

        $this->assertEquals($composer->version, $this->service->sdkVersion());
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

    protected function generatePushReceiptIds(int $count): PushReceiptIdCollection
    {
        $receiptIds = [];

        for ($i = 0; $i < $count; $i++) {
            $receiptIds[] = $this->generatePushReceiptId();
        }

        return new PushReceiptIdCollection(...$receiptIds);
    }

    protected function generatePushReceiptId(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
