<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Enums\PushStatus;

final readonly class SuccessfulPushReceipt extends PushReceipt
{
    public function __construct(string $id)
    {
        parent::__construct($id, PushStatus::Ok);
    }
}