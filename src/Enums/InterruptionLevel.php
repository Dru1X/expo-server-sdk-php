<?php

namespace Dru1x\ExpoPush\Enums;

enum InterruptionLevel: string
{
    case Active = 'active';
    case Critical = 'critical';
    case Passive = 'passive';
    case TimeSensitive = 'time-sensitive';
}