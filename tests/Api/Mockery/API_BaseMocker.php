<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperTesting;

abstract class API_BaseMocker
{
    // The Map: Key is the folder name, Value is the Class
    /**
     * @var array <string, array <string, string|bool>>
     */
    private const MOCKER_MAP = [
        'comment'     => [ 'name' => API_CommentMocker::class,    'pass' => false ],
        'document'    => [ 'name' => API_DocumentMocker::class,   'pass' => true  ],
        'list'        => [ 'name' => API_ListMocker::class,       'pass' => false ],
        'photo'       => [ 'name' => API_PhotoMocker::class,      'pass' => false ],
        'photo_album' => [ 'name' => API_PhotoAlbumMocker::class, 'pass' => false ],
        'profile'     => [ 'name' => API_ProfileMocker::class,    'pass' => false ],
        'project'     => [ 'name' => API_ProjectMocker::class,    'pass' => false ],
        'stats'       => [ 'name' => API_StatsMocker::class,      'pass' => false ],
        'surname'     => [ 'name' => API_SurnameMocker::class,    'pass' => false ],
        'union'       => [ 'name' => API_UnionMocker::class,      'pass' => false ],
        'user'        => [ 'name' => API_UserMocker::class,       'pass' => false ],
        'video'       => [ 'name' => API_VideoMocker::class,      'pass' => false ],

//      'revision'    => [ 'name' => API_RevisionMocker::class,   'pass' => FALSE ],
        'misc'        => [ 'name' => API_DefaultMocker::class,    'pass' => false ],
    ];

    /**
     * "Who" - The ID of the object of the API call.
     * @var string
     */
    protected ?string $subject;

    /**
     * "What" - the API end-point method to call (e.g., 'update').
     * @var string $method
     */
    protected string $method;
    protected string $resourceType;

    /**
     * The remaining actionable segments of the URL of the API call (if any).
     * @var array <string>
     */
    protected array  $actions;

    /**
     * @var array <string, mixed|array <string, mixed>>
     */
    protected ?array  $fixture;

    /**
     * @var string
     */
    protected string $suffix;

    /**
     * This array contains Mocker class objects. The array is reset
     * at the beginning of each test.
     * @var array <string, API_BaseMocker>
     */
    private static array $testMockers;

    /**
     * @var array <string, array <string, mixed|array <string, mixed>>>
     * array of arrays for each of the test-objects added. These arrays
     * are used to create the replies to API queries.
     */
    private static array $testObjects = [];

    /**
     * @var array <string, array <string, API_ListMocker>> $testLists
     * 2D array of API_ListMocker objects for the current test-objects.
     * The structure of the array is [object-ID][list-name] => list object.
     */
    private static array $testLists; //   = [];

    /**
     * Array of indexes used to create ID values, for each type of object.
     * @var array<string|int>
     */
    private static array $defaultIDs = [];

    //------------------------------------------------------

    protected function __construct() {}

    /**
     * Updates the mocker context for reuse of instances.
     * This ensures the same object can handle different IDs and actions
     * within a single test execution.
     * @param string $subject           Object being used (e.g., document-123)
     * @param string $resourceType      Object type (document)
     * @param array <string> $actions   Operations to do with params.
     * @param array <string, mixed|array <string, mixed>> $fixture Default values from file.
     * @param string $suffix            Additional information (like specific test-name).
     */
    public function setContext(
        string $subject,
        string $resourceType,
        array  $actions,
        array  $fixture,
        string $suffix,
    ): void {
        // The "Who" - the ID from the URL (e.g., document-123)
        $this->setSubject($subject);

        $this->setMethod('');

        $this->setResourceType($resourceType);

        // The remaining URL segments, if any(e.g., ['edit', 'tags'])

        $this->setActions($actions);

        // The "Defaults" - the JSON fixture data for this specific call

        $this->setFixture($fixture);

        // Additional, optional, information for an API call. For example
        // the NAME of the unit-test function being called, to handle edge cases.

        $this->setSuffix($suffix);
    }

    //------------------------------------------------------

    /**
     * @param string $API_URL Full URL of API call.
     * @param array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param array <string, mixed|array <string, mixed>>   $fixture Default values from file.
     * @param string $suffix  Additional information (like specific test-name).
     * @return array <string, mixed|array <string, mixed>>
     */
    public static function createDynamicFixture(string $API_URL, ?array $API_Params = null, array $fixture = [], string $suffix = ''): array
    {
        $mocker = self::getMocker($API_URL, $fixture, $suffix);

        return $mocker->process($API_Params);
    }

    /**
     * Resets all mocker state, for new test.
     */
    public static function reset(): void
    {
        self::$testMockers = [];
        self::$testObjects = [];
        self::$testLists   = [];
        self::$defaultIDs  = [];
    }

    //------------------------------------------------------
    // Parts of createDynamicFixture process.

    /**
     * Factory method to get or create a mocker instance based on the URL.
     * @param string $API_URL Full URL of API call.
     * @param array <string, mixed|array <string, mixed>> $fixture Default values from file.
     * @param string $suffix  Additional information (like specific test-name).
     * @return API_BaseMocker
     */
    private static function getMocker(string $API_URL, array $fixture, string $suffix = ''): self
    {
        // 1. Determine the path stack from the URL

        $pathString = parse_url($API_URL, PHP_URL_PATH); // Result: "/document-44888697/edit"

        $pathStack = explode('/', trim($pathString, '/'));

        // 2. Get the subject and object type (i.e. 'document', etc.)
        //    The explode gives the correct results, even if NO hyphen.

        $subject      = array_shift($pathStack);
        $stripped     = explode('-', $subject);
        $resourceType = reset($stripped);

        // 3. Map the resource type to the specific Mocker class
        $className = self::MOCKER_MAP [$resourceType]['name'] ?? self::MOCKER_MAP ['misc']['name'];

        // 4. Lifecycle Management (Singleton + Context Refresh)
        $instance = self::getMockerByClass($className);

        if (self::MOCKER_MAP [$resourceType]['pass'])
        {
            // Passing the current test to the mocker, for handling edge-cases.

            $suffix = self::getRunningTestName();
        }

        // 5. Set /refresh mocker pointer, settings, and actions.
        $instance->setContext($subject, $resourceType, $pathStack, $fixture, $suffix);

        return self::$testMockers [$className];
    }

    /**
     * Factory method to get or create a mocker instance based on the URL.
     */
    public static function getMockerByClass(string $className): self
    {
        // Lifecycle Management (Singleton + Context Refresh)
        if (!isset(self::$testMockers [$className]))
        {
            // Create brand new instance
            $instance                       = new $className();
            self::$testMockers [$className] = $instance;
        }
        else
        {
            // Reuse existing instance.
            $instance = self::$testMockers [$className];
        }

        return $instance;
    }

    /**
     * The Standard Execution Engine
     * @param array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    private function process(array $API_Params): array
    {
        // The method name should be at the top of the actions array.
        // If the array is empty, it's a 'get' operation.

        $rawMethod = array_shift($this->actions) ?? 'get';
        $method    = str_replace('-', '_', $rawMethod);

        $this->setMethod($method);

        if (method_exists($this, $method))
        {
            return $this->{$method} ($API_Params, $this->getActions());
        }

        // Fallback: If no dynamic method exists, return the Template as-is
        $fixture = $this->getFixture();

        if (!empty($fixture))
        {
            return $fixture;
        }

        $this->handleMissingAction($method);
    }

    private function handleMissingAction(string $method): never
    {
        // Centralized 404 logic for the dynamic mocker
        throw new \Exception("Action '$method' not implemented in " . get_class($this));
    }

    /**
     * Find name of test function being called.
     * To be used together with the $suffix parameter, in the API_BaseMocker
     * functions. The individual API end-point functions, can then use this
     * value for exceptions in their responses (e.g. use a dedicated fixture).
     * @return string
     */
    private static function getRunningTestName(): string
    {
        // Capture the execution stack trace
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($trace as $step)
        {
            // Look for class methods that start with 'test' (PHPUnit convention)
            if (isset($step['class'])
                && str_starts_with($step['function'], 'test'))
            {
                // Returns something like "test_invalid_document_throws_error"
                return $step['function'];
            }
        }

        return '';
    }

    //------------------------------------------------------

    /** @param string $subject ID of the object being acted on (e.g., document-123). */
    protected function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /** @param string $method Which API call is being made on tthe subject. */
    protected function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /** @param array <string, mixed|array <string, mixed>> $fixture */
    protected function setFixture(array  $fixture): void
    {
        $this->fixture = $fixture;
    }

    /** @param array <string> $actions Actionable parts of API call. */
    protected function setActions(array  $actions): void
    {
        $this->actions = $actions;
    }

    /** @param string $suffix  Additional information (like specific test-name). */
    protected function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /** @param string $resourceType Type of object (e.g. 'document') of API call. */
    protected function setResourceType(string $resourceType): void
    {
        $this->resourceType = $resourceType;
    }

    //------------------------------------------------------

    /** @return string  ID of the object being acted on (e.g., document-123). */
    protected function getSubject(): ?string
    {
        return $this->subject;
    }

    /** @return string  Which API call is being made on tthe subject. */
    protected function getMethod(): string
    {
        return $this->method;
    }

    /** @return array <string, mixed|array <string, mixed>>  default values from file. */
    protected function getFixture(): array
    {
        return $this->fixture;
    }

    /** @return array <string>  Actionable parts of API call. */
    protected function getActions(): array
    {
        return $this->actions;
    }

    /** @return string $suffix  Additional information (like specific test-name). */
    protected function getSuffix(): string
    {
        return $this->suffix;
    }

    /** @return string  Type of object (e.g. 'document') of API call. */
    protected function getResourceType(): string
    {
        return $this->resourceType;
    }

    /**
     * @param array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return bool TRUE when only field is the ID.
     */
    protected function checkIDsOnly(array $API_Params): bool
    {
        return isset($API_Params ['fields']) && $API_Params ['fields'] === 'id';
    }

    //------------------------------------------------------
    // Functions to persist objects for the entire test.
    // The remove functions are for objects being deleted.

    /**
     * Set subject of API call.
     * @param array <string, mixed|array <string, mixed>> $object
     */
    protected function setSubjectObject(array $object): void
    {
        $type     = $this->getResourceType();
        $objectID = $this->getSubject();

        // Add /Update test object.

        self::$testObjects [$objectID] = [ 'type' => $type, 'object' => $object ];
    }

    /**
     * Get current object of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function getSubjectObject(): array
    {
        return $this->getTestObject($this->getSubject());
    }

    /**
     * "Delete" current object from test object.
     * @return bool
     */
    protected function removeSubjectObject(): bool
    {
        $subject = $this->getSubject();

        return $this->removeTestObject($subject);
    }

    //------------------------------------------------------

    /**
     * Add an additional object to the test data, e.g. when testing document/tag
     * you would add a profile.
     * @param  array <string, mixed|array <string, mixed>> $object
     * @param  string $type Class name
     * @return string ID of added object, whether from the object itself or generated.
     */
    protected function setTestObject(array $object, string $type): string
    {
        if (!isset($object ['id']))
        {
            // Object does NOT come with an ID. Must generate one.

            $id = $this->getDefaultID($type);
        }
        else
        {
            // Objects has an ID already.

            $id = $object ['id'];
        }

        self::$testObjects [$id] = [ 'type' => $type, 'object' => $object ];

        return $id;
    }

    /**
     * Get one of the test objects previously created.
     * @param string $id
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function getTestObject(string $id): array
    {
        $testObject = [];

        if (!$this->isGUID($id))
        {
            if (isset(self::$testObjects [$id]))
            {
                $testObject = self::$testObjects [$id]['object'];
            }
        }
        else
        {
            // GUID.

            $trimmedGUID = substr($id, strlen('profile-g'));

            foreach (self::$testObjects as $objEntry)
            {
                $obj = $objEntry ['object'];

                if (isset($obj ['guid']) && $obj ['guid'] == $trimmedGUID)
                {
                    $testObject = $obj;
                    break;
                }
            }
        }

        return $testObject;
    }

    /**
     * @param string $id
     * @return bool
     */
    protected function isExistsTestObject(string $id): bool
    {
        return isset(self::$testObjects [$id]);
    }

    /**
     * Remove (i.e. "delete") the object by it's index.
     * @param string $id
     * @return bool Successs of remove (if object not found will fail).
     */
    protected function removeTestObject(string $id): bool
    {
        if (isset(self::$testObjects [$id]))
        {
            // Set to empty object. This is what the server returns
            // for deleted objects.
            self::$testObjects [$id]['object'] = [ 'results' => [] ];

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Remove the object by it's INTERNAL id, not the index.
     * @param string $id
     */
    protected function removeByID(string $id): void
    {
        foreach (self::$testObjects as $objID => $object)
        {
            if (isset($object ['id']) && $object ['id'] == $id)
            {
                if ($objID != $id)
                {
                    // Don't remove the object itself, if present.
                    unset(self::$testObjects [$objID]);
                    break;
                }
            }
        }
    }

    /**
     * Get all test objects of given type.
     * @param string $type e.g. 'document'.
     * @return array <string, array <string, mixed|array <string, mixed>>>
     */
    protected function getTestObjectsByType(string $type): array
    {
        $objects = [];

        if ($type != '')
        {
            foreach (self::$testObjects as $id => $object)
            {
                if ($object ['type'] == $type)
                {
                    $objects [$id] = $object ['object'];
                }
            }
        }
        else
        {
            // NO type specified. Return all.

            $objects = self::$testObjects;
        }

        return $objects;
    }

    //------------------------------------------------------

    /**
     * Find name of TYPE for the mocker class (API_DocumentMocker => 'document')
     * This type value is used to add test objects.
     * @param string $className ::class value of the mocker.
     * @return string
     */
    protected function getClassType(string $className): string
    {
        // The value we want is the KEY for the mocker in the 2D map.
        // The mocker's name is under the 'name' key of the inner array.

        foreach (self::MOCKER_MAP as $key => $mockerData)
        {
            if ($mockerData['name'] === $className)
            {
                return $key;
            }
        }

        return '';
    }

    /**
     * Find name of the mocker class ('document' => API_DocumentMocker)
     * @param  string $classType
     * @return string 'class' value of the mocker.
     */
    protected function getClassName(string $classType): string
    {
        $className = '';
        if (isset(self::MOCKER_MAP [$classType]))
        {
            $className = self::MOCKER_MAP [$classType]['name'];
        }

        return $className;
    }

    protected function getDefaultID(string $type): string
    {
        // Use the indexes in $defaultIDs for the specific type.
        // These are incremented every time an object is added.

        if (!isset(self::$defaultIDs [$type]))
        {
            self::$defaultIDs [$type] = 0;
        }

        return "$type-" . self::$defaultIDs [$type]++;
    }

    //------------------------------------------------------
    // Functions to handle list objects.

    /**
     * Add new list to object.
     * @param string $ownerID    ID of the object this list is part of, e.g. user object->followers list.
     * @param string $listName   the API call that created the list.
     * @param API_ListMocker $list
     */
    protected function setList(string $ownerID, string $listName, API_ListMocker $list): void
    {
        self::$testLists[$ownerID][$listName] = $list;
    }

    /**
     * Get existing list.
     * @param string $ownerID    ID of the object this list is part of, e.g. user object->followers list.
     * @param string $listName   the API call that created the list.
     * @return API_ListMocker|NULL
     */
    protected function getList(string $ownerID, string $listName): ?API_ListMocker
    {
        return self::$testLists[$ownerID][$listName] ?? null;
    }

    /**
     * Add objects to an existing list (if needed, first create it).
     * @param string $ownerID    ID of the object this list is part of, e.g. user object->followers list.
     * @param string $listName   the API call that created the list.
     * @param array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param string $objectType Type of list objects.
     * @param array <string>|array <array <string, mixed|array <string, mixed>>> $objects
     *      Objects to add to list.
     * @return API_ListMocker
     */
    protected function updateList(
        string $ownerID,
        string $listName,
        array  $API_Params,
        string $objectType,
        array  $objects,
    ): API_ListMocker {
        $list = $this->getList($ownerID, $listName);

        if ($list == null)
        {
            $list = new API_ListMocker($ownerID, $listName, $API_Params, $objectType, $objects);
        }
        else
        {
            // List exists, just add new objects.

            $list->addObjects($objects);
        }

        $this->setList($ownerID, $listName, $list);

        $this->setUpdateAt();

        return $list;
    }

    /**
     * @param string  $ownerID    ID of the object this list is part of, e.g. user object->followers list.
     * @param string  $listName   the API call that created the list.
     * @param $object Object, or objectID to remove from list.
     * @param string|array <string, mixed|array <string, mixed>> $object Object (or ID) to remove.
     */
    protected function removeFromList(string $ownerID, string $listName, string|array $object): void
    {
        /** @var API_ListMocker $list */
        $list = self::$testLists[$ownerID][$listName];
        $list->removeObject($object);
    }

    //------------------------------------------------------

    /**
     * Function for loading alternate fixtures, based on this mocker's properties.
     * @param  string $fileName without any path.
     * @param  bool   $isMethod belonging to current method (otherwise 'misc').
     * @return array<string|string>
     */
    protected function loadFixture(string $fileName, bool $isMethod = true): array
    {
        $resource = $this->getResourceType();
        $method   = $isMethod ? $this->getMethod() : 'misc';

        $fixture = $this->loadFixtureDirectly($resource, $method, $fileName);

        return $fixture;
    }

    /**
     * Function for loading alternate fixtures for specific method.
     * @param string $resource
     * @param string $method
     * @param string $fileName  without any path.
     * @return array <string, mixed|array <string, mixed>> Fixture file contents.
     */
    protected function loadFixtureDirectly(string $resource, string $method, string $fileName): array
    {
        $BASE_PATH = API_WrapperTesting::getMockingBaseDir();

        // Make sure $fileName has .json extension.
        if (strrpos($fileName, '.json') == false)
        {
            $fileName .= '.json';
        }

        $filePath = "$BASE_PATH/$resource/$method/$fileName";

        $fixture = [];
        if (file_exists($filePath))
        {
            $fixture = json_decode(file_get_contents($filePath), true) ?: [];
            //API_WrapperTesting::logFileUse ($filePath, FALSE);
        }

        return $fixture;
    }

    //------------------------------------------------------
    // Generic helper functions.

    /**
     * Create set of test-objects, based on IDs, and class.
     * This function gets the mocker for the requested class, and creates array
     * 'objects' from the array of IDs. This function depends on the implementation
     * of the createObjectByID function by the mocker class.
     * @param array <string|array <string, mixed|array <string, mixed>>> $objects
     *      Objects to add to list.
     * @param string  $className Class of the object for which we are creating arrays.
     * @return array <int|array <string|string>> Array of arrays 'objects'.
     */
    protected function createClassObjects(array $objects, string $className, bool $IDsOnly): array
    {
        $mockerName  = $this->getClassName($className);
        $classMocker = API_BaseMocker::getMockerByClass($mockerName);
        $classType   = $this->getClassType($className);

        $outObjects = [];

        if ($IDsOnly)
        {
            foreach ($objects as $objectID)
            {
                $obj                    = $classMocker->createObjectByID($objectID);
                $outObjects [$objectID] = $obj;

                $this->setTestObject($obj, $classType);
            }
        }
        else
        {
            $isObject = is_array(reset($objects));

            if ($isObject)
            {
                foreach ($objects as $object)
                {
                    $ID               = $object ['id'];
                    $outObjects [$ID] = $object;
                }
            }
            else
            {
                foreach ($objects as $objectID)
                {
                    $outObjects [$objectID] = $objectID;
                }
            }
        }

        return $outObjects;
    }

    /**
     * Create set of test-objects, based on IDs, and class.
     * This function creates array 'objects' from the array of IDs.
     * @param string $ID
     * @return array <string, mixed|array <string, mixed>>
     */
    abstract public function createObjectByID(string $ID): array;

    /**
     * Set or clear a field in the passed fixture, using values from API call.
     * @param array <string, mixed|array <string, mixed>> $fixture Default values from file.
     * @param string $fieldName
     * @param array <string, string|array <string, string>> $sourceArray Parameters of API call.
     */
    protected function setFixtureField(array &$fixture, string $fieldName, array $sourceArray): void
    {
        if (isset($sourceArray [$fieldName]))
        {
            $fixture [$fieldName] = $sourceArray[$fieldName];
        }
        else
        {
            unset($fixture [$fieldName]);
        }
    }

    /**
     * @param string $method     API method call.
     * @param array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param array <string> $urlActions   Operations to do with params.
     */
    protected function logAPICall(string $method, array $API_Params, array $urlActions): void
    {
        echo "\r\n=============================================================\r\n";
        echo "$method\r\n";
        echo "\tAPI parameters:\r\n";
        print_r($API_Params);
        echo "\tURL values:\r\n";
        print_r($urlActions);
    }

    /**
     * Check if the current test is as specified by class & method.
     * This is used to identify specific tests, that need to override the default flow.
     * @param  string $className   Name of Unit-Test class.
     * @param  string $methodName  Name of test function.
     * @return bool  True when test is being called.
     */
    public static function isSpecificTest(string $className, string $methodName): bool
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($trace as $frame)
        {
            if (isset($frame['class'])
                && $frame['class'] === $className
                && $frame['function'] === $methodName)
            {
                return true;
            }
        }

        return false;
    }

    protected function setUpdateAt(): void
    {
        $fixtureValues = $this->getSubjectObject();

        $fixtureValues ['updated_at'] = time();

        $this->setSubjectObject($fixtureValues);
    }

    protected function isGUID(string $ID): bool
    {
        return str_contains($ID, '-g');
    }

    //------------------------------------------------------

    /**
     * Merge values from params into test object.
     * @param  array <string, mixed|array <string, mixed>> $subjectObject array <string, mixed|array <string, mixed>>
     * @param  array <string> $params Values to merge.
     * @param  array <string> $keys   List of keys from combined objects.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function mergeParams(array $subjectObject, array $params, array $keys): array
    {
        foreach ($keys as $key)
        {
            $subjectObject[$key] = $params[$key] ?? $subjectObject[$key];
        }

        return $subjectObject;
    }
}
