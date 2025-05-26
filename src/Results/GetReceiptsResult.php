<?php

namespace Dru1x\ExpoPush\Results;

use Dru1x\ExpoPush\Collections\PushErrorCollection;
use Dru1x\ExpoPush\Collections\PushReceiptCollection;

final readonly class GetReceiptsResult extends Result
{
    public function __construct(public PushReceiptCollection $receipts, ?PushErrorCollection $errors = null)
    {
        parent::__construct($errors);
    }
}