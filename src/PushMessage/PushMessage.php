<?php

namespace Dru1x\ExpoPush\PushMessage;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use Dru1x\ExpoPush\Support\ConvertsFromArray;
use Dru1x\ExpoPush\Support\ConvertsFromJson;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use JsonSerializable;

final readonly class PushMessage implements JsonSerializable
{
    use ConvertsFromJson, ConvertsToJson;

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
    public function copy(PushTokenCollection|PushToken|null $to): self
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

    /**
     * Create an object from a JSON string
     */
    public static function fromJson(string $json): self
    {
        $array = json_decode($json, true);

        // TODO: there's got to be a better way...
        if(is_array($array['to'])) {
            $tokens = array_map(
                fn(string $token) => PushToken::fromJson($token),
                $array['to'],
            );

            $array['to'] = new PushTokenCollection(...$tokens);
        } else {
            $array['to'] = PushToken::fromJson($array['to']);
        }

        return self::fromArray($array);
    }
}
