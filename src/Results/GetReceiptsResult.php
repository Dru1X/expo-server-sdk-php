<?php

namespace Dru1x\ExpoPush\Results;

use Dru1x\ExpoPush\Collections\PushReceiptCollection;
use Dru1x\ExpoPush\PushError\PushErrorCollection;

final readonly class GetReceiptsResult extends Result
{
    public function __construct(public PushReceiptCollection $receipts, ?PushErrorCollection $errors = null)
    {
        parent::__construct($errors);
    }
}