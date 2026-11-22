<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * API_Project - Describes a specific project.
 * The lists of profiles this object include are initliazed by dedicated getters.
 * {@link https://www.geni.com/platform/developer/help/api?path=project&version=1 API_Project}
 */
class API_Project extends API_Base
{
    /**
     * @var string Unique identifier for the project (e.g., "project-12345").
     */
    private string $id;

    /**
     * @var string The date and time the project was created, in ISO 8601 format.
     */
    private string $created_at;

    /**
     * @var string A detailed description of the project's goals or focus.
     */
    private string $description;

    /**
     * @var string The display name of the project.
     */
    private string $name;

    /**
     * @var int    The number of profiles in this project.
     *             This is NOT part of the Geni API, so it will be set only if
     *             a relevant function is called. See {@link self::getProfiles}
     *             and {@link self::getNumProfiles}.
     */
    private int    $num_profiles;

    /**
     * @var string The date and time the project was last updated, in ISO 8601 format.
     */
    private string $updated_at;

    /**
     * @var string The permalink URL to the project page on Geni.com.
     */
    private string $url;

    //-----------------------------------------------------
    // List of profiles relevant to the project. These are set by
    // specific API calls for each list. At the time of creation

    /**
     * List project Collaborators.
     * Values gotten using {@link self::getCollaborators}. Can not be modified via API.
     * Will contain either full API_Profile objects, or just the ID and
     * GUID. This depends on the $IDsOnly parameter of the getter.
     * @var \ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles
     */
    private ?API_ListProfiles  $collaborators;

    /**
     * List project Followers.
     * Values gotten using {@link self::getFollowers}. Can not be modified via API.
     * Will contain either full API_Profile objects, or just the ID and
     * GUID. This depends on the $IDsOnly parameter of the getter.
     * @var \ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles
     */
    private ?API_ListProfiles  $followers;

    /**
     * List project Profiles.
     * Values gotten using {@link self::getProfiles}. Can not be modified via API.
     * Will contain either full API_Profile objects, or just the ID and
     * GUID. This depends on the $IDsOnly parameter of the getter.
     * @var \ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles
     */
    private ?API_ListProfiles  $profiles;

    //=====================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->id           = '';
        $this->created_at   = '';
        $this->description  = '';
        $this->name         = '';
        $this->num_profiles = -1;
        $this->updated_at   = '';
        $this->url          = '';

        $this->collaborators = null;
        $this->followers     = null;
        $this->profiles      = null;
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     * @param array <string, string|array <string|mixed>> $arrayValues
     * @param bool $fromDB
     */
    public function updateFromArray(array $arrayValues, bool $fromDB): void
    {
        $this->id = $arrayValues['id'] ?? '';

        $this->created_at  = $arrayValues['created_at'] ?? '';
        $this->description = $arrayValues['description'] ?? '';
        $this->name        = $arrayValues['name'] ?? '';
        $this->updated_at  = $arrayValues['updated_at'] ?? '';
        $this->url         = $arrayValues['url'] ?? '';

        // The following collections are NOT sent from the API, only when requested specifically
        // with dedicated API calls. BUT the persistence layer does send them this way.

        if ($fromDB)
        {
            if (isset($arrayValues ['collaborators']) && count($arrayValues ['collaborators']) > 0)
            {
                $this->collaborators = API_ListProfiles::newFromArray($arrayValues ['collaborators'], listName: 'API_Project/collaborators', fromDB: $fromDB);
                $this->setLoaded('collaborators');
            }

            if (isset($arrayValues ['followers']) && count($arrayValues ['followers']) > 0)
            {
                $this->followers = API_ListProfiles::newFromArray($arrayValues ['followers'], listName: 'API_Project/followers', fromDB: $fromDB);
                $this->setLoaded('followers');
            }

            if (isset($arrayValues ['profiles']) && count($arrayValues ['profiles']) > 0)
            {
                $this->profiles = API_ListProfiles::newFromArray($arrayValues ['profiles'], listName: 'API_Project/profiles', fromDB: $fromDB);
                $this->setLoaded('profiles');
            }
        }
    }

    /**
     * Factory method: Create instance from array
     * @param array <string, string|array <string|mixed>> $arrayValues
     * @param bool $fromDB
     * @return API_Project
     */
    public static function newFromArray(array $arrayValues, bool $fromDB = false): API_Project
    {
        $project = new API_Project();

        $project->updateFromArray($arrayValues, $fromDB);

        return $project;
    }

    /**
     * @param API_Project $proj1
     * @param API_Project $proj2
     * @param bool $withUpdated
     * @param bool $withLists
     * @return bool
     */
    public static function __equals(
        API_Project $proj1,
        API_Project $proj2,
        bool        $withUpdated = false,
        bool        $withLists = false,
    ) {
        $same = true;

        if ($proj1->id != $proj2->id
            || $proj1->created_at != $proj2->created_at
            || $proj1->description != $proj2->description
            || $proj1->name != $proj2->name
            || $proj1->num_profiles != $proj2->num_profiles
            || $proj1->url != $proj2->url)
        {
            $same = false;
        }

        if ($same && $withUpdated)
        {
            if ($proj1->updated_at != $proj2->updated_at)
            {
                $same = false;
            }
        }

        if ($same && $withLists)
        {
            // TODO NOT implemented. Requires __equals for API_ListProfiles class,
            //      probably for list base class - API_ListBase.

            // if (!__equals ($proj1->collaborators, $proj2->collaborators) ||
            //     !__equals ($proj1->followers,     $proj2->followers)     ||
            //     !__equals ($proj1->profiles,      $proj2->profiles))
            // {
            //     $same = FALSE;
            // }
        }

        return $same;
    }

    //=====================================================
    // API Calls.

    /**
     * Get project information from Server.
     * @param string $projectUID
     * @return API_Project|NULL Returns NULL when project not found.
     */
    public static function get(string $projectUID): ?API_Project
    {
        $API_Wrap = API_Wrapper::getInstance();

        $Request_URL = "/$projectUID";

        // -------------------------------
        $ProjectInfo = $API_Wrap->callAPI($Request_URL);
        // -------------------------------

        $project = null;
        if ($ProjectInfo != null && isset($ProjectInfo ['id']))
        {
            $project = API_Project::newFromArray($ProjectInfo, false);
        }

        return $project;
    }

    /**
     * Add Documents to Project.
     * @param array<string> $blockofIDs Docments to add.
     * @return array <string, int|array<string>> array of Document IDs.
     */
    public function addDocuments(array $blockofIDs, bool $isGUID = false): array
    {
        return $this->addList('documents', $blockofIDs, $isGUID);
    }

    /**
     * Add profiles to Project.
     *
     * (There is NO API to remove profiles from a project (or to
     * remove a project from a profile)).
     *
     * @param array <string>  $blockofIDs  Array of IDs (or GUIDs) to add.
     * @param boolean $isGUID      TRUE when input are GUIDs.
     * @return array
     * @return array <string, int|array<string>> Number of profiles added,
     *                                           and arrray of IDs that were
     *                                           NOT added.
     */
    public function addProfiles(array $blockofIDs, bool $isGUID = false): array
    {
        return $this->addList('profiles', $blockofIDs, $isGUID);
    }

    /**
     * Return list of Collaborators of the Project (or just their IDs).
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param bool $force     Force loading of list, even if already loaded.
     * @param int  $pageSize  Number of entries in a single page.
     * @return API_ListProfiles
     */
    public function getCollaborators(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        return $this->getList('collaborators', $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Return list of Followers of the Project (or just their IDs).
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param bool $force     Force loading of list, even if already loaded.
     * @param int  $pageSize  Number of entries in a single page.
     * @return API_ListProfiles
     */
    public function getFollowers(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        return $this->getList('followers', $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Return list of Profiles in the Project (or just their IDs).
     * Also sets the num_profiles field.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param bool $force     Force loading of list, even if already loaded.
     * @param int  $pageSize  Number of entries in a single page.
     * @return API_ListProfiles
     */
    public function getProfiles(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        $profileList        = $this->getList('profiles', $getAll, $IDsOnly, $force, $pageSize);
        $this->num_profiles = $this->profiles->getTotalCount();

        return $profileList;
    }

    //=====================================================
    // Additional useful getters.

    /**
     * Verify that project exists.
     * @param string $projectUID
     * @return boolean
     */
    public static function verifyProjectExists(string $projectUID): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        // Request ID of project, using the given ID.
        $Request_URL = "/$projectUID?fields=id";

        // -------------------------------
        $ProjectInfo = $API_Wrap->callAPI($Request_URL);
        // -------------------------------

        $ProjectExists = false;

        if (isset($ProjectInfo ['id']))
        {
            if ($projectUID == $ProjectInfo ['id'])
            {
                $ProjectExists = true;
            }
        }

        return $ProjectExists;
    }

    /**
     * Returns number of profiles in Project.
     * This function does NOT get or update the list of profiles itself. If the
     * list of profiles is NOT loaded, it makes an API call to get the
     * number of profiles, otherwise use already set value.
     * @return int Number of profiles, or -1, if list not loaded.
     */
    public function getNumProfiles(): int
    {
        if (!$this->isLoaded('profiles'))
        {
            $this->num_profiles = API_ListBase::getListCount($this->getID(), 'profiles');
        }

        return $this->num_profiles;
    }

    //-----------------------------------------------------

    /**
     * Add list of IDs (either profiles or documents) to project.
     * @param string $listType  Name of list to get.
     * @param array<string>  $blockofIDs Array of IDs.
     * @param bool   $isGUID     When set ID values are GUID.
     * @return array <string, int|array<string>> IDs that were added.
     */
    private function addList(string $listType, array $blockofIDs, bool $isGUID = false): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        if (empty($blockofIDs))
        {
            $addedResult = [
                'Not Added' => 0,
                'Num Added' => 0
            ];

            return $addedResult;
        }

        if ($listType == 'profiles')
        {
            $paramName = 'profile_ids';
        }
        else
        {
            $paramName = 'ids';
        }

        if ($isGUID)
        {
            $idType = 'guid';
        }
        else
        {
            $idType = 'id';
        }

        // Divide list into blocks of 50 items to be sent each time.

        $allResultIDs = [];
        $chunkedIDs   = array_chunk($blockofIDs, API_ListBase::SIZE_OF_BATCH);

        foreach ($chunkedIDs as $chunk)
        {
            $stringOfIDs = $this->makeStringIDs($chunk, $isGUID);

            $addParams [ $paramName ] = $stringOfIDs;
            $addParams [ 'fields'   ] = $idType;

            $request_URL = '/' . $this->getID() . "/add_$listType";

            // -------------------------------
            $addedResult = $API_Wrap->callAPI_POST($request_URL, $addParams);
            // -------------------------------

            if ($addedResult != null)
            {
                // The add operation returns a COMPLETE list of all the objects
                // that are part of the Project, including those added previously.

                if (isset($addedResult ['results']))
                {
                    foreach ($addedResult ['results'] as $addedObject)
                    {
                        $allResultIDs [] = $addedObject [$idType];
                    }
                }
                elseif (isset($addedResult [$idType]))
                {
                    $allResultIDs [] = $addedResult [$idType];
                    break;
                }
            }
            else
            {
                break;
            }
        }

        $this->updated_at = $this->getObjectField($this->getID(), 'updated_at');

        //-----------------------------------------------------
        // Added all objects. Process results.

        $NumToAdd = count($blockofIDs);
        $NumAdded = count($allResultIDs);
        $NotAdded = [];
        $Existing = [];

        if ($NumAdded > 0)
        {
            if ($NumToAdd != $NumAdded)
            {
                // NOT all IDs were added.
                // Create array of results NOT added.
                $NotAdded = array_diff($blockofIDs, $allResultIDs);
                $Existing = array_diff($allResultIDs, $blockofIDs);

                $NumAdded = $NumAdded - count($Existing);
            }
        }

        $addedResult = [
            'Not Added' => $NotAdded,
            'Num Added' => $NumAdded
        ];

        return $addedResult;
    }

    /**
     * Return list of profiles related to the project (or just their IDs).
     * @param string $listType  Name of list to get.
     * @param bool   $getAll    Get the complete list in one read, rather than a
     *                          page at a time (that the user can page through).`
     * @param bool   $IDsOnly   Instead of returning API_Profile objects, returns
     *                          only their ID and GUID values.
     * @param bool   $force     Force loading of list, even if already loaded.
     * @param int    $pageSize  Number of entries in a single page.
     * @return API_ListProfiles
     */
    private function getList(string $listType, bool $getAll, bool $IDsOnly, bool $force, int  $pageSize): API_ListProfiles
    {
        if ($force || !$this->isLoaded($listType))
        {
            $listURL = '/' . $this->getID() . "/$listType";

            $this->$listType = new API_ListProfiles($listURL, $getAll, $IDsOnly, true, $pageSize);
            $this->setLoaded($listType);
        }

        return $this->$listType;
    }

    //=====================================================
    // Getters of project attributes.

    public function getID(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function getURL(): string
    {
        return $this->url;
    }
}
