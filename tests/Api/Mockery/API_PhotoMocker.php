<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotos;

class API_PhotoMocker extends API_MediaMocker
{
    /**
     * These files EXIST on the Geni Server.
     * @var array <string, string>
     */
    private const PHOTOS_ON_SERVER = [
        'photo-51190389' => 'File-SDK-1.jpg',
        'photo-51190390' => 'File-SDK-2.jpg',
        'photo-51190391' => 'File-SDK-3.jpg',
        'photo-51190392' => 'File-SDK-4.png',
        'photo-51190393' => 'File-SDK-5.png',
        'photo-51190394' => 'File-SDK-6.png',

        'photo-52067799' => 'File-SDK-9.gif',

        'photo-51190395' => 'File-SDK-Donatello-David.jpg',
        'photo-51190396' => 'File-SDK-Michelangelo-Creation.jpg',
        'photo-51190397' => 'File-SDK-Mona-Lisa.jpg',
        'photo-51190400' => 'File-SDK-Raphael-Athens.jpg',

        "photo-52067762" => "Title",    // <= the yellow dot.
    ];

    //======================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setObjectType('photo');
    }

    //======================================================
    // API calls.

    /**
     * GET photo/
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        $photo = parent::get($API_Params, $urlActions);

        $photo ['album_id'] = $photoFactory->getAlbumGUID();

        $this->setSubjectObject($photo);

        return $photo;
    }

    /**
     * POST photo/add
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add(array $API_Params, array $urlActions = []): array
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        $VALID_FIELDS = ['album_id', 'date', 'description', 'file', 'title'];

        $photo = parent::_add($API_Params, $VALID_FIELDS);

        // The API returns the GUID even when the ID is sent in.
        // So this field needs to be set directly.

        $photo ['album_id'] = $photoFactory->getAlbumGUID();

        return $photo;
    }

    //------------------------------------------------------

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_MediaMocker::getFileList()
     * @return array <string, string|TRUE>
     */
    protected function getFileList(): array
    {
        return self::PHOTOS_ON_SERVER;
    }
}
