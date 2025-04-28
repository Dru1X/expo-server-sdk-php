<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Traits\ConvertsToJson;
use JsonSerializable;

readonly class RichContent implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(public string $image) {}
}