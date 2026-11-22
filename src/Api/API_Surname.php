<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * API_Surname
 * Represents a surname in the general sense, i.e. without reference to a specific
 * profile. It contains a description of the family-name, if entered by a user,
 * and has two optional lists of the profiles with the name, or those that follow it.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=surname&version=1 API_Surname}
 */
class API_Surname extends API_Base
{
    private string $description;    // The surname's description.
    private string $ID;             // The surname's node id.
    private string $slugged_name;   // The surname's slugged name.
    private string $URL;            // The surname's url.

    private int    $follower_count;
    private int    $profile_count;

    private ?API_ListProfiles  $followers;
    private ?API_ListProfiles  $profiles;

    //=====================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->description  = '';
        $this->ID           = '';
        $this->slugged_name = '';
        $this->URL          = '';

        $this->clearLists();
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     * @param array<string, string|array<string, mixed>> $arrayValues
     * @param bool $fromDB
     */
    public function updateFromArray(array $arrayValues, bool $fromDB = false): void
    {
        $this->description  = $arrayValues['description'] ?? '';
        $this->ID           = $arrayValues['id'] ?? '';
        $this->slugged_name = $arrayValues['slugged_name'] ?? '';
        $this->URL          = $arrayValues['url'] ?? '';

        // The following collections are NOT sent from the API, only when requested specifically
        // with dedicated API calls. BUT the persistence layer does send them this way.

        if ($fromDB)
        {
            if (isset($arrayValues ['followers']) && count($arrayValues ['followers']) > 0)
            {
                $this->followers = API_ListProfiles::newFromArray($arrayValues ['followers'], listName: 'surname/followers', fromDB: $fromDB);
                $this->setLoaded('followers');
            }

            if (isset($arrayValues ['profiles']) && count($arrayValues ['profiles']) > 0)
            {
                $this->profiles = API_ListProfiles::newFromArray($arrayValues ['profiles'], listName: 'surname/profiles', fromDB: $fromDB);
                $this->setLoaded('profiles');
            }
        }
    }

    /**
     * Factory method: Create instance from array
     * @param array<string, string|array<string, mixed>> $arrayValues
     * @param bool $fromDB  Call is from persistence layer.
     * @return API_Surname
     */
    public static function newFromArray(array $arrayValues, bool $fromDB = false): API_Surname
    {
        $surname = new API_Surname();

        $surname->updateFromArray($arrayValues, $fromDB);

        return $surname;
    }

    /**
     * Reset list data-members.
     */
    private function clearLists(): void
    {
        $this->followers      = null;
        $this->profiles       = null;
        $this->follower_count = -1;
        $this->profile_count  = -1;
    }

    /**
     * Get number of elements in specific list.
     * Request first page of list and check different possible results.
     * @param string $listType 'followers' or 'profiles'.
     * @return int
     */
    private function getListCount(string $listType): int
    {
        return API_ListBase::getListCount($this->getID(), $listType);
    }

    //=====================================================
    // API calls.

    /**
     * Get basic information about the Surname (not inc. lists and counts).
     * @param string $ID
     */
    public static function get(string $ID): ?API_Surname
    {
        $API_Wrap = API_Wrapper::getInstance();

        $surname = null;

        if ($ID != null)
        {
            $getSurnameURL = "/$ID";

            // -------------------------------
            $surnameArray = $API_Wrap->callAPI($getSurnameURL);
            // -------------------------------

            if ($surnameArray != null && array_key_exists('id', $surnameArray))
            {
                $surname = API_Surname::newFromArray($surnameArray);

                $surname->clearLists();
            }
        }

        return $surname;
    }

    /**
     * Get list of followers of the Surname.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListProfiles list of profile-IDs or {@link API_Profile} objects,
     *                        depending on the $IDsOnly parameter.
     */
    public function getFollowers(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        if ($force || !$this->isLoaded('followers'))
        {
            $ListURL = '/' . $this->getID() . '/followers';

            $this->followers      = new API_ListProfiles($ListURL, $getAll, $IDsOnly, true, $pageSize);
            $this->follower_count = count($this->followers);
            $this->setLoaded('followers');
        }

        return $this->followers;
    }

    /**
     * Get list of Profiles that have the Surname.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListProfiles list of profile-IDs or {@link API_Profile} objects,
     *                        depending on the $IDsOnly parameter.
     */
    public function getProfiles(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        if ($force || !$this->isLoaded('profiles'))
        {
            $ListURL = '/' . $this->getID() . '/profiles';

            $this->profiles      = new API_ListProfiles($ListURL, $getAll, $IDsOnly, true, $pageSize);
            $this->profile_count = count($this->profiles);
            $this->setLoaded('profiles');
        }

        return $this->profiles;
    }

    //======================================================
    // Getter functions.

    /**
     * Get NUMBER of Followers of the name.
     * Does NOT load this list.
     * @return int
     */
    public function getFollowerCount(): int
    {
        if ($this->follower_count == -1 && !$this->isLoaded('followers'))
        {
            $this->follower_count = $this->getListCount('followers');
        }

        return $this->follower_count;
    }

    /**
     * Get NUMBER of Profiles with the name.
     * Does NOT load this list.
     * @return int
     */
    public function getProfileCount(): int
    {
        if ($this->profile_count == -1 && !$this->isLoaded('profiles'))
        {
            $this->profile_count = $this->getListCount('profiles');
        }

        return $this->profile_count;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getID(): string
    {
        return $this->ID;
    }

    public function getSluggedName(): string
    {
        return $this->slugged_name;
    }

    public function getURL(): string
    {
        return $this->URL;
    }
}
