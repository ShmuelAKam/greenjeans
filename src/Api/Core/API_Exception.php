<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

use Exception;

class API_Exception extends Exception
{
    /**
     * API_Wrapper:
     * HTTP Error 429. Too many API calls in time period. Server throttling.
     */
    public const THROTTLE = 1;

    /**
     * API_Wrapper:
     * HTTP Error 502.
     */
    public const GATEWAY_ERROR = 2;

    /**
     * API_Wrapper:
     * HTTP Error 500.
     */
    public const INTERNAL_ERROR = 3;

    /**
     * API_Date.
     * Bad number of days in month, or bad month, missing year. values
     */
    public const BAD_DATE = 4;

    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }
}
