<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

use ShmuelAKam\Greenjeans\Api\API_Profile;

/**
 * List of {@link \ShmuelAKam\Greenjeans\Api\API_Profile}s as returned from the server.
 The profile ID will be used as a KEY for the lists. If the $IDsOnly flag is
 * set on creation, instead of the profile, the list value will be TRUE.
 *
 * @see     API_ListBase
 * @extends API_ListBase<bool|API_Profile>
 */
class API_ListProfiles extends API_ListBase
{
    /**
     * Constructor.
     * @param string $listName  Fixed part of URL to request the list from server.
     * @param bool   $getAll    When set, reads the entire list when created.
     * @param bool   $IDsOnly   When set list will contain only IDs and GUID values.
     * @param int    $pageSize  Number of elements to request to read each time.
     * @param bool   $fromAPICall  When set, list was created from a specific API call (see $listName).
     */
    public function __construct(
        ?string $listName,
        bool    $getAll = false,
        bool    $IDsOnly = true,
        bool    $fromAPICall = true,
        int     $pageSize = API_ListBase::SIZE_OF_BATCH,
    ) {
        parent::__construct($listName, 'ShmuelAKam\Greenjeans\Api\API_Profile', $getAll, $IDsOnly, $fromAPICall, $pageSize);
    }

    //------------------------------------------------------

    /**
     * Factory method: Create instance from array
     * Called from the updateFromArray processes of objects that contain lists.
     *
     * @param array<string, string|array <string, mixed>> $arrayValues
     * @param string $listName
     * @param bool $IDsOnly
     * @param bool $fromDB
     *
     * @return API_ListProfiles
     */
    public static function newFromArray(array $arrayValues, string $listName, bool $IDsOnly = true, bool $fromDB = false): API_ListProfiles
    {
        if (isset($arrayValues ['results']))
        {
            // Get the inner array to parse.
            $arrayValues = $arrayValues ['results'];
        }

        // Process the $arrayValues depending on its source and content.
        // If from DB or IDs only, then create look-up table with format
        //
        // [
        //     'profile-7653' => TRUE,
        //     'profile-1010' => TRUE
        // ]
        //
        // Otherwise if received profiles from server, then create array
        // with value being the profile instead of TRUE.
        //
        // [
        //     'profile-7653' => [
        //         'id' => 'profile-7653'
        //         ....
        //     ],
        //     'profile-1010' => [
        //         'id' => 'profile-1010'
        //         ....
        //     ],
        // ]

        if (!$fromDB)
        {
            if ($IDsOnly)
            {
                // Example input:
                // 0 => profile-7653
                // 1 => profile-1010

                $parsedValues = array_fill_keys($arrayValues, true);
            }
            else
            {
                // Example input:
                // 0 => [
                //     'id' => profile-7653
                //     ....
                //      ],
                // 1 => [
                //     'id' => profile-1010
                //     ....
                //      ],

                $parsedValues = [];
                foreach ($arrayValues as $profile)
                {
                    $parsedValues [$profile ['id']] = $profile;
                }
            }
        }
        else
        {
            // From DB. DB reply IS flat already, because it's a single column from the DB.
            $parsedValues = array_fill_keys($arrayValues, true);
        }

        $list = new API_ListProfiles($listName, getAll: true, IDsOnly: $IDsOnly, fromAPICall: false);

        $list->setList($parsedValues);

        $list->setPageCount(count($arrayValues));
        $list->setTotalCount(count($arrayValues));
        $list->setLastPageNum(1);
        $list->pageNum = 1;

        return $list;
    }

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Api\Core\API_ListBase::parseArray
     * @return array <string, API_Profile|bool>
     */
    protected function parseArray(array $listOnly): array
    {
        $arrayObjects = [];

        if (!$this->isIDsOnly())
        {
            foreach ($listOnly as $listObj)
            {
                // Convert each entry into object and save to array with ID as key.
                $profileID = $listObj ['id'];
                $obj       = API_Profile::newFromArray($listObj);

                $arrayObjects [$profileID] = $obj;
            }
        }
        else
        {
            // List of IDs.

            $first = reset($listOnly);

            if (is_array($first))
            {
                foreach ($listOnly as $listObj)
                {
                    // Create entry for profileID;.
                    $profileID = $listObj ['id'];

                    $arrayObjects [$profileID] = true;
                }
            }
            else
            {
                // Array of ID strings.

                foreach ($listOnly as $listID)
                {
                    $arrayObjects [$listID] = true;
                }
            }
        }

        return $arrayObjects;
    }
}
