<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\API_PhotoAlbum;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_PhotoAlbumMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_PhotoMapper;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperPhotoAlbums;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperPhotos;

/**
 * DB_PhotoAlbumMapperTest
 * Unit tests for DB_PhotoAlbumMapper persistence layer.
 */
class DB_PhotoAlbumMapperTest extends DB_MapperTestBase
{
    private DB_PhotoAlbumMapper $mapper;

    /**
     * TODO add tests with photo operations.
     * @phpstan-ignore-next-line */
    private DB_PhotoMapper      $photoMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper      = $this->getMapper(DB_PhotoAlbumMapper::class);
        $this->photoMapper = $this->getMapper(DB_PhotoMapper::class);
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string|null>>
     */
    public static function provideAlbumUpdates(): array
    {
        return [
            'Update Name & Desc' => ['Updated Album Name', 'New Description'],
            'Update Name Only'   => ['Updated Album Name', null],
        ];
    }

    /**
     * @return array <string, array <string|int|NULL>>
     */
    public static function provideAlbum(): array
    {
        return FactoryMapperPhotoAlbums::ALBUM_LIST;
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_PhotoAlbum instances have identical field values,
     * cover size sub-objects, and photo lists without triggering SDK network calls.
     */
    protected function assertEqualsPhotoAlbum(API_PhotoAlbum $expected, API_PhotoAlbum $actual, string $message = ''): void
    {
        // 1. Direct scalar properties on API_PhotoAlbum
        self::assertEquals($expected->getID(), $actual->getID(), $message . ' [id mismatch]');
        self::assertEquals($expected->getName(), $actual->getName(), $message . ' [name mismatch]');
        self::assertEquals($expected->getDescription(), $actual->getDescription(), $message . ' [description mismatch]');
        self::assertEquals($expected->getPhotosCount(), $actual->getPhotosCount(), $message . ' [photos_count mismatch]');
        self::assertEquals($expected->getURL(), $actual->getURL(), $message . ' [url mismatch]');

        // 2. Sub-Object: Cover Photo Size Map
        $this->assertEqualsSizeMap($expected->getCoverPhoto(), $actual->getCoverPhoto(), $message);

        // 3. Relational Collection: Photos (Guarded against API network calls)
        $expectedPhotosLoaded = $expected->isLoaded('photos');
        $actualPhotosLoaded   = $actual->isLoaded('photos');

        self::assertEquals($expectedPhotosLoaded, $actualPhotosLoaded, $message . ' [photos isLoaded state mismatch]');

        if ($expectedPhotosLoaded)
        {
            $expectedPhotos = array_map(
                fn(API_Profile $p) => $p->makeParamArray(allFields: false, forDB: false),
                $expected->getPhotos()->getList(),
            );
            $actualPhotos = array_map(
                fn(API_Profile $p) => $p->makeParamArray(allFields: false, forDB: false),
                $actual->getPhotos()->getList(),
            );

            self::assertEquals($expectedPhotos, $actualPhotos, $message . ' [photos list mismatch]');
        }
    }

    /**
     * Asserts that two API_PhotoImageSizeMap objects have identical size URLs.
     */
    protected function assertEqualsSizeMap(?API_PhotoImageSizeMap $expected, ?API_PhotoImageSizeMap $actual, string $message = ''): void
    {
        if ($expected === null || $actual === null)
        {
            self::assertNull($expected, $message . ' [expected size map to be null]');
            self::assertNull($actual, $message . ' [expected actual size map to be null]');
            return;
        }

        self::assertEquals($expected->getLarge(), $actual->getLarge(), $message . ' [large size url mismatch]');
        self::assertEquals($expected->getMedium(), $actual->getMedium(), $message . ' [medium size url mismatch]');
        self::assertEquals($expected->getOrigin(), $actual->getOrigin(), $message . ' [origin size url mismatch]');
        self::assertEquals($expected->getPrint(), $actual->getPrint(), $message . ' [print size url mismatch]');
        self::assertEquals($expected->getSmall(), $actual->getSmall(), $message . ' [small size url mismatch]');
        self::assertEquals($expected->getThumb(), $actual->getThumb(), $message . ' [thumb size url mismatch]');
        self::assertEquals($expected->getThumb2(), $actual->getThumb2(), $message . ' [thumb2 size url mismatch]');
    }

    // =====================================================
    // Basic Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations for an API_PhotoAlbum object.
     * @dataProvider provideAlbum
     */
    public function testCreateAndRead(string $name, ?string $description, int $photosCount): void
    {
        /** @var FactoryMapperPhotoAlbums $albumMapFactory */
        $albumMapFactory = FactoryMapperBase::getFactory(API_PhotoAlbum::class);

        $album   = $albumMapFactory->makeMapAlbum($name, $description, $photosCount, full: true);
        $albumID = $album->getID();

        //-------------------------------
        $savedID = $this->mapper->save($album, fullSave: true);

        $retrieved = $this->mapper->read($albumID, fullRead: true);
        //-------------------------------

        $this->assertEquals($albumID, $savedID);
        $this->assertNotNull($retrieved);
        $this->assertEqualsPhotoAlbum($album, $retrieved);
    }

    /**
     * Verifies null response when reading a non-existent album ID.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $retrieved = $this->mapper->read('album-non-existent-id', fullRead: false);

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Scalar Updates

    /**
     * @dataProvider provideAlbumUpdates
     */
    public function testUpdateAlbumScalarFields(?string $newName, ?string $newDesc): void
    {
        /** @var FactoryMapperPhotoAlbums $albumMapFactory */
        $albumMapFactory = FactoryMapperBase::getFactory(API_PhotoAlbum::class);

        /** @var FactoryMapperPhotos      $photoMapFactory */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        $albumID = FactoryBase::generatePrefixedId('album');
        $photos  = $photoMapFactory->makePhotoList($albumID, 3);

        $initialAlbum = $albumMapFactory->makeMapAlbum('Original Name', 'Original Desc', photosCount: 0, full: true);
        $albumID      = $initialAlbum->getID();

        $this->mapper->save($initialAlbum, fullSave: true);

        // Build updated object
        $params       = $albumMapFactory->makeParamArray($newName ?? 'Original Name', $newDesc, photosCount: 0, id: $albumID);
        $updatedAlbum = API_PhotoAlbum::newFromArray($params, true);

        //-------------------------------
        $this->mapper->save($updatedAlbum, fullSave: true);
        //-------------------------------

        $retrieved = $this->mapper->read($albumID, fullRead: false);

        $this->assertEquals($newName ?? 'Original Name', $retrieved->getName());
        if ($newDesc !== null)
        {
            $this->assertEquals($newDesc, $retrieved->getDescription());
        }
    }

    // =====================================================
    // Sub-Object Lifecycle & Cascade Delete Tests

    /**
     * Verifies that full delete purges the album, cover size map, and child photos in geni_photos.
     */
    public function testCascadeDeleteAlbumAndPhotos(): void
    {
        /** @var FactoryMapperPhotoAlbums $albumMapFactory */
        $albumMapFactory = FactoryMapperBase::getFactory(API_PhotoAlbum::class);

        $albumParams = $albumMapFactory->makeParamArray('Cascade Delete Test', 'Desc', photosCount: 2, full: true);
        $album       = API_PhotoAlbum::newFromArray($albumParams, true);
        $albumID     = $album->getID();

        $this->mapper->save($album, fullSave: true);

        // Confirm 2 child photos exist in DB
        $this->assertEquals(2, $this->mapper->countTableRows('geni_photos', 'album_id', $albumID));

        // Fetch cover size ID before deletion
        $sql  = "SELECT cover_sizes_id FROM geni_photo_albums WHERE album_id = :id AND run_id = :rid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $albumID, ':rid' => $this->runID]);
        $coverID = $stmt->fetchColumn();

        //-------------------------------
        // Perform full delete
        $this->mapper->delete($albumID, fullDelete: true);
        //-------------------------------

        // Assert parent album deleted
        $this->assertNull($this->mapper->read($albumID, fullRead: false));

        // Assert cover size map deleted
        if ($coverID)
        {
            $this->assertEquals(0, $this->mapper->countTableRows('geni_photo_sizes', 'id', $coverID));
        }

        // Assert child photos deleted
        $this->assertEquals(0, $this->mapper->countTableRows('geni_photos', 'album_id', $albumID));
    }

    // =====================================================
    // Transaction Atomicity Tests

    /**
     * Verifies that multi-table album saves roll back cleanly on exception.
     */
    public function testAlbumSaveRollbackOnException(): void
    {
        /** @var FactoryMapperPhotoAlbums $albumMapFactory */
        $albumMapFactory = FactoryMapperBase::getFactory(API_PhotoAlbum::class);

        $albumParams = $albumMapFactory->makeParamArray('Rollback Album', 'Desc', photosCount: 0);
        $album       = API_PhotoAlbum::newFromArray($albumParams, true);
        $albumID     = $album->getID();

        $this->pdo->beginTransaction();

        try
        {
            $this->mapper->save($album, fullSave: true);

            // Force duplicate primary key exception
            $sql  = "INSERT INTO geni_photo_albums (album_id, run_id) VALUES (:id, :rid)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $albumID, ':rid' => $this->runID]);

            $this->pdo->commit();
        }
        catch (\PDOException $e)
        {
            $this->pdo->rollBack();
        }

        $this->assertNull($this->mapper->read($albumID, fullRead: false));
    }
}
