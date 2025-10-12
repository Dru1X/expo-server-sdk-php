<?php

namespace Dru1x\ExpoPush\Support;

trait ConvertsFromJson
{
    use ConvertsFromArray;

    /**
     * Create an object from a JSON string
     */
    public static function fromJson(string $json): self
    {
        $array = json_decode($json, true);

        return self::fromArray($array);
    }
}
