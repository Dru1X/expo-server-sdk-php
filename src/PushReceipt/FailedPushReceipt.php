<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;

/**
 * @property-read array{expoPushToken?: PushToken} $details
 */
final readonly class FailedPushReceipt extends PushReceipt
{
    public function __construct(string $id, public string $message, public array $details = [])
    {
        parent::__construct($id, PushStatus::Error);
    }

}