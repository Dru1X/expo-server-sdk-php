<?php

namespace Dru1x\ExpoPush\Tests\Unit\Data;

use Dru1x\ExpoPush\Data\PushToken;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PushTokenTest extends TestCase
{
    #[Test]
    public function invalid_value_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PushToken('NOT A TOKEN');
    }

    #[Test]
    public function casts_to_string(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', (string)$token);
    }

    #[Test]
    public function to_string_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $token->toString());
    }

    #[Test]
    public function json_encode_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertJsonStringEqualsJsonString('"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"', json_encode($token));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertJsonStringEqualsJsonString('"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"', $token->toJson());
    }
}
