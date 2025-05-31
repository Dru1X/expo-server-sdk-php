<?php

namespace Dru1x\ExpoPush\PushTicket;

enum PushTicketErrorCode: string
{
    case Unknown = 'Unknown';
    case DeviceNotRegistered = 'DeviceNotRegistered';
}
