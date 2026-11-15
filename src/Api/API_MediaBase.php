<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListObjects;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;

/**
 * API_MediaBase - This is the base-class for media objects: Document, Photo & Video.
 * It implements most of their API functions. It is deliberate that this class does
 * NOT have ANY data-members.
 *
 * @phpstan-consistent-constructor
 */
abstract class API_MediaBase extends API_Base
{
    /**
     * The media's attribution
     * @var string|NULL
     */
    protected ?string   $attribution;

    /**
     * The media's original content type
     * @var string|NULL
     */
    protected ?string   $content_type;

    /**
     * Epoch time. When the media was created.
     * @var string|NULL
     */
    protected ?string   $created_at;

    /**
     * The date OF the media.
     * @var API_Date|NULL
     */
    protected ?API_Date $date;

    /**
     * The media's description
     * @var string|NULL
     */
    protected ?string   $description;

    /**
     * The GUID
     * @var string|NULL
     */
    protected ?string   $guid;

    /**
     * The media's id
     * @var string|NULL
     */
    protected ?string   $id;

    /**
     * Location associated with the media.
     * @var API_Location|NULL
     */
    protected ?API_Location $location;

    /**
     * The media's title. This is ONLY field that MUST be set.
     * @var string
     */
    protected string   $title;

    /**
     * Epoch time. When the media was last updated.
     * @var string|NULL
     */
    protected ?string   $updated_at;

    /**
     * The API URL of the media
     * @var string|NULL
     */
    protected ?string   $url;

    // -----------------------------------------------------

    /**
     * List of API_Comments on document.
     * @var API_ListObjects|NULL
     */
    protected ?API_ListObjects   $comments;

    /**
     * URLs (or ids) of profiles tagged in the video.
     * @var API_ListProfiles|NULL
     */
    protected ?API_ListProfiles  $tags;

    //======================================================

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->attribution  = null;
        $this->content_type = null;
        $this->created_at   = null;
        $this->date         = null; // API_Date
        $this->description  = null;
        $this->guid         = null;
        $this->id           = null;
        $this->location     = null; // API_Location
        $this->title        = '';
        $this->updated_at   = null;
        $this->url          = null;

        // These members are API_ListObject and API_ListProfiles objects, and
        // are set to NULL until the specific API calls are made.

        $this->comments = null;
        $this->tags     = null;
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     * @param array<string, string|array<string, mixed>> $arrayValues
     * @param bool $fromDB
     */
    protected function updateFromArray(array $arrayValues, bool $fromDB = false): void
    {
        // This sets ONLY fields that were changed.

        $this->attribution  = $arrayValues['attribution'] ?? $this->attribution;
        $this->content_type = $arrayValues['content_type'] ?? $this->content_type;
        $this->created_at   = $arrayValues['created_at'] ?? $this->created_at;
        $this->description  = $arrayValues['description'] ?? $this->description;
        $this->guid         = $arrayValues['guid'] ?? $this->guid;
        $this->id           = $arrayValues['id'] ?? $this->id;
        $this->title        = $arrayValues['title'] ?? $this->title;
        $this->updated_at   = $arrayValues['updated_at'] ?? $this->updated_at;
        $this->url          = $arrayValues['url'] ?? $this->url;

        //--------------------------------------------------
        // Object fields.

        // Hydrate Objects: Accept Objects (from Mapper) or raw arrays (from API)

        if (isset($arrayValues['date']))
        {
            $this->date = API_Date::newFromArray($arrayValues['date']);
        }

        if (isset($arrayValues['location']))
        {
            $this->location = API_Location::newFromArray($arrayValues['location']);
        }

        if (isset($arrayValues ['tags']) && count($arrayValues ['tags']) > 0)
        {
            if (!$fromDB)
            {
                $parsedIDs = $this->makeArrayFromURLs($arrayValues ['tags']);
                $this->setLoaded('tags');
            }
            else
            {
                $parsedIDs = $arrayValues ['tags'];
                $this->setLoaded('tag-IDs');
            }

            $listName = static::class . '/tags';

            $this->tags = API_ListProfiles::newFromArray($parsedIDs, listName: $listName, IDsOnly: true, fromDB: $fromDB);
        }

        if ($fromDB)
        {
            // The $comments field is NOT sent as part of the generic reply of the API.
            // It must be requested separately, using the specific API call: getComments.
            // BUT this was added here for the persistence layer, which can add this value
            // in order to restore the comments from the DB.

            if (isset($arrayValues ['comments']))
            {
                $listName = static::class . '/comments';

                $this->comments = API_ListObjects::newFromDB($arrayValues ['comments'], API_Comment::class, $listName);
                $this->setLoaded('comments');
            }
        }
    }

    /**
     * Factory method: Create instance from array
     * @param array<string, int|string|array <string, int>> $arrayValues
     * @param bool $fromDB
     * @return static
     */
    public static function newFromArray(array $arrayValues, bool $fromDB = false): static
    {
        $media = new static();

        $media->updateFromArray($arrayValues, $fromDB);

        return $media;
    }

    // =====================================================
    // API calls.

    /**
     * Get media object information from Server.
     *
     * @param string $ID
     *
     * @return NULL|static
     */
    protected static function get(string $ID): ?static
    {
        $API_Wrap = API_Wrapper::getInstance();

        $request_URL = "/$ID"; // Example of ID format: "document-IDNum".

        // -------------------------------
        $getReply = $API_Wrap->callAPI($request_URL);
        // -------------------------------

        $mediaObject = null;

        if (array_key_exists('id', $getReply))
        {
            $mediaObject = self::newFromArray($getReply);
        }

        return $mediaObject;
    }

    /**
     * Add user comment on media-object. Only text is mandatory.
     * @param string $text
     * @param string $title
     * @return bool TRUE on success
     */
    protected function comment(string $text, ?string $title = null): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $commentURL    = '/' . $this->getID() . '/comment';
        $commentParams = [
            'text'  => $text,
            'title' => $title
        ];

        // -------------------------------
        $commentReply = $API_Wrap->callAPI_POST($commentURL, $commentParams);
        // -------------------------------

        $commentAdded = false;

        if ($commentReply != null)
        {
            if (array_key_exists('results', $commentReply) // Array of comments.
                || array_key_exists('created_at', $commentReply))   // Key from Comment => only one.
            {
                $commentAdded = true;
            }
        }

        return $commentAdded;
    }

    /**
     * Delete this media-object.
     * @return bool TRUE on success.
     */
    protected function delete(): bool
    {
        return self::deleteID($this->getID());
    }

    /**
     * Delete media-object by ID.
     * @return bool TRUE on success.
     */
    public static function deleteID(string $ID): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $deleteURL    = "/$ID/delete";
        $deleteParams = [];

        // -------------------------------
        $deleteReply = $API_Wrap->callAPI_POST($deleteURL, $deleteParams);
        // -------------------------------

        if (array_key_exists('result', $deleteReply) && $deleteReply['result'] == 'Deleted')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Tag profile on media-object.
     * @param string $profileID Profile ID.
     * @return bool TRUE on success.
     */
    protected function tag(string $profileID): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $tagURL    = '/' . $this->getID() . '/tag/' . $profileID;
        $tagParams = [];

        // -------------------------------
        $tagArray = $API_Wrap->callAPI_POST($tagURL, $tagParams);
        // -------------------------------

        if (isset($tagArray ['results']))
        {
            $this->tags = API_ListProfiles::newFromArray($tagArray, listName: 'API_Document/tags', IDsOnly: false, fromDB: false);
            $this->setLoaded('tags');
        }

        return $this->findTaggedProfile($tagArray, $profileID);
    }

    /**
     * Remove tagged profile from media-object.
     * @param string $profileID Profile ID.
     * @return bool TRUE on success.
     */
    protected function untag(string $profileID): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $untagURL    = '/' . $this->getID() . '/untag/' . $profileID;
        $untagParams = [];

        // -------------------------------
        $untagArray = $API_Wrap->callAPI_POST($untagURL, $untagParams);
        // -------------------------------

        if ($untagArray != null && array_key_exists('id', $untagArray))
        {
            static::updateFromArray($untagArray);
        }

        return !($this->findTaggedProfile($untagArray, $profileID));
    }

    /**
     * Update values of this media-object.
     * After being created only a few fields can be updated. This function
     * will only update the paramters that are not NULL. NONE of the
     * parameters are mandatory. The $labels parameter applies only to
     * documents. Other classes calling this function need to pass NULL.
     *
     * @param  string   $title
     * @param  API_Date $date
     * @param  string   $description
     * @param  NULL|string|array<string> $labels applies only to document objects.
     * @return bool TRUE on success.
     */
    protected function _update(
        ?string   $title = null,
        ?API_Date $date = null,
        ?string   $description = null,
        array|string|null $labels = null,
    ): bool {
        $API_Wrap = API_Wrapper::getInstance();

        $updateURL    = '/' . $this->getID() . '/update';
        $updateParams = [];

        // Process parameters.

        if ($date != null)
        {
            $updateParams['date'] = $date->makeParamArray();
        }

        if ($description != null)
        {
            $updateParams['description'] = $description;
        }

        if ($title != null)
        {
            $updateParams['title'] = $title;
        }

        // Labels paramter applies ONLY to documents.

        if ($labels != null)
        {
            // Convert values to comma separated string.
            if (is_array($labels))
            {
                $vals   = array_values($labels);
                $labels = implode(',', $vals);
            }

            $updateParams['labels'] = $labels;
        }

        // -------------------------------
        $updateArray = $API_Wrap->callAPI_POST($updateURL, $updateParams);
        // -------------------------------

        if ($updateArray != null && array_key_exists('id', $updateArray))
        {
            static::updateFromArray($updateArray);

            return true;
        }
        else
        {
            return false;
        }
    }

    // -----------------------------------------------------

    /**
     * Get list of comments written on the document.
     * @param bool $getAll When set, the returned list will include ALL of
     * the comments. When false, will only return the first page. Since, by
     * default, a page in 50 items long, it is unlikely there will be that
     * many comments.
     * @return API_ListObjects
     */
    protected function getComments(
        bool $getAll = false,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListObjects {
        if ($force || !$this->isLoaded('comments'))
        {
            $getCommentsURL = '/' . $this->getID() . '/comments';

            $this->comments = new API_ListObjects($getCommentsURL, API_Comment::class, $getAll, IDsOnly: false, fromAPICall: true, pageSize: $pageSize);

            $this->setLoaded('comments');
        }

        return $this->comments;
    }

    /**
     * Get list of profile-IDs tagged on the document.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param bool $IDsOnly   Instead of returning API_Profile objects, returns
     *                        only their ID and GUID values.
     * @param int  $pageSize  Number of entries in a single page.
     * @return API_ListProfiles
     */
    protected function getTags(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        if ($force
            || (!$this->isLoaded('tags')) && !$this->isLoaded('tag-IDs'))
        {
            $getTagsURL = '/' . $this->getID() . '/tags';

            $this->tags = new API_ListProfiles(
                listName: $getTagsURL,
                getAll: $getAll,
                IDsOnly: $IDsOnly,
                fromAPICall: true,
                pageSize: $pageSize,
            );
            $this->setLoaded('tags');
        }

        return $this->tags;
    }

    // =====================================================

    /**
     * Search for profileID in reply from Server.
     * This is the logic for BOTH {@link API_MediaBase::tag()} and
     * {@link API_MediaBase::untag()}, in which case the untag wants
     * a FALSE response.
     * @ param  array <string, array <string, string>> $tagArray  API reply array, as received from Server.

     * @param array{results?: array<string, array{id?: string|int}>} $tagArray API reply array, as received from Server.
     * @param  string $profileID ID to search for.
     * @return bool   TRUE when profile-ID is found in reply.
     */
    protected function findTaggedProfile(array $tagArray, string $profileID): bool
    {
        if (empty($tagArray['results']))
        {
            return false;
        }

        // The API call returns an array of ALL tagged profiles.
        // Loop through array to check if our profile is present.
        foreach ($tagArray['results'] as $taggedProfile)
        {
            if (isset($taggedProfile['id']) && $taggedProfile['id'] == $profileID)
            {
                return true;
            }
        }

        // When called for untag, the result of loop should be FALSE.
        return false;
    }

    /**
     * Convert array of URLs into array of ID-GUID pairs.
     * When creating the 'tags' field directly from the Server, as PART of the
     * Media objects, the Server returns an array of URLs to the profiles. In
     * order to create an API_ListProfiles, we need to transform this array
     * into an array in which each entry is an array with ID & GUID values.
     * @param  array <string> $arrayValues  The array of the 'tags' field as recieved
     *                             from the Server.
     * @return array <string>
     */
    protected function makeArrayFromURLs(array $arrayValues): array
    {
        // Strip first part of URL, leaving only profile-ID.

        $profileIDs = [];
        foreach ($arrayValues as $profileURL)
        {
            $profileID     = str_replace('https://www.geni.com/api/', '', $profileURL);
            $profileIDs [] = $profileID;
        }

        return $profileIDs;
    }

    /**
     * Set a collection loaded flag.
     * Override base class method to handle resetting 'tag-IDs' collection
     * on loading the full 'tags'.
     * @param string $key Collection 'name'.
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Api\API_Base::setLoaded()
     */
    protected function setLoaded(string $key): void
    {
        parent::setLoaded($key);

        if ($key === 'tags' && $this->isLoaded('tag-IDs'))
        {
            $this->clearLoaded('tag-IDs');
        }
    }

    //=====================================================
    // Getter functions.
    // There are NO setter functions. The limited number of changes are
    // made using the update function.

    public function getAttribution(): ?string
    {
        return $this->attribution;
    }

    public function getContentType(): ?string
    {
        return $this->content_type;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getDate(): ?API_Date
    {
        return $this->date;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getGUID(): ?string
    {
        return $this->guid;
    }

    public function getID(): ?string
    {
        return $this->id;
    }

    public function getLocation(): ?API_Location
    {
        return $this->location;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function getURL(): ?string
    {
        return $this->url;
    }
}
