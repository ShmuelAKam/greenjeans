<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListObjects;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;

/**
 * API_Photo - describes a photo, including dates & location, user comments made
 * on the photo and a list of the people tagged in it. This class derives from
 * {@link API_MediaBase}, which implements most of its API calls.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=photo&version=1 API_Photo}
 */
class API_Photo extends API_MediaBase
{
    /**
     * The id of the album where the photo is stored.
     * @var string|NULL
     */
    private ?string $album_id;

    /**
     * URLs to the photo's sizes.
     * @var API_PhotoImageSizeMap|NULL
     */
    private ?API_PhotoImageSizeMap $sizes;

    //======================================================

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->album_id = null;
        $this->sizes    = null; // API_PhotoImageSizeMap
    }

    /**
     * {@inheritDoc}
     * Hydration logic: Set fields based on API or Database array keys.
     * @see \ShmuelAKam\Greenjeans\Api\API_MediaBase::updateFromArray()
     */
    public function updateFromArray(array $arrayValues, bool $fromDB = false): void
    {
        parent::updateFromArray($arrayValues, $fromDB);

        // This sets ONLY fields that were changed.

        $this->album_id = $arrayValues['album_id'] ?? $this->album_id;

        if (isset($arrayValues['sizes']))
        {
            $this->sizes = API_PhotoImageSizeMap::newFromArray($arrayValues['sizes']);
        }
    }

    /**
     * Creates array to send to Server in API calls.
     * @return array<string, string>
     */
    public function makeParamArray(): array
    {
        static $MEMBER_FIELDS = [
            'album_id', 'attribution', 'content_type', 'created_at', 'description',
            'guid',     'id',          'title',        'updated_at', 'url',
            ];

        $paramArray = [];

        foreach ($MEMBER_FIELDS as $member)
        {
            if ($this->$member != null && $this->$member != '')
            {
                $paramArray [$member] = $this->$member;
            }
        }

        if ($this->date != null)
        {
            $paramArray ['date'] = $this->date->makeParamArray();
        }

        if ($this->location != null)
        {
            $paramArray ['location'] = $this->location->makeParamArray();
        }

        if ($this->sizes != null)
        {
            $paramArray ['sizes'] = $this->sizes->makeParamArray();
        }

        if ($this->tags != null)
        {
            $paramArray ['tags'] = $this->tags->getList();
        }

        return $paramArray;
    }

    // =====================================================
    // API calls.

    /**
     * Add new photo file.
     * @param string   $title       Photo's title. The ONLY mandatory parameter.
     * @param string   $album_id
     * @param API_Date $date        Photo's date in JSON form
     * @param string   $description Description of photo.
     * @param string   $fileBinary  Binary File to create photo from
     *
     * @param string   $fileName
     * @param string   $fileType
     * @return self|NULL
     */
    public static function add(
        string    $title,
        ?string   $album_id = null,
        ?API_Date $date = null,
        ?string   $description = '',
        string    $fileBinary = null,
        string    $fileName = null,
        string    $fileType = null,
    ): ?self {
        $API_Wrap = API_Wrapper::getInstance();

        $addURL   = '/photo/add';
        $addParam = [];

        $addParam ['title'] = $title;   // Mandatory parameter.

        if ($album_id != null)
        {
            $addParam ['album_id'] = $album_id;
        }

        if ($date != null)
        {
            $addParam ['date'] = $date->toJSON();
        }

        if ($description != null)
        {
            $addParam ['description'] = $description;
        }

        //-------------------------------
        $addArray = $API_Wrap->callAPI_POST_WithBinary($addURL, $addParam, $fileBinary, $fileName, $fileType);
        //-------------------------------

        $photo = null;
        if ($addArray != null && array_key_exists('id', $addArray))
        {
            $photo = self::newFromArray($addArray);
        }

        return $photo;
    }

    /**
     * Update values of this photo.
     * After being created, only some fields can be updated. This function will
     * only update the paramters that are not NULL. NONE of the parameters are
     * mandatory.
     *
     * @param  string   $title
     * @param  API_Date $date   Date field OF the document (NOT the date of the
     *                          update. That is the updated_at field).
     * @param  string   $description
     * @return bool     TRUE on success.
     */
    public function update(?string  $title = null, ?API_Date $date = null, ?string $description = null): bool
    {
        // Set last parameter, $labels, to NULL. It's not relevant for photos.
        return parent::_update($title, $date, $description, null);
    }

    //-------------------------------------------------
    // Inherited functions - The functions call the base class functions,
    // so that the complete API of the class is visible in one place.

    /**
     * {@inheritDoc}
     */
    public static function get(string $ID): ?static
    {
        return parent::get($ID);
    }

    /**
     * {@inheritDoc}
     */
    public function comment(string $text, ?string $title = null): bool
    {
        return parent::comment($text, $title);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(): bool
    {
        return parent::delete();
    }

    /**
     * {@inheritDoc}
     */
    public function tag(string $profileID): bool
    {
        return parent::tag($profileID);
    }

    /**
     * {@inheritDoc}
     */
    public function untag(string $profileID): bool
    {
        return parent::untag($profileID);
    }

    //-------------------------------------------------
    // Get functions for list values. The API call is made by the constructor
    // of the list base class.

    /**
     * {@inheritDoc}
     */
    public function getComments(bool $getAll = false, bool $force = false, int $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        return parent::getComments($getAll, $force, $pageSize);
    }

    /**
     * {@inheritDoc}
     */
    public function getTags(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        return parent::getTags($getAll, $IDsOnly, $force, $pageSize);
    }

    //=====================================================
    // Getter functions.
    // There are NO setter functions. The limited number of changes are
    // made using the update function.

    public function getAlbumID(): ?string
    {
        return $this->album_id;
    }

    public function getSizes(): ?API_PhotoImageSizeMap
    {
        return $this->sizes;
    }
}
