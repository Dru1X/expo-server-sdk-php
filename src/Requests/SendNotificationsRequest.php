<?php

namespace Dru1x\ExpoPush\Requests;

use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\Data\PushTicket;
use Dru1x\ExpoPush\Data\PushTicketDetails;
use Dru1x\ExpoPush\Enums\PushStatus;
use Dru1x\ExpoPush\Enums\PushTicketErrorCode;
use Dru1x\ExpoPush\Traits\CompressesBody;
use InvalidArgumentException;
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

class SendNotificationsRequest extends Request implements HasBody
{
    use AcceptsJson, HasJsonBody, CompressesBody;

    public const int MAX_NOTIFICATION_COUNT = 100;
    public const int MAX_MESSAGE_DATA_BYTES = 4096;

    protected Method $method = Method::POST;

    public function __construct(protected readonly PushMessageCollection $pushMessages) {}

    public function resolveEndpoint(): string
    {
        return '/send';
    }

    public function createDtoFromResponse(Response $response): PushTicketCollection
    {
        try {
            $errors = $response->json('errors');
            $data   = $response->json('data');
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

        return new PushTicketCollection(
            ...array_map(fn(array $ticketData) => new PushTicket(
                status: PushStatus::from($ticketData['status']),
                receiptId: $ticketData['id'] ?? null,
                message: $ticketData['message'] ?? null,
                details: isset($ticketData['details']) ?
                    new PushTicketDetails(
                        error: PushTicketErrorCode::tryFrom($ticketData['details']['error'] ?? null),
                        expoPushToken: $ticketData['details']['expoPushToken'] ?? null
                    ) : null,
            ), $data)
        );
    }

    // Internals ----

    protected function defaultBody(): array
    {
        $this->preventTooManyMessages();
        $this->preventTooMuchMessageData();

        return $this->pushMessages->toArray();
    }

    /**
     * Prevent the max number of notifications per request being exceeded
     *
     * @return void
     */
    protected function preventTooManyMessages(): void
    {
        if ($this->pushMessages->notificationCount() > self::MAX_NOTIFICATION_COUNT) {
            throw new OverflowException(
                "Cannot send more than " . self::MAX_NOTIFICATION_COUNT . " notifications per request"
            );
        }
    }

    /**
     * Prevent the data field of any message being larger than the limit
     *
     * @return void
     */
    protected function preventTooMuchMessageData(): void
    {
        foreach ($this->pushMessages as $pushMessage) {

            // Calculate the size of the data field when converted to JSON
            try {
                $dataBytes = $pushMessage->data ? strlen(json_encode($pushMessage->data, JSON_THROW_ON_ERROR)) : 0;
            } catch (JsonException $e) {
                throw new InvalidArgumentException(
                    "Push message data could not be encoded as JSON: {$e->getMessage()}"
                );
            }

            if ($dataBytes > self::MAX_MESSAGE_DATA_BYTES) {
                throw new OverflowException(
                    "Individual push message data cannot be larger than " . self::MAX_MESSAGE_DATA_BYTES . " bytes"
                );
            }
        }
    }
}