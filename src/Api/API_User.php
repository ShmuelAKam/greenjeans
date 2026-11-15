<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListObjects;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_ListStrings;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * API_User - This class API describes the USER that authorized the app, i.e.
 * who is running it. It is a READ-ONLY object (Except for the two metadata
 * functions).
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=user&version=1 API_User}
 */
class API_User extends API_Base
{
    /**
     * The user's account type: basic, plus, pro.
     * @var string
     */
    private string $accountType;

    /**
     * The user's name.
     * @var string
     */
    private string $name;

    /**
     * This array can be used to store ANY user-provided data, at run-time.
     * This is the ONLY field in this object that can be modified.
     * TODO Currently this field is not needed, because the get function ALWAYS
     * queries the server. This should be changed somehow, for when using a
     * persistence layer.
     * @var array<string, string|array<string, mixed>>
     */
    private array  $metadata;
    private ?API_ListObjects  $followed_documents;
    private ?API_ListProfiles $followed_profiles;
    private ?API_ListObjects  $followed_projects;
    private ?API_ListObjects  $followed_surnames;
    private ?API_ListProfiles $managed_profiles;
    private ?API_ListProfiles $max_family;
    private ?API_ListObjects  $my_albums;
    private ?API_ListStrings  $my_labels;
    private ?API_ListObjects  $uploaded_documents;
    private ?API_ListObjects  $uploaded_photos;
    private ?API_ListObjects  $uploaded_videos;
    private bool              $fromDB;

    //------------------------------------------------------

    /**
     * Constructor.
     */
    private function __construct()
    {
        parent::__construct();

        $this->accountType = '';
        $this->name        = '';

        // This is the only value that can be set by the user.

        $this->metadata = [];

        // Set all lists to NULL, until called by list specific API.

        $this->followed_documents = null;
        $this->followed_profiles  = null;
        $this->followed_projects  = null;
        $this->followed_surnames  = null;
        $this->managed_profiles   = null;
        $this->max_family         = null;

        $this->my_albums = null;
        $this->my_labels = null;

        $this->uploaded_documents = null;
        $this->uploaded_photos    = null;
        $this->uploaded_videos    = null;

        $this->fromDB = false;
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     * @see API_User::newFromArray
     * @param array<string, string|array<string, mixed>> $arrayValues Server reply.
     */
    public function updateFromArray(array $arrayValues, bool $fromDB = false): void
    {
        if ($fromDB)
        {
            $this->setFromDB();
        }

        if (isset($arrayValues ['account_type']))
        {
            $this->accountType = $arrayValues ['account_type'];
        }

        if (isset($arrayValues ['name']))
        {
            $this->name = $arrayValues ['name'];
        }

        // Metadata field is named 'data' when read from API.
        if (isset($arrayValues ['data']))
        {
            $this->metadata = $arrayValues ['data'];
            $this->setLoaded('metadata');
        }

        if ($fromDB)
        {
            // Metadata field is named 'metadata' when read from DB.
            if (isset($arrayValues ['metadata']))
            {
                $this->metadata = $arrayValues ['metadata'];
                $this->setLoaded('metadata');
            }

            //         Object type handler                      List name,              List class
            $this->updateListObjectsFromArray($arrayValues, 'followed-documents', API_Document::class);
            $this->updateListObjectsFromArray($arrayValues, 'followed-projects', API_Project::class);
            $this->updateListObjectsFromArray($arrayValues, 'followed-surnames', API_Surname::class);
            $this->updateListObjectsFromArray($arrayValues, 'my-albums', API_PhotoAlbum::class);
            $this->updateListObjectsFromArray($arrayValues, 'uploaded-documents', API_Document::class);
            $this->updateListObjectsFromArray($arrayValues, 'uploaded-photos', API_Photo::class);
            $this->updateListObjectsFromArray($arrayValues, 'uploaded-videos', API_Video::class);

            $this->updateListFromArray($arrayValues, 'followed-profiles', API_ListProfiles::class);
            $this->updateListFromArray($arrayValues, 'managed-profiles', API_ListProfiles::class);
            $this->updateListFromArray($arrayValues, 'max-family', API_ListProfiles::class);
            $this->updateListFromArray($arrayValues, 'my-labels', API_ListStrings::class);
        }
    }

    /**
     * Generate list from $arrayValues returned from API (or persistence).
     * @param array<string, string|array<string, mixed>> $arrayValues Server reply.
     * @param string $listName    API name for the list.
     * @param string $listClass   class of the LIST (API_ListProfiles or API_ListStrings).
     */
    private function updateListFromArray(array $arrayValues, string $listName, string $listClass): void
    {
        if (!empty($arrayValues [$listName]))
        {
            $propertyName = str_replace('-', '_', $listName);

            $this->$propertyName = $listClass::newFromArray($arrayValues [$listName], $listName);
            $this->setLoaded($listName);
        }
    }

    /**
     * Generate list of objects ({@link API_ListObjects}) from data from Server.
     * @param array<string, string|array<string, mixed>> $arrayValues Server reply.
     * @param string $listName    API name for the list.
     * @param string $objectClass  class of the objects IN the list.
     */
    private function updateListObjectsFromArray(array $arrayValues, string $listName, string $objectClass): void
    {
        if (!empty($arrayValues [$listName]))
        {
            $fullListName = "API_User/$listName";
            $propertyName = str_replace('-', '_', $listName);

            if (!$this->isFromDB())
            {
                $this->$propertyName = API_ListObjects::newFromArray($arrayValues [$listName], $objectClass, listName: $fullListName);
            }
            else
            {
                $this->$propertyName = API_ListObjects::newFromDB($arrayValues [$listName], $objectClass, listName: $fullListName);
            }

            $this->setLoaded($listName);
        }
    }

    /**
     * Factory method: Create object from array.
     * @internal
     * Since the API_User object is basically READ-ONLY the API has no need for
     * this function. It was created solely for "completion", and to READ the
     * user object from the persistence layer.
     * @param array<string, string|array<string, mixed>> $arrayValues Server reply.
     * @return API_User
     */
    public static function newFromArray(array $arrayValues, bool $fromDB = false): API_User
    {
        $user = new API_User();

        $user->updateFromArray($arrayValues, $fromDB);

        return $user;
    }

    //------------------------------------------------------
    // API calls.

    /**
     * Get name and & account type (basic, pro) of the user.
     */
    public static function get(): API_User
    {
        $API_Wrap = API_Wrapper::getInstance();

        $getUserURL = "/user";

        // -------------------------------
        $getArray = $API_Wrap->callAPI($getUserURL);
        // -------------------------------

        $user = new API_User();

        if ($getArray != null && array_key_exists('name', $getArray))
        {
            $user->accountType = $getArray ['account_type'];
            $user->name        = $getArray ['name'];
        }

        return $user;
    }

    /**
     * Add new user account.
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $gender    The user's gender: m, f, or u
     * @return bool  add was successful.
     */
    public static function add(
        string $email,
        string $firstName,
        string $lastName,
        string $gender,
    ): bool {
        $API_Wrap = API_Wrapper::getInstance();

        $addUserURL    = '/user/add';
        $addUserParams = [
            'email'     => $email,
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'gender'    => $gender
        ];

        // -------------------------------
        $addUserArray = $API_Wrap->callAPI_POST($addUserURL, $addUserParams);
        // -------------------------------

        $added = false;
        if ($addUserArray != null && array_key_exists('name', $addUserArray))
        {
            $added = true;
        }

        return $added;
    }

    /**
     * @param  array<string, string|array<string, mixed>> $data Custom metadata to be
     *                                                          stored by your application.
     * @return bool   success.
     */
    public function updateMetadata(array $data): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $updateURL    = '/user/update-metadata';
        $updateParams = [
            'data' => json_encode($data)
        ];

        // -------------------------------
        $updateArray = $API_Wrap->callAPI_POST($updateURL, $updateParams);
        // -------------------------------

        $updated = false;
        if (array_key_exists('data', $updateArray))
        {
            $this->metadata = $updateArray ['data'];
            $this->setLoaded('metadata');
            $updated = true;
        }

        return $updated;
    }

    /**
     * Get user metadata. If already loaded from DB or previous API call, returns cached array
     * unless $force is TRUE.
     *
     * @param bool $force Force an API round-trip to refresh metadata from server.
     * @return array<string, mixed>
     */
    public function getMetadata(bool $force = false): array
    {
        if ($this->isLoaded('metadata') && !$force)
        {
            return $this->metadata;
        }

        $API_Wrap = API_Wrapper::getInstance();

        $getMetadataURL = "/user/metadata";

        // -------------------------------
        $metadataArray = $API_Wrap->callAPI($getMetadataURL);
        // -------------------------------

        $this->metadata = [];

        if (isset($metadataArray ['data']))
        {
            $this->metadata = $metadataArray ['data'];
            $this->setLoaded('metadata');
        }

        return $this->metadata;
    }

    //------------------------------------------------------
    // API calls - Getters for all of the lists.

    private function getListObjects(
        string $API,
        string $objectClass,
        bool   $getAll,
        bool   $IDsOnly = true,
        bool   $force = false,
        int    $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListObjects {
        $propertyName = str_replace('-', '_', $API);

        if (!$this->isLoaded($API) || $force)
        {
            $this->$propertyName = new API_ListObjects("/user/$API", $objectClass, $getAll, $IDsOnly, true, pageSize: $pageSize);
            $this->setLoaded($API);
        }

        return $this->$propertyName;
    }

    private function getListProfiles(
        string $API,
        bool   $getAll,
        bool   $IDsOnly = true,
        bool   $force = false,
        int    $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        $propertyName = str_replace('-', '_', $API);

        if (!$this->isLoaded($API) || $force)
        {
            $this->$propertyName = new API_ListProfiles("/user/$API", $getAll, $IDsOnly, true, $pageSize);
            $this->setLoaded($API);
        }

        return $this->$propertyName;
    }

    /**
     * Get list of documents that the user follows.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListObjects list of {@link API_Document} objects.
     */
    public function getFollowedDocuments(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return $this->getListObjects('followed-documents', API_Document::class, $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of profiles that the user follows.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListProfiles list of profile-IDs or {@link API_Profile} objects,
     *                        depending on the $IDsOnly parameter.
     */
    public function getFollowedProfiles(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        return $this->getListProfiles('followed-profiles', $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of projects that the user follows.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListObjects list of {@link API_Project} objects.
     */
    public function getFollowedProjects(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return $this->getListObjects('followed-projects', API_Project::class, $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of surnames that the user follows.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListObjects list of {@link API_Surname} objects.
     */
    public function getFollowedSurnames(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return $this->getListObjects('followed-surnames', API_Surname::class, $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of profiles that the user manages.
     * There is currently a Server bug that returns a faulty reply when $IDsOnly
     * is FALSE. [instead of fields for a full profile, it sends just the GUID and
     * name fields.] So do NOT make such a call. It will return an empty list.
     *
     * Instead call it with $IDsOnly TRUE, and get the profiles individually using
     * the ID or GUID values.
     *
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListProfiles list of profile-IDs or {@link API_Profile} objects,
     *                        depending on the $IDsOnly parameter.
     */
    public function getManagedProfiles(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        return $this->getListProfiles('managed-profiles', $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of profiles that are in the user's "max-family", i.e. out to fourth cousins.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListProfiles list of profile-IDs or {@link API_Profile} objects,
     *                        depending on the $IDsOnly parameter.
     */
    public function getMaxFamily(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        return $this->getListProfiles('max-family', $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of the user's photo-albums.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListObjects list of {@link API_PhotoAlbum} objects.
     */
    public function getMyAlbums(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return $this->getListObjects('my-albums', API_PhotoAlbum::class, $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of documents that the user follows.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListStrings list of string labels associated with the user.
     */
    public function getMyLabels(bool $getAll = false, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListStrings
    {
        if (!$this->isLoaded('my-labels') || $force)
        {
            $this->my_labels = new API_ListStrings('/user/my-labels', $getAll, true, $pageSize);
            $this->setLoaded('my-labels');
        }

        return $this->my_labels;
    }

    /**
     * Get list of documents that the user uploaded.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListObjects list of {@link API_Document} objects.
     */
    public function getUploadedDocuments(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return $this->getListObjects('uploaded-documents', API_Document::class, $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of photos that the user uploaded.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListObjects list of {@link API_Photo} objects.
     */
    public function getUploadedPhotos(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return $this->getListObjects('uploaded-photos', API_Photo::class, $getAll, $IDsOnly, $force, $pageSize);
    }

    /**
     * Get list of videos that the user uploaded.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @param bool $force     Force an API call, to refresh the list.
     * @return API_ListObjects list of {@link API_Video} objects.
     */
    public function getUploadedVideos(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int  $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return $this->getListObjects('uploaded-videos', API_Video::class, $getAll, $IDsOnly, $force, $pageSize);
    }

    //------------------------------------------------------

    /**
     * Get number of entries in list, WITHOUT loading it.
     * @param string $listName
     * @return int
     */
    public function getListCount(string $listName): int
    {
        return API_ListBase::getListCount('user', $listName);
    }

    //------------------------------------------------------
    // Getter functions for data-fields.

    /**
     * Get the user's account type: basic, plus, pro.
     * @return string
     */
    public function getAccountType(): string
    {
        return $this->accountType;
    }

    /**
     * Get the user's name.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    private function isFromDB(): bool
    {
        return $this->fromDB;
    }

    private function setFromDB(): void
    {
        $this->fromDB = true;
    }
}
