<?php

namespace Dru1x\ExpoPush\Enums;

enum PushTicketErrorCode: string
{
    case Unknown = 'Unknown';
    case DeviceNotRegistered = 'DeviceNotRegistered';
}
