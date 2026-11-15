<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

//require_once '../../Test/Debug_OUT.php';

use ArrayAccess;
use Countable;
use IteratorAggregate;

use function ShmuelAKam\Greenjeans\Tests\Api\boolToString;

/**
 * Base class for all types of "lists" sent from Server. The lists are sent in
 * the format of pages. ALL of the logic for list classes is HERE, except for
 * the function parseArray. This does the class specific process of parsing the
 * part of the Server reply that contains the actual values (without the "wrapping").
 *
 * Internally the list is handled by a simple array. See {@link API_ListBase::list}
 * This contains the current page OR the entire list, depending on $getAll.
 * When set, the entire list is read when the object is created. When not, only
 * the first page is loaded, and the user can page through the list using the
 * next & previous functions.
 *
 * These are read-only objects. So they don't have the updateFromArray function.
 * TODO Create unit-test classes for the four list classes.
 *
 * @template TValueType
 * @implements \ArrayAccess<int|string, TValueType>
 * @implements \IteratorAggregate<int|string, TValueType>
 */
abstract class API_ListBase implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var integer Default size of page to read. Can be set higher when
     *              list contains IDs or strings.
     */
    public const SIZE_OF_BATCH = 50;

    /**
     * @var array<TValueType> of the objects/string/etc of the current page (or the
     *            entire list when API_ListBase::getAll is set.
     */
    private array  $listArray;

    /**
     * @var int Number of items in current PAGE.
     */
    private int    $pageCount;

    /**
     * @var int Total number of items in entire LIST.
     */
    private int    $totalCount;

    /**
     * @var int Current page, starts at 1 (ignored when API_ListBase::getAll is set).
     */
    protected int    $pageNum;

    /**
     * @var int Max. number of pages. Starts at 1 (ignored when API_ListBase::getAll is set).
     */
    private int    $lastPageNum;

    /**
     * @var int Number of items in each page.
     */
    private int    $pageSize;

    /**
     * @var string Beginning of URL string for the API call.
     */
    private string $listName;

    /**
     * @var bool Read entire list when created.
     */
    private bool   $getAll;

    /**
     * Determined when object constructed. When set list will contain only IDs
     * and GUID values. When FALSE list contains full API objects. Will ALWAYS
     * be FALSE for derived classes API_ListObjects, and API_ListStrings.
     * @var bool
     */
    private bool   $IDsOnly;

    /**
     * When set, list was created from a specific API call (see $listName). When
     * not, contains name of class and sub-object it was created from (debug value).
     * @var bool
     */
    private bool   $fromAPICall;

    /**
     * @var string "::class" of the objects in list. Used to create them.
     *             For API_ListStrings,  is equal ''.
     *             For API_ListProfiles, is equal 'API_Profile'.
     */
    protected string $className;

    //------------------------------------------------------

    /**
     * Constructor.
     * @param string $listName  Fixed part of URL to request the list from server.
     * @param string $className Type of object this list contains.
     * @param bool   $getAll    When set, reads the entire list when created.
     * @param int    $pageSize  Number of elements to request to read each time.
     * @param bool   $fromAPICall  When set, list was created from a specific API call (see $listName).
     */
    protected function __construct(
        ?string $listName,
        string  $className,
        bool    $getAll,
        bool    $IDsOnly,
        bool    $fromAPICall,
        int     $pageSize,
    ) {
        $this->listArray   = [];
        $this->pageCount   = 0;
        $this->totalCount  = 0;
        $this->pageNum     = 1;
        $this->lastPageNum = -1;

        $this->listName  = $listName;
        $this->className = '\\' . $className;

        $this->pageSize    = $pageSize;
        $this->getAll      = $getAll;
        $this->IDsOnly     = $IDsOnly;
        $this->fromAPICall = $fromAPICall;

        // If NOT $fromAPICall, values will be set by newFromArray in
        // derived classes.

        if ($fromAPICall)
        {
            if ($getAll)
            {
                $this->listArray = $this->getAllPages();
            }
            else
            {
                $this->listArray = $this->getPage();
            }
        }
    }

    //------------------------------------------------------
    // ArrayAccess, Countable and IteratorAggregate interfaces.
    // Makes these lists behave like an array.

    public function offsetExists($offset): bool
    {
        return isset($this->listArray[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->listArray[$offset]);
    }

    public function offsetSet($offset, $value): void {} // The list is read only!

    public function offsetGet($offset): mixed
    {
        return isset($this->listArray[$offset]) ? $this->listArray[$offset] : null;
    }

    public function count(): int
    {
        return count($this->listArray);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->listArray);
    }

    //------------------------------------------------------
    // Getters and Setters.

    /** @return array<TValueType> */
    public function getList(): array
    {
        return $this->listArray;
    }

    public function getListName(): string
    {
        return $this->listName;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function getPageNum(): int
    {
        return $this->pageNum;
    }

    public function getLastPageNum(): int
    {
        return $this->lastPageNum;
    }

    public function getPageMaxSize(): int
    {
        return $this->pageSize;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * Return ::class name of objects in list. Used to create objects for list.
     *             For API_ListStrings,  is equal ''.
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function isEmpty(): bool
    {
        return ($this->totalCount == 0);
    }

    public function isGetAll(): bool
    {
        return  $this->getAll;
    }

    public function isIDsOnly(): bool
    {
        return  $this->IDsOnly;
    }

    public function isFromAPICall(): bool
    {
        return  $this->fromAPICall;
    }

    protected function setIDsOnly(bool  $only): void
    {
        $this->IDsOnly = $only;
    }

    /** @param array<TValueType> $list */
    protected function setList(array $list): void
    {
        $this->listArray = $list;
    }

    protected function setLastPageNum(int  $pageNum): void
    {
        $this->lastPageNum = $pageNum;
    }

    protected function setPageCount(int  $pageCount): void
    {
        $this->pageCount = $pageCount;
    }

    protected function setTotalCount(int  $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    //------------------------------------------------------

    /**
     * Return array of the IDs of all entries in the list.
     * @return array <string>
     */
    public function getListIDs(): array
    {
        $listIDs = [];

        // API_ListStrings do NOT have a class-name.

        $className = $this->getClassName();

        if ($this->isIDsOnly() || ($className != '' && method_exists($className, 'getID')))
        {
            // Array with ID as key (and object or TRUE as value.
            $listIDs = array_keys($this->getList());
        }
        else
        {
            $listIDs = $this->getList();
        }

        return $listIDs;
    }

    //------------------------------------------------------

    /**
     * Create URL to request current page from Server.
     * @return string
     */
    private function makePageURL(): string
    {
        $beginURL = $this->getListName();

        if (!strpos($beginURL, '?'))
        {
            $prefix = '?';
        }
        else
        {
            $prefix = '&';
        }

        $fieldsValue = '';
        if ($this->isIDsOnly())
        {
            $fieldsValue = 'fields=id&';
        }

        $pageURL = $beginURL . $prefix
            . $fieldsValue
            . 'page=' . $this->getPageNum()
            . '&per_page=' . $this->getPageMaxSize();

        return $pageURL;
    }

    /**
     * Get and process current page of list from Server.
     * @return array <string, string |array <string, mixed>>
     *      Processed page values Has varying formats in the derived
     *      classes. Returns [] when empty or faulty.
     */
    private function getPage(): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        $getList_URL = $this->makePageURL();

        // -------------------------------
        $pageReply = $API_Wrap->callAPI($getList_URL);
        // -------------------------------

        $pageArray = $this->parseValues($pageReply);

        return $pageArray;
    }

    /**
     * Get and process entire list from Server.
     * Works by getting each page and processing them together.
     * @return array <string, string |array <string, mixed>>
     *      Processed page values Has varying formats in the derived
     *      classes. Returns [] when empty or faulty.
     */
    private function getAllPages(): array
    {
        $objectList   = [];
        $profilesLeft = 0;

        do
        {
            $pageObjects = $this->getPage();

            // Page just processed was empty? Exit loop.
            if (empty($pageObjects))
            {
                break;
            }

            if ($this->getPageNum() == 1)
            {
                $profilesLeft = $this->getTotalCount();
            }

            $itemsInPage = $this->getPageCount();

            if ($itemsInPage > 0)
            {
                $objectList = array_merge($objectList, $pageObjects);

                // Set for next loop.
                $profilesLeft -= $itemsInPage;
            }

            if ($itemsInPage < $this->getPageMaxSize())
            {
                // Not a full page (possibly empty).
                break;
            }

            $this->pageNum++;
        }
        while ($profilesLeft > 0);

        $this->setPageCount($this->getTotalCount());
        $this->setLastPageNum(1);
        $this->pageNum = 1; // Is really ignored when get-all.

        return $objectList;
    }

    /**
    * Process Server values: Parse global parameters of list, and process
    * array from Server (a 2D array), into an array with the ID as key,
    * and the GUID or object as the value.
    * @param  array  <string, string|array <string, mixed>> $arrayValues
    *                The entire reply from the server. In some cases this
    *                array does NOT have a "wrapping" 'results' field.
    * @return array <int|string, TValueType> reorganized array (or empty array if none or if faulty).
    */
    protected function parseValues(array $arrayValues): array
    {
        //API_ListBase::logList ($this, $arrayValues);

        $pageArray = [];

        if ($arrayValues != null)
        {
            // Isolate array of actual values.

            if (array_key_exists('results', $arrayValues))
            {
                // Each entry in this array is an ARRAY of the object parameters.
                $pageArray = $arrayValues ['results'];
            }
            else
            {
                // When the list is part of a larger object, or is being
                // created from an array (from the Server), the array won't
                // have a 'results' field to "wrap" it.

                // When there are multiple entries they will returned as an
                // array, and nothing needs to be done. When there is only
                // ONE entry, we need to make it into an array.

                if (array_key_first($arrayValues) == '0')
                {
                    $pageArray = $arrayValues;
                }
                else
                {
                    $pageArray = [ $arrayValues ];
                }
            }

            // Set number of entries in THIS current page.

            $this->setPageCount(count($pageArray));

            // When reading first page, set TOTAL number of entries.

            if ($this->getPageNum() == 1)
            {
                if (isset($arrayValues['total_count']) && is_numeric($arrayValues['total_count']))
                {
                    // First page of multiple page list.

                    $totalCount = (int) $arrayValues ['total_count'];
                    $pageCount  = (int) ceil($totalCount / self::SIZE_OF_BATCH);

                    $this->setTotalCount($totalCount);
                    $this->setLastPageNum($pageCount);
                }
                else
                {
                    // NO total_count field. Must be ONLY page (complete list).
                    // Use size of results array.

                    $this->setTotalCount(count($pageArray));
                    $this->setLastPageNum(1);
                }
            }
        }
        else
        {
            // No 'results' array at all.
            $this->setTotalCount(0);
            $this->setPageCount(0);
            $this->setLastPageNum(0);
            return [];
        }

        $values = static::parseArray($pageArray);

        return $values;
    }

    /**
     * @param array <string, string|array <string, mixed>> $listOnly
     * @return array <string, TValueType>
     */
    abstract protected function parseArray(array $listOnly): array;

    /**
     * Get number of elements in specific list (without a massive load).
     * Request first page of list and check different possible results.
     * @param string $objectID  Object to get list for.
     * @param string $listType  Specific list to get.
     * @return int
     */
    public static function getListCount(string $objectID, string $listType): int
    {
        $API_Wrap = API_Wrapper::getInstance();

        $listCount     = -1;
        $listCount_URL = "/$objectID/$listType?fields=id&per_page=1&count=true";

        // -------------------------------
        $countReply = $API_Wrap->callAPI($listCount_URL);
        // -------------------------------

        if (isset($countReply ['total_count']))
        {
            $listCount = $countReply ['total_count'];
        }
        else
        {
            // Key 'total_count' only exists if more than 1 results.
            // Check other options.
            if (isset($countReply ['id']))
            {
                $listCount = 1;
            }
            else
            {
                if (!isset($countReply ['error']))
                {
                    $listCount = 0;
                }
            }
        }

        return $listCount;
    }

    /**
     * @internal
     * Print list values to file and to output. This is for debug only.
     * @param API_ListBase <TValueType> $list
     * @param array<string> $arrayValues
     */
    public static function logList(API_ListBase $list, array $arrayValues): void
    {
        //------------------------------------------------------
        // Filter (if any).

        // if ($list->isIDsOnly())
        //     return;
        //            $list->getClassName() != '\API_Profile' ||

        if ($list->getPageNum() != 1)
        {
            return;
        }

        //------------------------------------------------------
        // Description:

        $header
            = "\r\n======================================================\r\n\r\n"
            . "\tName    : " . $list->getListName() . "\r\n"
            . "\tClass   : " . $list->getClassName() . "\r\n"
            . "\tcount   : " . $list->count() . "\r\n"
            . "\tGet ALL : " . boolToString($list->isGetAll()) . "\r\n"
            . "\tID only : " . boolToString($list->isIDsOnly()) . "\r\n\r\n";

        //------------------------------------------------------
        // The list itself:

        $listString = print_r($arrayValues, true);
        $listArray  = explode(PHP_EOL, $listString);

        $startArray = array_slice($listArray, 0, 30); // Start
        $endArray   = array_slice($listArray, -30, 30); // End

        $listData
            = $header
            . implode("\r\n", $startArray) . "\r\n"
            . "------------------------------------------------------\r\n"
            . implode("\r\n", $endArray) . "\r\n";

        echo $listData;
        file_put_contents('list-log.log', $listData, FILE_APPEND);
    }

    //------------------------------------------------------

    /**
     * Get NEXT page (and process it).
     * @return array <string, string |array <string, mixed>> Processed page.
     */
    public function nextPage(): ?array
    {
        // objects that are getAll only have a single page.
        // If so, nothing to do here.

        if (!$this->isGetAll())
        {
            if ($this->getPageNum() < $this->getLastPageNum())
            {
                $this->pageNum++;

                $nextPageArray = $this->getPage();

                if (!empty($nextPageArray))
                {
                    $this->listArray = $nextPageArray;

                    $this->setPageCount(count($this->listArray));
                }
                else
                {
                    // Page was EMPTY
                    // Adjust total number of pages.

                    $this->setLastPageNum($this->pageNum--);
                }
            }
        }

        return $this->listArray;
    }

    /**
     * Get PREVIOUS page (and process it).
     * @return array <string, string |array <string, mixed>> Processed page.
     */
    public function previousPage(): ?array
    {
        // objects that are getAll only have a single page.
        // If so, nothing to do here.

        if (!$this->isGetAll())
        {
            if ($this->getPageNum() > 1)
            {
                $this->pageNum--;

                $this->listArray = $this->getPage();

                $this->setPageCount(count($this->listArray));
            }
        }

        return $this->listArray;
    }

    /**
     * Resets the internal list array. I'm not sure why I created this.
     * It probably SHOULD also reset the various list pointers.
     * TODO do something with this.
     * @return mixed the first list element (like builtin array reset ()).
     */
    public function reset(): mixed
    {
        return reset($this->listArray);
    }
}
