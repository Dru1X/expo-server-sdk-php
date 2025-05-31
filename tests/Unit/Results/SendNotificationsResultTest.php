<?php

namespace Dru1x\ExpoPush\Tests\Unit\Results;

use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\Results\SendNotificationsResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SendNotificationsResultTest extends TestCase
{
    #[Test]
    public function has_errors_returns_true_when_errors_are_present(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(),
            errors: new PushErrorCollection(
                new PushError(
                    code: PushErrorCode::Failed,
                    message: 'Failed to send push notification'
                )
            ),
        );

        $this->assertTrue($result->hasErrors());
    }

    #[Test]
    public function has_errors_returns_false_when_error_collection_is_empty(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasErrors());
    }

    #[Test]
    public function has_errors_returns_false_when_error_collection_is_missing(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(),
            errors: null
        );

        $this->assertFalse($result->hasErrors());
    }
}