<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_PhotoAlbum;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotoAlbums;

/**
 * Factory for creating test API_PhotoAlbum objects and parameter arrays
 * for DB_PhotoAlbumMapper unit tests without making remote API calls.
 */
class FactoryMapperPhotoAlbums extends FactoryPhotoAlbums
{
    public const ALBUM_LIST = [
        //                    Name             Description            Photos Count
        'ALL fields'     => [ 'Vacation 2026', 'Trip to Europe',      3 ],
        'name only'      => [ 'Family',        null,                  0 ],
        'description'    => [ 'Archives',      'Historical documents', 2 ],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function makeMapAlbum(
        string  $name = '',
        ?string $description = null,
        int     $photosCount = 0,
        ?string $id = null,
        bool    $full = true,
    ): API_PhotoAlbum {
        $albumParam = $this->makeParamArray($name, $description, $photosCount, id: $id, full: $full);

        return API_PhotoAlbum::newFromArray($albumParam, fromDB: false);
    }

    /**
     * @param string $name
     * @param string $description
     * @param int $photosCount
     * @param string $id
     * @param bool $full
     * @return array <string, string|int|array <string, string>|array<\ShmuelAKam\Greenjeans\Api\API_Photo>>
     */
    public function makeParamArray(
        string  $name = '',
        ?string $description = null,
        int     $photosCount = 0,
        ?string $id = null,
        bool    $full = true,
    ): array {
        /** @var FactoryMapperPhotos $photoMapFactory */
        global $photoMapFactory;

        $albumID = ($id !== null && $id !== '')
            ? $id
            : FactoryBase::generatePrefixedId('album');

        $albumParam ['id']           = $albumID;
        $albumParam ['name']         = $name;
        $albumParam ['photos_count'] = $photosCount;
        $albumParam ['url']          = 'https://www.geni.com/api/album/' . $albumID;

        if ($description !== null)
        {
            $albumParam ['description'] = $description;
        }

        if ($full)
        {
            $albumParam ['cover_photo'] = $photoMapFactory->makeSizeMapArray($albumID, 'cover.jpg');

            if ($photosCount > 0)
            {
                $albumParam ['photos'] = $photoMapFactory->makePhotoList($albumID, $photosCount);
            }
        }

        return $albumParam;
    }
}
