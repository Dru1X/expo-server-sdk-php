<?php

namespace Dru1x\ExpoPush\Tests\Unit\Results;

use Dru1x\ExpoPush\Collections\PushReceiptCollection;
use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\Results\GetReceiptsResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetReceiptsResultTest extends TestCase
{
    #[Test]
    public function has_errors_returns_true_when_errors_are_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(),
            errors: new PushErrorCollection(
                new PushError(
                    code: PushErrorCode::Failed,
                    message: 'Failed to get push receipts'
                )
            ),
        );

        $this->assertTrue($result->hasErrors());
    }

    #[Test]
    public function has_errors_returns_false_when_error_collection_is_empty(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasErrors());
    }

    #[Test]
    public function has_errors_returns_false_when_error_collection_is_missing(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(),
            errors: null
        );

        $this->assertFalse($result->hasErrors());
    }
}