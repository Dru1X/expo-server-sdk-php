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
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
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
    public function body_compresses_when_size_is_over_one_kib(): void
    {
        // Approximately 1800 bytes of data
        $receiptIds = array_fill(0, 50, 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');

        $request = new GetReceiptsRequest(
            new PushReceiptIdCollection(...$receiptIds)
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
        // Approximately 180 bytes of data
        $receiptIds = array_fill(0, 5, 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');

        $request = new GetReceiptsRequest(
            new PushReceiptIdCollection(...$receiptIds)
        );

        $this->mockClient->addResponse(MockResponse::make());

        $this->connector->send($request);

        $lastPendingRequest = $this->mockClient->getLastPendingRequest();

        $this->assertNull($lastPendingRequest->headers()->get('Content-Encoding'));
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
                        'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => [
                            'status' => 'ok',
                        ],
                        'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => [
                            'status' => 'ok',
                        ],
                        'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => [
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
}