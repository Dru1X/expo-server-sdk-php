<?php

namespace Data;

use Dru1x\ExpoPush\Data\PushToken;
use Dru1x\ExpoPush\Data\SuccessfulPushReceipt;
use Dru1x\ExpoPush\Data\SuccessfulPushTicket;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SuccessfulPushReceiptTest extends TestCase
{
    #[Test]
    public function json_encode_returns_value(): void
    {
        $receipt = new SuccessfulPushReceipt(
            id:'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );

        $expectedJson = <<<JSON
{
  "status": "ok", 
  "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
}
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($receipt));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $receipt = new SuccessfulPushReceipt(
            id:'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );

        $expectedJson = <<<JSON
{
  "status": "ok", 
  "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
}
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $receipt->toJson());
    }
}