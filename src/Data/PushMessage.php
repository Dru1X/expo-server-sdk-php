<?php

namespace Dru1x\ExpoPush\Data;

use Dru1x\ExpoPush\Collections\PushTokenCollection;
use Dru1x\ExpoPush\Enums\InterruptionLevel;
use Dru1x\ExpoPush\Enums\Priority;
use Dru1x\ExpoPush\Traits\ConvertsToJson;
use JsonSerializable;

readonly class PushMessage implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public PushTokenCollection|PushToken $to,
        public ?string                       $title = null,
        public ?string                       $subtitle = null,
        public ?string                       $body = null,
        public ?int                          $ttl = null,
        public ?array                        $data = null,
        public ?int                          $expiration = null,
        public ?Priority                     $priority = null,
        public ?string                       $sound = null,
        public ?int                          $badge = null,
        public ?InterruptionLevel            $interruptionLevel = null,
        public ?string                       $channelId = null,
        public ?string                       $icon = null,
        public ?RichContent                  $richContent = null,
        public ?string                       $categoryId = null,
        public bool                          $mutableContent = false,
        public bool                          $_contentAvailable = false,
    ) {}

    // Internals ----

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return array_filter(
            get_object_vars($this)
        );
    }
}