<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

/**
 * List of objects as returned from the server.
 * For example the list returned from * /profile/documents will contain API_Document objects.
 * If the object has an ID value, this will be used as a KEY for the lists/array. If the $IDsOnly flag is
 * set on creation, instead of the object, the list value will TRUE.

 * @see API_ListBase
 * @extends API_ListBase <bool|object>
 */
class API_ListObjects extends API_ListBase
{
    /**
     * Constructor.
     * @param string $listName  Fixed part of URL to request the list from server.
     * @param string $className Type of object this list contains.
     * @param bool   $getAll    When set, reads the entire list when created.
     * @param bool   $IDsOnly   When set list will contain only IDs and GUID values.
     * @param int    $pageSize  Number of elements to request to read each time.
     * @param bool   $fromAPICall  When set, list was created from a specific API call (see $listName).
     */
    public function __construct(
        ?string $listName,
        string  $className,
        bool    $getAll = false,
        bool    $IDsOnly = false,
        bool    $fromAPICall = true,
        int     $pageSize = API_ListBase::SIZE_OF_BATCH,
    ) {
        parent::__construct($listName, $className, $getAll, $IDsOnly, $fromAPICall, $pageSize);
    }

    //------------------------------------------------------

    // There is NO updateFromArray function, because these objects are never
    // updated. The data itself is read-only.

    /**
     * Factory method: Create instance from array
     * Called from the updateFromArray processes of objects that contain lists.
     * @param  array  <string, string|array <string, mixed>> $arrayValues
     * @param string $listClass
     * @param string $listName
     * @param bool   $IDsOnly
     * @return self
     */
    public static function newFromArray(array $arrayValues, string $listClass, string $listName, bool $IDsOnly = false): self
    {
        $list = new API_ListObjects($listName, $listClass, getAll: true, IDsOnly: $IDsOnly, fromAPICall: false);

        $parsedReply = $list->parseValues($arrayValues);

        $list->setList($parsedReply);

        return $list;
    }

    /**
     * Factory method: Create instance from array
     * Called from the updateFromArray processes of objects that contain lists.
     * @param array <string, string|mixed> $arrayValues
     * @param string $listClass
     * @param string $listName
     * @return self
     */
    public static function newFromDB(array $arrayValues, string $listClass, string $listName): self
    {
        $first       = reset($arrayValues);
        $objectArray = is_a($first, $listClass);

        $list = new API_ListObjects($listName, $listClass, getAll: true, IDsOnly: !$objectArray, fromAPICall: false);

        $parsed = $list->parseArray($arrayValues);

        $list->setList($parsed);
        $list->setPageCount(count($arrayValues));
        $list->setTotalCount(count($arrayValues));
        $list->setLastPageNum(1);
        $list->pageNum = 1;

        return $list;
    }

    /**
     * Convert array into parsed list of IDs or objects.
     * There are three kinds of input arrays:
     *      1. Array of IDs (strings).
     *      2. Array of arrays to make objects from.
     *      3. Array of objects.
     * IF a the object has an ID, then this is used as the array's key.
     * @param  array <string, mixed> $listOnly
     * @return array <string, bool|mixed> reorganized array.
     */
    protected function parseArray(array $listOnly): array
    {
        if (count($listOnly) == 0)
        {
            return [];
        }

        $first      = reset($listOnly);
        $hasIDField = method_exists($this->getClassName(), 'getID');
        $class      = $this->getClassName();

        // There are two types of lists: IDs or objects.
        // There are three kinds of input arrays:
        //    1. Array of IDs (strings).
        //    2. Array of arrays to make objects from.
        //    3. Array of objects.

        $parsedArray = [];

        if ($hasIDField)
        {
            foreach ($listOnly as $entry)
            {
                if (is_a($first, $class))
                {
                    $entryKey = $entry->getID();
                }
                elseif (is_array($first))
                {
                    $entryKey = $entry ['id'];
                }
                else
                {
                    $entryKey = $entry;
                }

                if ($this->isIDsOnly())
                {
                    $parsedArray [$entryKey] = true;
                }
                else
                {
                    // List of objects.

                    if (is_a($first, $this->getClassName()))
                    {
                        $parsedArray [$entryKey] = $entry;
                    }
                    elseif (is_array($first))
                    {
                        // Create object from array.
                        $parsedArray [$entryKey] = $this->getClassName()::newFromArray($entry);
                    }
                }
            }
        }
        else
        {
            // Object does NOT have ID field (e.g. API_Comment).
            // Because there is no key, only an array of objects is possible.

            foreach ($listOnly as $entry)
            {
                if (is_a($first, $class))
                {
                    $parsedArray [] = $entry;
                }
                elseif (is_array($first))
                {
                    $parsedArray [] = $this->getClassName()::newFromArray($entry);
                }
            }
        }

        return $parsedArray;
    }
}
