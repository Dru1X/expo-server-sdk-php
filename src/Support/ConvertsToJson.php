<?php

namespace Dru1x\ExpoPush\Support;

trait ConvertsToJson
{
    use ConvertsToArray;

    /** @inheritDoc */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert this object to a JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }
}