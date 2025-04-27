<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Unit\Requests;

use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Data\PushMessage;
use Dru1x\ExpoPush\Data\PushToken;
use Dru1x\ExpoPush\ExpoPushClient;
use Dru1x\ExpoPush\Requests\SendNotificationsRequest;
use InvalidArgumentException;
use OverflowException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use UnexpectedValueException;

class SendNotificationsRequestTest extends TestCase
{
    protected MockClient $mockClient;
    protected ExpoPushClient $connector;

    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        $this->mockClient = new MockClient();
        $this->connector  = new ExpoPushClient()->withMockClient($this->mockClient);

        Config::preventStrayRequests();
    }

    #[Test]
    public function create_dto_from_response_returns_push_ticket_collection(): void
    {
        $request = new SendNotificationsRequest(
            new PushMessageCollection()
        );

        $this->mockClient->addResponses([
            SendNotificationsRequest::class => MockResponse::make(
                body: [
                    'data' => [
                        ['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'],
                        ['status' => 'ok', 'id' => 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'],
                        ['status' => 'ok', 'id' => 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
                    ],
                ],
                headers: ['Content-Type' => 'application/json']
            ),
        ]);

        $dto = $request->createDtoFromResponse(
            $this->connector->send($request)
        );

        $this->assertCount(3, $dto);
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $dto->get(0)->receiptId);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $dto->get(1)->receiptId);
        $this->assertEquals('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', $dto->get(2)->receiptId);
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
    public function create_dto_from_response_throws_exception_for_error_response(): void
    {
        $request = new SendNotificationsRequest(
            new PushMessageCollection()
        );

        $this->mockClient->addResponses([
            SendNotificationsRequest::class => MockResponse::make(
                body: [
                    'errors' => [
                        [
                            "message" => "Too many notifications",
                            "details" => [],
                        ],
                    ],
                ],
                headers: ['Content-Type' => 'application/json']
            ),
        ]);

        $this->expectException(RuntimeException::class);

        $request->createDtoFromResponse(
            $this->connector->send($request)
        );
    }

    #[Test]
    public function body_throws_exception_when_push_message_collection_is_too_large(): void
    {
        $messageCount = SendNotificationsRequest::MAX_MESSAGE_COUNT + 1;
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

    #[Test]
    public function body_throws_exception_when_any_push_message_data_is_too_large(): void
    {
        $contentBytes = SendNotificationsRequest::MAX_MESSAGE_DATA_BYTES + 1;
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
}