<?php

namespace Dru1x\ExpoPush\PushMessage;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use Dru1x\ExpoPush\Traits\ConvertsToJson;
use JsonSerializable;

final readonly class PushMessage implements JsonSerializable
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

    // Helpers ----

    /**
     * Make a copy of this PushMessage, optionally overriding the recipients
     *
     * @param PushTokenCollection|PushToken|null $to
     *
     * @return self
     */
    public function copy(PushTokenCollection|PushToken|null $to): static
    {
        return new PushMessage(
            to: $to ?? $this->to,
            title: $this->title,
            subtitle: $this->subtitle,
            body: $this->body,
            ttl: $this->ttl,
            data: $this->data,
            expiration: $this->expiration,
            priority: $this->priority,
            sound: $this->sound,
            badge: $this->badge,
            interruptionLevel: $this->interruptionLevel,
            channelId: $this->channelId,
            icon: $this->icon,
            richContent: $this->richContent,
            categoryId: $this->categoryId,
            mutableContent: $this->mutableContent,
            _contentAvailable: $this->_contentAvailable,
        );
    }

    // Internals ----

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return array_filter(
            get_object_vars($this)
        );
    }
}