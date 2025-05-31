<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Unit\Requests;

use Dru1x\ExpoPush\Data\FailedPushTicket;
use Dru1x\ExpoPush\Data\PushToken;
use Dru1x\ExpoPush\Data\SuccessfulPushTicket;
use Dru1x\ExpoPush\ExpoPushConnector;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\PushMessageCollection;
use Dru1x\ExpoPush\Requests\SendNotificationsRequest;
use InvalidArgumentException;
use OverflowException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use UnexpectedValueException;

class SendNotificationsRequestTest extends TestCase
{
    protected MockClient $mockClient;
    protected ExpoPushConnector $connector;

    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        $this->mockClient = new MockClient();
        $this->connector  = new ExpoPushConnector()->withMockClient($this->mockClient);

        Config::preventStrayRequests();
    }

    #[Test]
    public function body_compresses_when_size_is_over_one_kib(): void
    {
        // Approximately 1800 bytes of data
        $pushMessages = array_fill(0, 25, new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: "Test Notification"
        ));

        $request = new SendNotificationsRequest(
            new PushMessageCollection(...$pushMessages)
        );

        $this->mockClient->addResponse(MockResponse::make());

        $this->connector->send($request);

        $lastPendingRequest = $this->mockClient->getLastPendingRequest();

        $this->assertEquals('gzip', $lastPendingRequest->headers()->get('Content-Encoding'));
        $this->assertEquals(gzcompress((string)$request->body()), $lastPendingRequest->body()->all());
    }

    #[Test]
    public function body_doesnt_compress_when_size_is_under_one_kib(): void
    {
        // Approximately 360 bytes of data
        $pushMessages = array_fill(0, 5, new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: "Test Notification"
        ));

        $request = new SendNotificationsRequest(
            new PushMessageCollection(...$pushMessages)
        );

        $this->mockClient->addResponse(MockResponse::make());

        $this->connector->send($request);

        $this->assertNull($this->mockClient->getLastPendingRequest()?->headers()->get('Content-Encoding'));
    }

    #[Test]
    public function body_throws_exception_when_any_push_message_data_is_too_large(): void
    {
        $contentBytes = SendNotificationsRequest::MAX_MESSAGE_DATA_BYTES + 2;
        $content      = bin2hex(random_bytes($contentBytes / 2));

        $messages = new PushMessageCollection(
            new PushMessage(
                to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                title: 'Test Notification 1'
            ),
            new PushMessage(
                to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                title: 'Test Notification 2'
            ),
            new PushMessage(
                to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                title: 'Test Notification 3',
                data: [
                    'content' => $content,
                ]
            ),
        );

        $request = new SendNotificationsRequest($messages);

        $this->expectException(OverflowException::class);

        $request->body();
    }

    #[Test]
    public function create_dto_from_response_returns_push_ticket_collection(): void
    {
        $request = new SendNotificationsRequest(
            new PushMessageCollection(
                new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]')),
                new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]')),
                new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]')),
            )
        );

        $this->mockClient->addResponses([
            SendNotificationsRequest::class => MockResponse::make(
                body: [
                    'data' => [
                        [
                            'status' => 'ok',
                            'id'     => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
                        ],
                        [
                            'status' => 'ok',
                            'id'     => 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
                        ],
                        [
                            'status'  => 'error',
                            'message' => '"ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]" is not a registered push notification recipient',
                            'details' => [
                                'error'         => 'DeviceNotRegistered',
                                'expoPushToken' => 'ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]',
                            ],
                        ],
                    ],
                ],
                headers: ['Content-Type' => 'application/json']
            ),
        ]);

        $dto = $request->createDtoFromResponse(
            $this->connector->send($request)
        );

        $this->assertCount(3, $dto);

        $ticket1 = $dto->get(0);
        $this->assertInstanceOf(SuccessfulPushTicket::class, $ticket1);
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $ticket1->receiptId);

        $ticket2 = $dto->get(1);
        $this->assertInstanceOf(SuccessfulPushTicket::class, $ticket2);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $ticket2->receiptId);

        $ticket3 = $dto->get(2);
        $this->assertInstanceOf(FailedPushTicket::class, $ticket3);
    }

    #[Test]
    public function create_dto_from_response_throws_exception_when_body_cannot_be_decoded(): void
    {
        $request = new SendNotificationsRequest(
            new PushMessageCollection()
        );

        $this->mockClient->addResponses([
            SendNotificationsRequest::class => MockResponse::make(
                body: 'NOT VALID JSON',
                headers: ['Content-Type' => 'application/json']
            ),
        ]);

        $this->expectException(UnexpectedValueException::class);

        $request->createDtoFromResponse(
            $this->connector->send($request)
        );
    }

    #[Test]
    public function body_throws_exception_when_push_message_collection_is_too_large(): void
    {
        $messageCount = SendNotificationsRequest::MAX_NOTIFICATION_COUNT + 1;
        $messages     = [];

        for ($i = 0; $i <= $messageCount; $i++) {

            $tokenValue = bin2hex(random_bytes(11));

            $messages[] = new PushMessage(
                to: new PushToken("ExponentPushToken[$tokenValue]"),
                title: "Test Notification $i"
            );
        }

        $request = new SendNotificationsRequest(
            new PushMessageCollection(...$messages)
        );

        $this->expectException(OverflowException::class);

        $request->body();
    }

    #[Test]
    public function body_throws_exception_when_any_push_message_data_cannot_be_encoded(): void
    {
        $content = random_bytes(100);

        $messages = new PushMessageCollection(
            new PushMessage(
                to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                title: 'Test Notification 1'
            ),
            new PushMessage(
                to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                title: 'Test Notification 2'
            ),
            new PushMessage(
                to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                title: 'Test Notification 3',
                data: [
                    'content' => $content,
                ]
            ),
        );

        $request = new SendNotificationsRequest($messages);

        $this->expectException(InvalidArgumentException::class);

        $request->body();
    }
}