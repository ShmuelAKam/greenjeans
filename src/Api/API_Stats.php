<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * API_Stats - This is a general "object" that returns statistics about Geni and the WFT
 * (World Family Tree, AKA "Big Tree") on Geni. As such it does NOT contain any data.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=stats&version=1 API_Stats}
 */
class API_Stats extends API_Base
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get general statistics. Currently this returns 'name' and 'url' values.
     * The URL points to some JSON which contains the size of the WFT. Basically
     * this function is useless. Use getWFTStats instead. It returns the values
     * directly.
     * @return array<string, array <string, string>> Example:
     *
     *  [
     *      'stats' =>
     *      [
     *          'name' => "World Family Tree",
     *          'url'  => "https://www.geni.com/api/stats/world-family-tree",
     *      ]
     *  ]
     *
     */
    public function getStats(): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        $statsURL = "/stats";

        // -------------------------------
        $statsReply = $API_Wrap->callAPI($statsURL);
        // -------------------------------

        $statsArray = reset($statsReply ['stats']);

        return $statsArray;
    }

    /**
     * Get statistics about thw WFT. Currently this includes only the size,
     * in formatted and integer formats. Like this:
     * [
     *    'formatted_size' => "208,981,741",
     *    'size'           => 208981741
     * ]
     *
     * @return array
     * @return array<string, string|int> Example:
     */
    public function getWFTStats(): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        $statsURL = "/stats/world-family-tree";

        // -------------------------------
        $statsArray = $API_Wrap->callAPI($statsURL);
        // -------------------------------

        return $statsArray;
    }
}
