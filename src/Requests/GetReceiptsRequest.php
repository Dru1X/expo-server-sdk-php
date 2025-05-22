<?php

namespace Dru1x\ExpoPush\Requests;

use Dru1x\ExpoPush\Collections\PushReceiptCollection;
use Dru1x\ExpoPush\Collections\PushReceiptIdCollection;
use Dru1x\ExpoPush\Data\PushReceipt;
use Dru1x\ExpoPush\Enums\PushStatus;
use Dru1x\ExpoPush\Traits\CompressesBody;
use JsonException;
use OverflowException;
use RuntimeException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;
use UnexpectedValueException;

final class GetReceiptsRequest extends Request implements HasBody
{
    use AcceptsJson, HasJsonBody, CompressesBody;

    public const int MAX_RECEIPT_COUNT = 1000;

    protected Method $method = Method::POST;

    public function __construct(protected readonly PushReceiptIdCollection $pushReceiptIds) {}

    public function resolveEndpoint(): string
    {
        return '/getReceipts';
    }

    public function createDtoFromResponse(Response $response): PushReceiptCollection
    {
        try {
            $data   = $response->json('data');
            $errors = $response->json('errors');
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                message: "Response could not be decoded: {$exception->getMessage()}",
                previous: $exception
            );
        }

        if (!empty($errors)) {
            throw new RuntimeException(
                message: "Request failed: " . $errors[0]['message'] ?? 'Unknown error'
            );
        }

        return new PushReceiptCollection(
            ...array_map(fn(array $receiptData, string $id) => new PushReceipt(
                id: $id,
                status: PushStatus::from($receiptData['status']),
                message: $receiptData['message'] ?? '',
                details: $receiptData['details'] ?? [],
            ), $data, array_keys($data))
        );
    }

    // Internals ----

    protected function defaultBody(): array
    {
        $this->preventTooManyReceipts();

        return [
            'ids' => $this->pushReceiptIds->toArray(),
        ];
    }

    /**
     * Prevent the max number of receipts per request being exceeded
     *
     * @return void
     */
    protected function preventTooManyReceipts(): void
    {
        if ($this->pushReceiptIds->count() > self::MAX_RECEIPT_COUNT) {
            throw new OverflowException(
                "Cannot send more than " . self::MAX_RECEIPT_COUNT . " push receipt IDs per request"
            );
        }
    }
}