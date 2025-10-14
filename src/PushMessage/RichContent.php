<?php

namespace Dru1x\ExpoPush\PushMessage;

use Dru1x\ExpoPush\Support\ConvertsFromJson;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use JsonSerializable;

final readonly class RichContent implements JsonSerializable
{
    use ConvertsFromJson, ConvertsToJson;

    public function __construct(public string $image) {}
}
