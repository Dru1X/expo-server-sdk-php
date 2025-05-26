<?php

namespace Dru1x\ExpoPush;

use Dru1x\ExpoPush\Collections\PushErrorCollection;
use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Collections\PushReceiptCollection;
use Dru1x\ExpoPush\Collections\PushReceiptIdCollection;
use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\Data\PushMessage;
use Dru1x\ExpoPush\Exception\RequestExceptionHandler;
use Dru1x\ExpoPush\Requests\GetReceiptsRequest;
use Dru1x\ExpoPush\Requests\SendNotificationsRequest;
use Dru1x\ExpoPush\Results\GetReceiptsResult;
use Dru1x\ExpoPush\Results\SendNotificationsResult;
use Generator;
use Saloon\Exceptions\InvalidPoolItemException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Pool;
use Saloon\Http\Response;

final class ExpoPushClient extends Connector
{
    public const int MAX_CONCURRENT_REQUESTS = 6;

    public function __construct(protected readonly ?string $authToken = null) {}

    public function resolveBaseUrl(): string
    {
        return 'https://exp.host/--/api/v2/push';
    }

    // Requests ----

    /**
     * Send a set of push notifications
     *
     * This supports request concurrency, with up to 6 requests being sent at once
     *
     * @param PushMessageCollection|PushMessage[] $pushMessages
     *
     * @return SendNotificationsResult
     * @throws InvalidPoolItemException
     */
    public function sendNotifications(PushMessageCollection|array $pushMessages): SendNotificationsResult
    {
        // Ensure push messages are in a collection
        if (is_array($pushMessages)) {
            $pushMessages = new PushMessageCollection(...$pushMessages);
        }

        // Prepare ticket and error collections
        $tickets = new PushTicketCollection();
        $errors  = new PushErrorCollection();

        // Prepare a new request pool
        $pool = $this->makeRequestPool($errors);

        // Split the message collection into a set of requests and add them to the pool
        $pool->setRequests(function () use ($pushMessages): Generator {
            $chunks = $pushMessages->chunkByNotifications(SendNotificationsRequest::MAX_NOTIFICATION_COUNT);

            foreach ($chunks as $pushMessages) {
                yield new SendNotificationsRequest($pushMessages);
            }
        });

        // When a response is received...
        $pool->withResponseHandler(function (Response $response, int $requestIndex) use ($tickets): void {
            /** @var PushTicketCollection $ticketBatch */
            $ticketBatch = $response->dtoOrFail();
            $offset      = $requestIndex * SendNotificationsRequest::MAX_NOTIFICATION_COUNT;

            foreach ($ticketBatch as $index => $ticket) {
                $tickets->set($offset + $index, $ticket);
            }
        });

        // Send all the requests
        $pool->send()->wait();

        // Merge all the chunks of push tickets into a single ordered collection and return
        return new SendNotificationsResult($tickets, $errors);
    }

    /**
     * Get available push receipts with the given IDs
     *
     * This supports request concurrency, with up to 6 requests being sent at once
     *
     * @param PushReceiptIdCollection|string[] $receiptIds
     *
     * @return GetReceiptsResult
     * @throws InvalidPoolItemException
     */
    public function getReceipts(PushReceiptIdCollection|array $receiptIds): GetReceiptsResult
    {
        // Ensure receipt IDs are in a collection
        if (is_array($receiptIds)) {
            $receiptIds = new PushReceiptIdCollection(...$receiptIds);
        }

        // Prepare receipt and error collections
        $receipts = new PushReceiptCollection();
        $errors   = new PushErrorCollection();

        // Prepare a new request pool
        $pool = $this->makeRequestPool($errors);

        // Split the receipt ID collection into a set of requests and add them to the pool
        $pool->setRequests(function () use ($receiptIds): Generator {
            $chunks = $receiptIds->chunk(GetReceiptsRequest::MAX_RECEIPT_COUNT);

            foreach ($chunks as $chunk) {
                yield new GetReceiptsRequest($chunk);
            }
        });

        // When a response is received...
        $pool->withResponseHandler(function (Response $response, int $requestIndex) use ($receipts): void {
            /** @var PushReceiptCollection $receiptBatch */
            $receiptBatch = $response->dtoOrFail();
            $offset       = $requestIndex * GetReceiptsRequest::MAX_RECEIPT_COUNT;

            foreach ($receiptBatch as $index => $receipt) {
                $receipts->set($offset + $index, $receipt);
            }
        });

        // Send all the requests
        $pool->send()->wait();

        // Merge all the chunks of push receipts into a single collection and return
        return new GetReceiptsResult($receipts, $errors);
    }

    // Helpers ----

    /**
     * Get the installed version of this SDK
     *
     * @return string
     */
    public function sdkVersion(): string
    {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__) . '/composer.json')
        );

        return $composer->version ?? 'unknown';
    }

    // Internals ----

    /** @inheritDoc */
    protected function defaultHeaders(): array
    {
        return [
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent'      => "expo-server-sdk-php/{$this->sdkVersion()} (dru1x)",
        ];
    }

    /** @inheritDoc */
    protected function defaultAuth(): ?TokenAuthenticator
    {
        return $this->authToken ? new TokenAuthenticator($this->authToken) : null;
    }

    /**
     * Make a request pool with a concurrency limit and error handler
     *
     * @param PushErrorCollection $errors A collection in which to store any push errors
     *
     * @return Pool
     */
    protected function makeRequestPool(PushErrorCollection $errors): Pool
    {
        // Make a new pool with a concurrency limit and exception handler
        return $this->pool(
            concurrency: self::MAX_CONCURRENT_REQUESTS,
            exceptionHandler: new RequestExceptionHandler(
                batchSize: SendNotificationsRequest::MAX_NOTIFICATION_COUNT,
                errors: $errors),
        );
    }
}