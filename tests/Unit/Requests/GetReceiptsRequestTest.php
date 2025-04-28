<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Unit\Requests;

use Dru1x\ExpoPush\Collections\PushReceiptIdCollection;
use Dru1x\ExpoPush\Data\PushReceipt;
use Dru1x\ExpoPush\ExpoPushClient;
use Dru1x\ExpoPush\Requests\GetReceiptsRequest;
use OverflowException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use UnexpectedValueException;

class GetReceiptsRequestTest extends TestCase
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
    public function create_dto_from_response_returns_push_receipt_collection(): void
    {
        $request = new GetReceiptsRequest(
            new PushReceiptIdCollection()
        );

        $this->mockClient->addResponses([
            GetReceiptsRequest::class => MockResponse::make(
                body: [
                    'data' => [
                        'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => ['status' => 'ok'],
                        'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => ['status' => 'ok'],
                        'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => ['status' => 'ok'],
                    ],
                ],
                headers: ['Content-Type' => 'application/json']
            ),
        ]);

        $dto = $request->createDtoFromResponse(
            $this->connector->send($request)
        );

        $this->assertCount(3, $dto);
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $dto->get(0)->id);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $dto->get(1)->id);
        $this->assertEquals('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', $dto->get(2)->id);
    }

    #[Test]
    public function create_dto_from_response_throws_exception_when_body_cannot_be_decoded(): void
    {
        $request = new GetReceiptsRequest(
            new PushReceiptIdCollection()
        );

        $this->mockClient->addResponses([
            GetReceiptsRequest::class => MockResponse::make(
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
        $request = new GetReceiptsRequest(
            new PushReceiptIdCollection()
        );

        $this->mockClient->addResponses([
            GetReceiptsRequest::class => MockResponse::make(
                body: [
                    'errors' => [
                        [
                            "message" => "Too many receipts",
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
    public function body_throws_exception_when_push_ticket_collection_is_too_large(): void
    {
        $receiptCount   = GetReceiptsRequest::MAX_RECEIPT_COUNT + 1;
        $pushReceiptIds = [];

        for ($i = 0; $i <= $receiptCount; $i++) {
            $pushReceiptIds[] = (string)$i;
        }

        $request = new GetReceiptsRequest(
            new PushReceiptIdCollection(...$pushReceiptIds)
        );

        $this->expectException(OverflowException::class);

        $request->body();
    }
}