<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 *
 * @author shmuel
 */
class API_PhotoAlbumMocker extends API_BaseMocker
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    //=====================================================
    // API calls.

    /**
     * GET /photo_album-1
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        $albumID     = $this->getSubject();
        $albumObject = $this->getTestObject($albumID);

        if ($albumObject == [])
        {
            $albumObject = $this->createObjectByID($albumID);

            $this->setSubjectObject($albumObject);
        }

        $albumPhotos = $this->findPhotos($albumID);

        $this->updateList($albumID, 'photos', $API_Params, 'photo', $albumPhotos);

        return $albumObject;
    }

    /**
     * POST /photo_album/add
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add(array $API_Params, array $urlActions = []): array
    {
        $albumObject = $this->createObject($API_Params);

        $this->setSubject($albumObject ['id']);
        $this->setSubjectObject($albumObject);

        return $albumObject;
    }

    /**
     * GET /photo_album-1/photos
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function photos(array $API_Params, array $urlActions = []): array
    {
        // Pass requestData (params) and actions along to the list generator

        $photosList = $this->getList($this->getSubject(), 'photos');

        $photosPage = [];
        if ($photosList != null)
        {
            $photosPage = $photosList->generatePageReply($API_Params);
        }

        return $photosPage;

        //generateListReply ($API_Params, NULL, 'photo');
    }

    /**
     * POST /photo_album-1/update
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function update(array $API_Params, array $urlActions = []): array
    {
        $albumObject = $this->getSubjectObject();

        $albumObject ['name'] = $API_Params ['name'] ?? $albumObject ['name'];

        if (isset($API_Params ['description']))
        {
            $albumObject ['description'] = $API_Params ['description'];
        }

        $this->setSubjectObject($albumObject);

        return $albumObject;
    }

    //------------------------------------------------------

    /**
     * Return all photo objects from the specific album.
     * @param string $albumID
     * @return array <array <string, mixed|array <string, mixed>>>
     */
    protected function findPhotos(string $albumID): array
    {
        $albumPhotos = [];

        $allPhotos = $this->getTestObjectsByType('photo');

        foreach ($allPhotos as $photo)
        {
            if ($photo ['album_id'] == $albumID)
            {
                $albumPhotos [] = $photo;
            }
        }

        return $albumPhotos;
    }

    /**
     * Create object from /add call parameters.
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function createObject(array $API_Params): array
    {
        // Generate deterministic ID.

        $name = $API_Params ['name'];
        $ID   = FactoryBase::generatePrefixedId('album', $name);

        $album = [
            'cover_photo'  => null,
            'id'           => $ID,
            'name'         => $name,
            'photos_count' => 0,
            'url'          => 'https://www.geni.com/api/' . $ID,
        ];

        if (isset($API_Params ['description']))
        {
            $album ['description'] = $API_Params ['description'];
        }


        return $album;
    }

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array <string, mixed|array <string, mixed>>
     */
    public function createObjectByID(string $ID): array
    {
        $name = 'Test Album ' . date('Y-m-d');

        $album = [
            'cover_photo'  => null,
            'description'  => "Mocked $name",
            'id'           => $ID,
            'name'         => $name,
            'photos_count' => 0,
            'url'          => 'https://www.geni.com/api/' . $ID,
        ];

        return $album;
    }
}
