<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

/**
 * List of *strings* as returned from the server. E.g. the list returned from
 * /user/labels
 * @see API_ListBase
 * @extends API_ListBase<string>
 */
class API_ListStrings extends API_ListBase
{
    /**
     * Constructor.
     * @param string $listName  Fixed part of URL to request the list from server.
     * @param bool   $getAll    When set, reads the entire list when created.
     * @param int    $pageSize  Number of elements to request to read each time.
     * @param bool   $fromAPICall  When set, list was created from a specific API call (see $listName).
     */
    public function __construct(
        string  $listName,
        bool    $getAll = false,
        bool    $fromAPICall = true,
        int     $pageSize = API_ListBase::SIZE_OF_BATCH,
    ) {
        parent::__construct($listName, '', $getAll, IDsOnly: false, fromAPICall: $fromAPICall, pageSize: $pageSize);
    }

    //------------------------------------------------------

    /**
     * Factory method: Create instance from array
     * Called from the updateFromArray processes of objects that contain lists.
     * NO need to parse. Passed specific field.
     * @param string         $listName
     * @param array <string> $arrayValues
     */
    public static function newFromArray(array $arrayValues, string $listName): API_ListStrings
    {
        $list = new API_ListStrings(listName: $listName, getAll: true, fromAPICall: false);

        $list->setList($list->parseValues($arrayValues));

        return $list;
    }

    /**
     * Factory method: Create instance from array
     * Called from the updateFromArray processes of objects that contain lists.
     * NO need to parse. Passed specific field.
     * @param array <string> $arrayValues
     */
    public static function newFromDB(array $arrayValues): API_ListStrings
    {
        $list = new API_ListStrings(listName: '', getAll: true, fromAPICall: false);

        $list->setList($list->parseArray($arrayValues));

        $list->setPageCount(count($arrayValues));
        $list->setTotalCount(count($arrayValues));
        $list->setLastPageNum(1);
        $list->pageNum = 1;

        return $list;
    }

    //------------------------------------------------------

    /**
     * This is a dummy function, because string arrays do NOT need any processing.
     * @param  array<string>  $listOnly
     * @return array<string, string>  input array.
     */
    protected function parseArray(array $listOnly): array
    {
        // No processing needed on list of string values.

        return $listOnly;
    }
}
