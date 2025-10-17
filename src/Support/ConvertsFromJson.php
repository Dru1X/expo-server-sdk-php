<?php

namespace Dru1x\ExpoPush\Support;

trait ConvertsFromJson
{
    use ConvertsFromArray;

    /**
     * Create an object from a JSON string
     */
    public static function fromJson(string $json): static
    {
        return static::fromArray(
            static::jsonDecode($json)
        );
    }

    public static function jsonDecode(string $json): mixed
    {
        return json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
    }
}
