<?php

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\Support\Result;

final readonly class GetReceiptsResult extends Result
{
    public function __construct(public PushReceiptCollection $receipts, PushErrorCollection $errors)
    {
        parent::__construct($errors);
    }

    public function hasReceipts(): bool
    {
        return $this->receipts->count() > 0;
    }

    public function hasSuccessfulReceipts(): bool
    {
        $successfulReceipts = $this->receipts->filter(fn(PushReceipt $receipt) => $receipt->isSuccessful());

        return $successfulReceipts->count() > 0;
    }

    public function hasFailedReceipts(): bool
    {
        $successfulReceipts = $this->receipts->filter(fn(PushReceipt $receipt) => $receipt->isFailed());

        return $successfulReceipts->count() > 0;
    }
}