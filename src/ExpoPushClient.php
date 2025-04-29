<?php

namespace Dru1x\ExpoPush;

use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Collections\PushReceiptCollection;
use Dru1x\ExpoPush\Collections\PushReceiptIdCollection;
use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\Data\PushMessage;
use Dru1x\ExpoPush\Requests\GetReceiptsRequest;
use Dru1x\ExpoPush\Requests\SendNotificationsRequest;
use Generator;
use Saloon\Exceptions\InvalidPoolItemException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;

class ExpoPushClient extends Connector
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
     * @return PushTicketCollection
     * @throws InvalidPoolItemException
     */
    public function sendNotifications(PushMessageCollection|array $pushMessages): PushTicketCollection
    {
        // Ensure push messages are in a collection
        if (is_array($pushMessages)) {
            $pushMessages = new PushMessageCollection(...$pushMessages);
        }

        // Make a new pool with the concurrency limit set
        $pool = $this->pool(concurrency: self::MAX_CONCURRENT_REQUESTS);

        // Split the message collection into a set of requests and add them to the pool
        $pool->setRequests(function () use ($pushMessages): Generator {
            $chunks = $pushMessages->chunkByNotifications(SendNotificationsRequest::MAX_NOTIFICATION_COUNT);

            foreach ($chunks as $pushMessages) {
                yield new SendNotificationsRequest($pushMessages);
            }
        });

        // Keep chunks in request order
        $pushTicketChunks = [];

        // When a response is received, extract the push ticket collection and add it to the chunk list
        $pool->withResponseHandler(function (Response $response, int $key) use (&$pushTicketChunks): void {
            $pushTicketChunks[$key] = $response->dtoOrFail();
        });

        // Send all the requests
        $pool->send()->wait();

        // Merge all the chunks of push tickets into a single ordered collection and return
        return new PushTicketCollection()->merge(...$pushTicketChunks);
    }

    /**
     * Get available push receipts with the given IDs
     *
     * @param PushReceiptIdCollection|string[] $receiptIds
     *
     * @return PushReceiptCollection
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function getReceipts(PushReceiptIdCollection|array $receiptIds): PushReceiptCollection
    {
        // Ensure receipt IDs are in a collection
        if (is_array($receiptIds)) {
            $receiptIds = new PushReceiptIdCollection(...$receiptIds);
        }

        // Prepare and send the request
        $response = $this->send(
            new GetReceiptsRequest($receiptIds)
        );

        // Convert the response to a push receipt collection and return
        return $response->dtoOrFail();
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
}