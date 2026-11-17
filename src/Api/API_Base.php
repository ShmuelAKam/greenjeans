<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * Base class for all API objects.
 * Mostly empty.
 */
class API_Base
{
    /**
     * Array of flags, set in each object to indicate the specific collections
     * (the key name) has been loaded.
     * @var array <string, bool>
     */
    private array $loadedLists;

    public function __construct()
    {
        $this->loadedLists = [];
    }

    //--------------------------------------------------------------------------

    /**
     * Check if collection is loaded.
     * @param  string $key Collection 'name'.
     * @return bool
     */
    public function isLoaded(string $key): bool
    {
        return $this->loadedLists [$key] ?? false;
    }

    /**
     * Set a collection loaded flag.
     * @param string $key Collection 'name'.
     */
    protected function setLoaded(string $key): void
    {
        $this->loadedLists [$key] = true;
    }

    /**
     * Reset a collection loaded flag.
     * This exists for the special case where a load replaces an earlier load
     * operation. For example 'tags' being loaded after a 'tag-IDs' load.
     * @param string $key Collection 'name'.
     */
    protected function clearLoaded(string $key): void
    {
        unset($this->loadedLists [$key]);
    }

    //--------------------------------------------------------------------------

    protected function getObjectField(string $ID, string $fieldName): mixed
    {
        $API_Wrap = API_Wrapper::getInstance();

        $request_URL = "/$ID?fields=$fieldName";

        // -------------------------------
        $getReply = $API_Wrap->callAPI($request_URL);
        // -------------------------------

        $fieldValue = $getReply [$fieldName] ?? null;

        return $fieldValue;
    }

    /**
     * Make comma separated string of IDs from array.
     * @param array <string> $blockofIDs Array of IDs.
     * @param bool   $isGUID     When set ID values are GUID.
     * @return string
     */
    protected function makeStringIDs(array $blockofIDs, bool $isGUID = false): string
    {
        $StringOfIDs = "";

        if ($isGUID)
        {
            $StringOfIDs = 'g' . implode(',g', $blockofIDs);
        }
        else
        {
            $StringOfIDs = implode(',', $blockofIDs);
        }

        return $StringOfIDs;
    }

    /**
     * Convert comma delimited list into array if necessary.
     * Some fields (e.g. /profile/nicknames) are optionally sent as a string,
     * insteatd of an array. So it is necesary to convert them. If array was
     * sent, it is returned as is.
     * @param  array <string>  $value - when list, array has only one member.
     * @return array <string>
     */
    public static function parseArrayOrCommaList(array|string|null $value): array
    {
        $tempArray   = [];
        $parsedArray = [];

        if (is_string($value)
            || (is_array($value) && count($value) > 0))
        {
            if (is_array($value))
            {
                if (count($value) == 1)
                {
                    // Check if single value ends with a comma.
                    $commaIndex = strpos($value[0], ',');
                    if ($commaIndex == strlen($value[0]) - 1)
                    {
                        $value[0] = substr($value[0], 0, $commaIndex - 1);
                    }
                }

                $tempArray = $value;
            }
            elseif (is_string($value))
            {
                // Comma delimited list. Convert to array.
                // Will convert to array even if single value (no ',').
                $tempArray = explode(',', $value);
            }

            foreach ($tempArray as $entry)
            {
                $parsedArray [] = trim($entry);
            }
        }

        return $parsedArray;
    }
}
