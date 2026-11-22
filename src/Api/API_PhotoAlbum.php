<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListObjects;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;

/**
 * API_PhotoAlbum - a collection of {@link API_Photo} objects.
 * This class has only TWO data-members that can be changed: $description & $name
 * (by calling the {@link API_PhotoAlbum::update} function). The other data-members
 * are set by the Server.
 *
 * There is NO API to delete a photo-album. This must be done manually, via the website.
 * There is NO API to set the cover-photo.
 *
 * Photos are added to albums, when they are created (see {@link API_Photo::add}), or manually.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=photo_album&version=1 API_PhotoAlbum}
 */
class API_PhotoAlbum extends API_Base
{
    /**
     *  The photo album's description.
     * @var string
     */
    private string             $description;

    /**
     *  The photo album's node id.
     * @var string
     */
    private string             $id;

    /**
     *  The photo album's name.
     * @var string
     */
    private string             $name;

    /**
     *  The number of photos in the photo album.
     * @var int
     */
    private int                $photos_count;

    /**
     *  URL to access the photo album.
     * @var string
     */
    private string             $url;

    /**
     * List of photo IDs with photo objects (or just a flag).
     * @var ?API_ListObjects
     */
    private ?API_ListObjects   $photos;

    /**
     *  All sizes of the photo album's cover photo.
     * @var ?API_PhotoImageSizeMap
     */
    private ?API_PhotoImageSizeMap $cover_photo;

    //=====================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->cover_photo  = null;
        $this->description  = '';
        $this->id           = '';
        $this->name         = '';
        $this->photos_count = 0;
        $this->url          = '';

        $this->photos = null;
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     * @param array <string, string|array <string, string|mixed>> $arrayValues
     * @param bool $fromDB
     */
    public function updateFromArray(array $arrayValues, bool $fromDB = false): void
    {
        // This sets ONLY fields that were changed.
        // The photos list is NOT updated, because it is not included
        // in the array returned by the general API calls.

        $this->description  = $arrayValues ['description'] ?? '';
        $this->id           = $arrayValues ['id'] ?? '';
        $this->name         = $arrayValues ['name'] ?? '';
        $this->photos_count = $arrayValues ['photos_count'] ?? 0;
        $this->url          = $arrayValues ['url'] ?? '';

        if (isset($arrayValues ['cover_photo']))
        {
            $this->cover_photo = API_PhotoImageSizeMap::newFromArray($arrayValues ['cover_photo']);
        }

        if ($fromDB)
        {
            if (isset($arrayValues ['photos']))
            {
                $listName = '/' . $this->getID() . '/photos';

                $this->photos = API_ListObjects::newFromDB($arrayValues ['photos'], API_Photo::class, $listName);
                $this->setLoaded('photos');
            }
        }
    }

    /**
     * Factory method: Create instance from array
     * @param  array <string, string|array <string, string>> $arrayValues
     * @param  bool $fromDB
     * @return API_PhotoAlbum
     */
    public static function newFromArray(array $arrayValues, bool $fromDB = false): API_PhotoAlbum
    {
        $photoAlbum = new API_PhotoAlbum();

        $photoAlbum->updateFromArray($arrayValues, $fromDB);

        return $photoAlbum;
    }

    //=====================================================
    // API calls.

    /**
     * Get photo-album from Server.
     * @param string $id
     * @return API_PhotoAlbum
     */
    public static function get(string $id): ?API_PhotoAlbum
    {
        $API_Wrap = API_Wrapper::getInstance();

        $photoAlbumURL = "/$id";

        // -------------------------------
        $photoAlbumArray = $API_Wrap->callAPI($photoAlbumURL);
        // -------------------------------

        $photoAlbum = null;
        if (array_key_exists('id', $photoAlbumArray))
        {
            $photoAlbum = API_PhotoAlbum::newFromArray($photoAlbumArray);
        }

        return $photoAlbum;
    }

    /**
     * Create new photo-album.
     * @param  string $name
     * @param  string $description
     * @return API_PhotoAlbum|NULL
     */
    public static function add(string $name, string $description = ''): ?API_PhotoAlbum
    {
        $API_Wrap = API_Wrapper::getInstance();

        $addAlbumURL   = '/photo_album/add';
        $addAlbumParam = [
            'name' => $name
        ];

        if ($description != '')
        {
            $addAlbumParam ['description'] = $description;
        }

        // -------------------------------
        $addAlbumArray = $API_Wrap->callAPI_POST($addAlbumURL, $addAlbumParam);
        // -------------------------------

        if ($addAlbumArray != null && array_key_exists('id', $addAlbumArray))
        {
            $album = API_PhotoAlbum::newFromArray($addAlbumArray);

            return $album;
        }
        else
        {
            return  null;
        }
    }

    /**
     * Get list of photo objects included in album.
     * @param bool $getAll    Get the complete list in one read, rather than a
     *                        page at a time (that the user can page through).`
     * @param int  $pageSize  Number of entries in a single page.
     * @return API_ListObjects list of {@link API_Photo} objects.
     */
    public function getPhotos(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        if ($force || !$this->isLoaded('photos'))
        {
            $getPhotosURL = '/' . $this->getID() . '/photos';

            $this->photos = new API_ListObjects($getPhotosURL, API_Photo::class, $getAll, $IDsOnly, fromAPICall: true, pageSize: $pageSize);
            $this->setLoaded('photos');
        }

        return $this->photos;
    }

    /**
     * Update photo-album name & description.
     * @param  string $name
     * @param  string $description
     * @return bool   TRUE on success.
     */
    public function update(?string $name = '', ?string $description = ''): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        if ($name == '' && $description == '')
        {
            // Nothing to update.
            return false;
        }

        $updateURL   = '/' . $this->getID() . '/update';
        $updateParam = [];

        if ($name != '')
        {
            $updateParam ['name'] = $name;
        }

        if ($description != '')
        {
            $updateParam ['description'] = $description;
        }

        // -------------------------------
        $updateArray = $API_Wrap->callAPI_POST($updateURL, $updateParam);
        // -------------------------------

        if ($updateArray != null && array_key_exists('id', $updateArray))
        {
            $this->updateFromArray($updateArray);

            return true;
        }
        else
        {
            return  false;
        }
    }

    /**
     * Returns true if the count of internal photo objects
     * matches the metadata count from the API.
     */
    public function fullyLoaded(): bool
    {
        // If API says 0, we are technically "fully loaded" even with an empty array
        if ($this->getPhotosCount() === 0)
        {
            return true;
        }

        // Otherwise, check if our internal collection is populated
        return count($this->getPhotos()) >= $this->getPhotosCount();
    }

    //=====================================================
    // This class does not have ANY setter methods.

    public function getCoverPhoto(): API_PhotoImageSizeMap
    {
        return $this->cover_photo;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhotosCount(): int
    {
        return $this->photos_count;
    }

    public function getURL(): string
    {
        return $this->url;
    }
}
