<?php

namespace Dru1x\ExpoPush\Request;

use Dru1x\ExpoPush\PushReceipt\FailedPushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptIdCollection;
use Dru1x\ExpoPush\PushReceipt\SuccessfulPushReceipt;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;
use JsonException;
use OverflowException;
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

    // DTO ----

    /**
     * @inheritDoc
     *
     * @param Response $response
     *
     * @return PushReceiptCollection
     * @throws UnexpectedValueException If the response body could not be decoded
     */
    public function createDtoFromResponse(Response $response): PushReceiptCollection
    {
        try {
            $data = $response->json('data');
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                message: "Response could not be decoded: {$exception->getMessage()}",
                previous: $exception
            );
        }

        $receipts = new PushReceiptCollection();

        foreach ($data as $id => $receiptData) {

            // Parse the receipt status
            $status = PushStatus::from($receiptData['status']);

            $receipts->add(match ($status) {
                PushStatus::Ok    => $this->makeSuccessfulPushReceipt($id),
                PushStatus::Error => $this->makeFailedPushReceipt($id, $receiptData),
            });
        }

        return $receipts;
    }

    /**
     * Make a SuccessfulPushReceipt from the given receipt ID
     *
     * @param string $id
     *
     * @return SuccessfulPushReceipt
     */
    protected function makeSuccessfulPushReceipt(string $id): SuccessfulPushReceipt
    {
        return new SuccessfulPushReceipt(
            id: $id,
        );
    }

    /**
     * Make a FailedPushReceipt from the given data
     *
     * @param string                                 $id
     * @param array{message: string, details: array} $data
     *
     * @return FailedPushReceipt
     */
    protected function makeFailedPushReceipt(string $id, array $data): FailedPushReceipt
    {
        $detailsToken = $data['details']['expoPushToken'] ?? null;

        if ($detailsToken) {
            $data['details']['expoPushToken'] = new PushToken($detailsToken);
        }

        return new FailedPushReceipt(
            id: $id,
            message: $data['message'],
            details: $data['details'],
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