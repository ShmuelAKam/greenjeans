<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

enum API_Gender: string
{
    case Male    = 'male';
    case Female  = 'female';
    case Unknown = 'unknown';
}
