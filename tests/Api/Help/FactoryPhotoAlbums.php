<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_PhotoAlbum;

class FactoryPhotoAlbums extends FactoryBase
{
    private int $albumCount;

    public function __construct()
    {
        // The PhotoAlbum API does NOT have a delete function.
        // So there can not be a shredder. OOPS!

        parent::__construct(ShredderDummy::class);

        $this->albumCount = 0;
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function makePhotoAlbum(string $name): API_PhotoAlbum
    {
        $album = API_PhotoAlbum::add($name);

        return $album;
    }

    public function makeName(): string
    {
        $name = 'SDK-Album ' . str_pad((string) $this->albumCount++, 4, '0', STR_PAD_LEFT);

        return $name;
    }
}
