<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;

/**
 * This mocker is NOT like the others, as it does NOT represent a 'resource type'.
 * Instead it is called and used by the other mockers to generate replies for the
 * end-points that return a list (which gets translated into the API_List... classes.
 * It used two dedicated fixtures for this, for ID-only lists and for lists that
 * return a list of objects.
 */
class API_ListMocker extends API_BaseMocker
{
    /**
     * @var array <string, array <string, string>>
     * Array of all the lists in the API. The values are the
     * type of object each list contains. Of course in some cases This
     * can be just the object ID.
     */
    private const LIST_TYPES = [
        'document' => [
            'comments'  => 'comment',
            'projects'  => 'project',
            'tags'      => 'profile',
        ],
        'photo' => [
            'comments'  => 'comment',
            'tags'      => 'profile',
        ],
        'video' => [
            'comments'  => 'comment',
            'tags'      => 'profile',
        ],
        'photo-album' => [
            'photos'    => 'photo',
        ],
        'project' => [
            'documents'     => 'document',
            'collaborators' => 'profile',
            'followers'     => 'profile',
            'profiles'      => 'profile',
        ],
    ];
    private string $ownerID;
    private string $listName;
    private string $listType;
    private int    $pageSize;
    private bool   $IDsOnly;

    /**
     * List of the objects (or just object IDs) in this list.
     * @var array <string|array <string, mixed|array <string, mixed>>>
     */
    private array $listObjects;

    /**
     * Constructor.
     * @param string $ownerID    ID of the object this list is part of, e.g. user object->followers list.
     * @param string $listName   the API call that created the list.
     * @param array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param string $objectType Type of list objects.
     * @param array <string|array <string, mixed|array <string, mixed>>> $objects
     *      Objects to add to list.
     */
    public function __construct(
        string $ownerID,
        string $listName,
        array  $API_Params,
        string $objectType,
        array  $objects,
    ) {
        parent::__construct();

        $this->ownerID  = $ownerID;
        $this->listName = $listName;
        $this->listType = $objectType;
        $this->pageSize = $API_Params ['per_page'] ?? API_ListBase::SIZE_OF_BATCH;
        $this->IDsOnly  = $this->checkIDsOnly($API_Params);

        // Create test objects using the IDs.

        $this->listObjects = [];

        if ($this->listName != 'comments')
        {
            if (!$this->isIDsOnly())
            {
                $this->listObjects = $this->createClassObjects($objects, $objectType, false);
            }
            else
            {
                foreach ($objects as $object)
                {
                    $this->listObjects [$object] = $object;
                }
            }
        }
        else
        {
            $this->listObjects = $objects;
        }
    }

    /**
     * @param array <string>|array <array <string, mixed|array <string, mixed>>> $moreObjects
     */
    public function addObjects(array $moreObjects): void
    {
        if ($this->listName != 'comments')
        {
            $isObject = is_array(reset($moreObjects));

            if ($isObject)
            {
                foreach ($moreObjects as $object)
                {
                    $objID                      = $object ['id'];
                    $this->listObjects [$objID] = $object;
                }
            }
            else
            {
                foreach ($moreObjects as $objectID)
                {
                    $this->listObjects [$objectID] = $objectID;
                }
            }
        }
        else
        {
            // Merge lists, remove duplicates and reindex it.
            $mergedList        = array_unique(array_merge($this->listObjects, $moreObjects), SORT_REGULAR);
            $this->listObjects = array_values($mergedList);

            $firstNew = reset($moreObjects);
            $this->setIDsOnly(!is_array($firstNew));
        }
    }

    /**
     * Remove item from list.
     * The list classes in the API do NOT support remove operations, because they
     * are read-only. BUT because this class is used internally by the mockers, it
     * needs a remove so that for example, a profile can be 'untagged'.
     * @param string|array <string, mixed|array <string, mixed>> $object Object to remove.
     */
    public function removeObject(string|array $object): void
    {
        if ($this->listName != 'comments')
        {
            // All lists except for 'comments' have ID values.

            if (is_array($object))
            {
                $removeID = $object ['id'];
            }
            else
            {
                $removeID = $object;
            }

            if (isset($this->listObjects [$removeID]))
            {
                unset($this->listObjects [$removeID]);
            }
        }
        else
        {
            foreach ($this->listObjects as $key => $listObject)
            {
                if ($listObject === $object)
                {
                    unset($this->listObjects [$key]);
                    break;
                }
            }
        }
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, string|array <string, string>>
     */
    public function generatePageReply(array $API_Params): array
    {
        static $OBJECT_CLASS_TYPES = [
            'comment', 'document', 'list',  'photo',   'photo-album',
            'project', 'revision', 'stats', 'surname', 'union',
            'user',    'video'
        ];

        // Process call parameters.

        $pageNum = $API_Params ['page'] ?? 1;

        //--------------------------------------------------
        // Isolate segment of the array needed for page.

        $pageSize = $this->getPageSize();
        if (count($this->listObjects) <= $pageSize)
        {
            $objects = $this->listObjects;
        }
        else
        {
            // List has multiple pages.

            $startOffset = ($pageNum - 1) * $pageSize;
            $objects     = array_slice($this->listObjects, $startOffset, $pageSize);
        }

        //--------------------------------------------------
        // Create array of the objects /IDs.

        $outObjects = [];
        $ownerID    = $this->getOwnerID();
        $ownerType  = substr($ownerID, 0, strpos($ownerID, '-'));

        if ($ownerType === 'project' && $this->listName == 'profiles')
        {
            $outObjects = $this->makeProjectProfilesArray($objects);
        }
        elseif (in_array($this->listType, $OBJECT_CLASS_TYPES))
        {
            $outObjects = $this->makeObjectsArray($objects);
        }
        elseif ($this->listType === 'profile')
        {
            $outObjects = $this->makeProfilesArray($objects);
        }
        else
        {
            // All other types are treated as strings.

            $outObjects = $this->makeStringsArray($objects);
        }

        //--------------------------------------------------
        // Put values in template.

        if (count($objects) <= $pageSize)
        {
            // Single page list. Don't need other fields.

            $list = [
                'results' => $outObjects
            ];
        }
        else
        {
            // Multi-page list. Need the whole thing.

            $list = [
                'results'     => $outObjects,
                'page'        => $pageNum,
                'next_page'   => $this->makePageURL($API_Params, next: true),
            ];

            if ($pageNum == 1)
            {
                $list ['total_count'] = count($this->listObjects);
            }
            else
            {
                $list ['prev_page'] = $this->makePageURL($API_Params, next: false);
            }
        }

        return $list;
    }

    /**
     * Convert internal array of IDs into the "objects" they represent.
     */
    public function convertIDs(): void
    {

        foreach ($this->listObjects as $key => $ID)
        {
            $this->listObjects [$key] = $this->getTestObject($ID);
        }

        $this->setIDsOnly(false);
    }

    //======================================================

    public function getOwnerID(): string
    {
        return $this->ownerID;
    }

    public function getListName(): string
    {
        return $this->listName;
    }

    public function getListType(): string
    {
        return $this->listType;
    }

    /**
     * @return array <string|array <string, mixed|array <string, mixed>>>
     */
    public function getListObjects(): array
    {
        return $this->listObjects;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function isIDsOnly(): bool
    {
        return $this->IDsOnly;
    }

    protected function setIDsOnly(bool $IDs): void
    {
        $this->IDsOnly = $IDs;
    }

    protected function findListType(string $id): string
    {
        $objectType = substr($id, 0, strpos($id, '-'));

        return self::LIST_TYPES [$objectType][$this->getListName()] ?? '';
    }

    //======================================================

    /**
     * Get array of all the test-objects that match the 'type'.
     * @param string $type
     * @return array<string|array <string|string>>
     */
    protected function getForList(string $type): array
    {
        return $this->getTestObjectsByType($type);
    }

    //------------------------------------------------------
    // Functions to create arrays of values for the generatePageReply process.

    /**
     * @param array <string|array <string, mixed|array <string, mixed>>> $objects
     * @return array{}
     */
    protected function makeStringsArray(array $objects): array
    {
        return [];
    }

    /**
     * @param  array <string|array <string, mixed|array <string, mixed>>> $objects
     * @return array <string|array <string, mixed|array <string, mixed>>>|array <array <string, string>>
     */
    protected function makeProjectProfilesArray(array $objects): array
    {
        $outObjects = [];
        $first      = reset($objects);

        if (is_array($first))
        {
            $outObjects = $objects;
        }
        else
        {
            if (substr($first, 0, 1) !== 'g')
            {
                $keyName = 'id';
            }
            else
            {
                $keyName = 'guid';
            }

            foreach ($objects as $obj)
            {
                $outObjects [] = [ $keyName => $obj ];
            }
        }

        return $outObjects;
    }

    /**
     * @param  array <string|array <string, mixed|array <string, mixed>>> $objects
     * @return array <string|array <string, mixed|array <string, mixed>>>|array <array <string, string>>
     */
    protected function makeObjectsArray(array $objects): array
    {
        $outObjects = [];

        $first = reset($objects);

        if ($this->isIDsOnly() && !is_array($first))
        {
            // Create array containing only ID fields.

            foreach ($objects as $objID)
            {
                $outObjects [] = [ 'id' => $objID ];
            }
        }
        else
        {
            $outObjects = $objects;
        }

        return $outObjects;
    }

    /**
     * @param  array <string|array <string, mixed|array <string, mixed>>> $objects
     * @return array <string|array <string, mixed|array <string, mixed>>>|array <array <string, string>>
     */
    protected function makeProfilesArray(array $objects): array
    {
        $outObjects = [];

        if ($this->isIDsOnly())
        {
            $first = reset($objects);
            if (!isset($first ['id']))
            {
                // These objects do NOT have an 'id' value. This is an
                // IDs-only list. There is nothing to return. This can
                // only be caused by an error in the program.
                // TODO Invalid request. Throw something?
                return [];
            }

            // Create array containing only ID fields.

            foreach ($objects as $obj)
            {
                $outObjects [] = [ 'id' => $obj ['id'] ];
            }
        }
        else
        {
            $outObjects = $objects;
        }

        return $outObjects;
    }

    //------------------------------------------------------

    /**
     * Create URL string for next or previous page. Goes into page reply
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  bool $next When true URL is for the next page, otherwise page-number set to previous.
     * @return string
     */
    protected function makePageURL(array $API_Params, bool $next): string
    {
        static $ROOT_URL = 'https://www.geni.com/api/';

        $page = $API_Params ['page'] ?? 1;

        $pageURL = $ROOT_URL . $this->getResourceType() . '/' . $this->getMethod() . '?';

        // Add parameters.

        if (isset($API_Params ['fields']))
        {
            $pageURL .= 'fields=' . $API_Params ['fields'] . ',';
        }
        elseif (isset($API_Params ['field_group']))
        {
            $pageURL .= 'field_group=' . $API_Params ['field_group'] . ',';
        }

        if ($next)
        {
            $page++;
        }
        else
        {
            $page--;
        }

        $pageURL .= "page=$page&per_page={$this->getPageSize()}";

        return $pageURL;
    }

    /**
     * {@inheritDoc}
     * This is an empty function. Lists do NOT have an ID.
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array{}
     */
    public function createObjectByID(string $ID): array
    {
        return [];
    }

    public function debug_print(): void
    {
        echo "\r\n----------------------\r\n";
        echo "List (Owner: {$this->getOwnerID()}, Name: {$this->getListName()}, Type: {$this->getListType()})";
        print_r($this->getListObjects());
        echo "\r\n----------------------\r\n";
    }
}
