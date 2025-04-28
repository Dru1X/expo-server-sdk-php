<?php

namespace Dru1x\ExpoPush\Tests\Unit\Data;

use Dru1x\ExpoPush\Data\RichContent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RichContentTest extends TestCase
{
    #[Test]
    public function json_encode_returns_value(): void
    {
        $richContent = new RichContent('https://file.cloud/abc-123');

        $this->assertJsonStringEqualsJsonString('{"image": "https://file.cloud/abc-123"}', json_encode($richContent));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $richContent = new RichContent('https://file.cloud/abc-123');

        $this->assertJsonStringEqualsJsonString('{"image": "https://file.cloud/abc-123"}', $richContent->toJson());
    }
}
