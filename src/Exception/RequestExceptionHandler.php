<?php

namespace Dru1x\ExpoPush\Exception;

use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use JsonException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\RateLimitPlugin\Exceptions\RateLimitReachedException;

final class RequestExceptionHandler
{
    public function __construct(protected int $batchSize, protected PushErrorCollection $errors) {}

    /**
     * @throws JsonException
     */
    public function __invoke(FatalRequestException|RequestException|RateLimitReachedException $exception, int $requestIndex): void
    {
        $startIndex = $requestIndex * $this->batchSize;
        $endIndex   = $startIndex + $this->batchSize - 1;

        // The request completely failed
        if ($exception instanceof FatalRequestException) {
            $this->errors->add(new PushError(
                code: PushErrorCode::Failed,
                message: $exception->getMessage(),
                startIndex: $startIndex,
                endIndex: $endIndex,
            ));
            return;
        }

        // A rate limit was reached
        if($exception instanceof RateLimitReachedException){
            $this->errors->add(new PushError(
                code: PushErrorCode::TooManyRequests,
                message: $exception->getMessage(),
                startIndex: $startIndex,
                endIndex: $endIndex,
            ));
            return;
        }

        // The request itself was successful, but the response contained errors
        $response = $exception->getResponse();

        foreach ($response->json('errors') as $responseError) {

            $responseErrorCode = $responseError['code'] ?? null;

            $this->errors->add(new PushError(
                code: PushErrorCode::tryFrom($responseErrorCode) ?? PushErrorCode::Unknown,
                message: $responseError['message'] ?? 'Unknown error',
                details: $responseError['details'] ?? null,
                startIndex: $startIndex,
                endIndex: $endIndex,
            ));
        }
    }
}