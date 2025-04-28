<?php

namespace Dru1x\ExpoPush;

use Dru1x\ExpoPush\Collections\PushMessageCollection;
use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\Data\PushMessage;
use Dru1x\ExpoPush\Requests\SendNotificationsRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;

class ExpoPushClient extends Connector
{
    public function __construct(protected readonly ?string $authToken = null) {}

    public function resolveBaseUrl(): string
    {
        return 'https://exp.host/--/api/v2/push';
    }

    // Requests ----

    /**
     * Send a set of push notifications
     *
     * @param PushMessageCollection|PushMessage[] $pushMessages
     *
     * @return PushTicketCollection
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function sendNotifications(PushMessageCollection|array $pushMessages): PushTicketCollection
    {
        if (is_array($pushMessages)) {
            $pushMessages = new PushMessageCollection(...$pushMessages);
        }

        // TODO: Automatically chunk the push messages and send them in concurrent requests
        $response = $this->send(
            new SendNotificationsRequest($pushMessages)
        );

        return $response->dtoOrFail();
    }

    // Internals ----

    protected function defaultHeaders(): array
    {
        return [
            'Accept-Encoding' => 'gzip, deflate',
        ];
    }

    protected function defaultAuth(): ?TokenAuthenticator
    {
        return $this->authToken ? new TokenAuthenticator($this->authToken) : null;
    }
}