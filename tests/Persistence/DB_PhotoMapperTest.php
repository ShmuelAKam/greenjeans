<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_PhotoMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperPhotos;

/**
 * DB_PhotoMapperTest
 * Unit tests for DB_PhotoMapper persistence layer.
 */
class DB_PhotoMapperTest extends DB_MapperTestBase
{
    private DB_PhotoMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_PhotoMapper::class);
    }

    // =====================================================
    // Test providers.

    /**
     * @return array <string, array <string|NULL>>
     */
    public static function providePhotoUpdates(): array
    {
        return [
            'Update Title & Desc' => ['New Title',          'New Description', 'new_pic.jpg'],
            'Update Title Only'   => ['Updated Title Only', null,              null],
        ];
    }

    /**
     * @return array <string, array <string|int|NULL|array <int>>>
     */
    public static function providePhoto(): array
    {
        return FactoryMapperPhotos::PHOTO_LIST;
    }

    // =====================================================
    // Helper Functions

    // =====================================================
    // Custom assert functions.

    /**
     * Asserts that two API_Photo instances have identical field values,
     * sub-objects, and loaded collections without triggering SDK network calls.
     */
    protected function assertEqualsPhoto(API_Photo $expected, API_Photo $actual, string $message = ''): void
    {
        // 1. Direct scalar properties on API_MediaBase & API_Photo
        self::assertEquals($expected->getID(), $actual->getID(), $message . ' [id mismatch]');
        self::assertEquals($expected->getGUID(), $actual->getGUID(), $message . ' [guid mismatch]');
        self::assertEquals($expected->getAlbumID(), $actual->getAlbumID(), $message . ' [album_id mismatch]');
        self::assertEquals($expected->getTitle(), $actual->getTitle(), $message . ' [title mismatch]');
        self::assertEquals($expected->getDescription(), $actual->getDescription(), $message . ' [description mismatch]');
        self::assertEquals($expected->getAttribution(), $actual->getAttribution(), $message . ' [attribution mismatch]');
        self::assertEquals($expected->getContentType(), $actual->getContentType(), $message . ' [content_type mismatch]');
        self::assertEquals($expected->getURL(), $actual->getURL(), $message . ' [url mismatch]');
        self::assertEquals($expected->getCreatedAt(), $actual->getCreatedAt(), $message . ' [created_at mismatch]');
        self::assertEquals($expected->getUpdatedAt(), $actual->getUpdatedAt(), $message . ' [updated_at mismatch]');

        // 2. Sub-Object: API_Date
        $expectedDate = $expected->getDate();
        $actualDate   = $actual->getDate();

        if ($expectedDate === null || $actualDate === null)
        {
            self::assertNull($expectedDate, $message . ' [expected date to be null]');
            self::assertNull($actualDate, $message . ' [expected actual date to be null]');
        }
        else
        {
            self::assertTrue(API_Date::__equals($expectedDate, $actualDate), $message . ' [date value mismatch]');
        }

        // 3. Sub-Object: API_Location
        $expectedLoc = $expected->getLocation();
        $actualLoc   = $actual->getLocation();

        if ($expectedLoc === null || $actualLoc === null)
        {
            self::assertNull($expectedLoc, $message . ' [expected location to be null]');
            self::assertNull($actualLoc, $message . ' [expected actual location to be null]');
        }
        else
        {
            self::assertTrue(API_Location::__equals($expectedLoc, $actualLoc), $message . ' [location value mismatch]');
        }

        // 4. Sub-Object: API_PhotoImageSizeMap

        $this->assertEqualsSizeMap($expected->getSizes(), $actual->getSizes(), $message);

        // 5. Relational Collection: Tags (Guarded against API network calls)

        $expectedTagsLoaded = $expected->isLoaded('tags') || $expected->isLoaded('tag-IDs');
        $actualTagsLoaded   = $actual->isLoaded('tags') || $actual->isLoaded('tag-IDs');

        self::assertEquals($expectedTagsLoaded, $actualTagsLoaded, $message . ' [tags isLoaded state mismatch]');

        if ($expectedTagsLoaded)
        {
            self::assertEquals(
                $expected->getTags()->getList(),
                $actual->getTags()->getList(),
                $message . ' [tags list mismatch]',
            );
        }

        // 6. Relational Collection: Comments (Guarded against API network calls)
        $expectedCommentsLoaded = $expected->isLoaded('comments');
        $actualCommentsLoaded   = $actual->isLoaded('comments');

        self::assertEquals($expectedCommentsLoaded, $actualCommentsLoaded, $message . ' [comments isLoaded state mismatch]');

        if ($expectedCommentsLoaded)
        {
            $expectedComments = array_map(
                fn(API_Comment $p) => $p->makeParamArray(),
                $expected->getComments()->getList(),
            );
            $actualComments = array_map(
                fn(API_Comment $p) => $p->makeParamArray(),
                $actual->getComments()->getList(),
            );

            self::assertEquals($expectedComments, $actualComments, $message . ' [comments list mismatch]');
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

    /**
     * Assert that documents' collections have expected number of table rows.
     * @param string $docID
     * @param string $stage
     * @param int    $numTags
     * @param int    $numComments
     */
    protected function assertCollections(string $docID, string $stage, int $numTags, int $numComments): void
    {
        $countTags     = $this->mapper->countTableRows('geni_media_tags', 'owner_id', $docID);
        $countComments = $this->mapper->countTableRows('geni_comments', 'owner_id', $docID);

        $this->assertEquals($numTags, $countTags, "$stage - Tags");
        $this->assertEquals($numComments, $countComments, "$stage - Comments");
    }

    // =====================================================
    // Basic Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations for an API_Photo object.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param string $albumId
     * @param array <int>|NULL $date
     * @param int    $numTags
     * @param int    $numComments
     * @dataProvider providePhoto
     */
    public function testCreateAndRead(
        string  $title,
        ?string $description,
        ?string $fileName,
        ?string $attribution,
        string  $albumId,
        ?array  $date,
        int     $numTags,
        int     $numComments,
    ): void {
        /** @var FactoryMapperPhotos $photoMapFactory */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        $photo = $photoMapFactory->makeMapPhoto(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            $numTags,
            $numComments,
            $albumId,
            full: true,
        );
        $photoID = $photo->getId();

        //-------------------------------
        $this->mapper->save($photo);

        $retrieved = $this->mapper->read($photoID, fullRead: true);
        //-------------------------------

        $this->assertNotNull($retrieved);
        $this->assertEqualsPhoto($photo, $retrieved);
    }

    /**
     * Verifies null response when reading a non-existent photo ID.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $retrieved = $this->mapper->read('photo-non-existent-id', fullRead: false);

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Scalar Updates

    /**
     * @dataProvider providePhotoUpdates
     */
    public function testUpdatePhotoScalarFields(?string $newTitle, ?string $newDesc, ?string $newFile): void
    {
        /** @var FactoryMapperPhotos $photoMapFactory */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        $initialPhoto = $photoMapFactory->makeMapPhoto('Original Title', 'Original Desc', 'original.jpg');
        $photoID      = $initialPhoto->getId();

        $this->mapper->save($initialPhoto);

        // Build updated object
        $params = $photoMapFactory->makeParamArray(
            $newTitle ?? 'Original Title',
            $newDesc,
            $newFile,
            doc_id: $photoID,
        );
        $params['id'] = $photoID;

        $updatedPhoto = API_Photo::newFromArray($params, fromDB: true);
        $this->mapper->save($updatedPhoto);

        $retrieved = $this->mapper->read($photoID, fullRead: false);

        $this->assertEquals($newTitle ?? 'Original Title', $retrieved->getTitle());
        if ($newDesc !== null)
        {
            $this->assertEquals($newDesc, $retrieved->getDescription());
        }
    }

    // =====================================================
    // Album Batch & Lifecycle Tests

    /**
     * Verifies createAll(), readAll(), and deleteAll() album scoping.
     */
    public function testAlbumCollectionBatchLifecycle(): void
    {
        /** @var FactoryMapperPhotos $photoMapFactory */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        $albumID = 'album-uuid-101';

        // 1. Create 3 photos for the album

        $photos = $photoMapFactory->makePhotoList($albumID, count: 3);

        $this->mapper->createAll($photos, $albumID);

        $retrieved = $this->mapper->readAll($albumID);
        $this->assertCount(3, $retrieved);

        // 2. Re-run createAll with 2 new photos and clear: true

        $newPhotos = $photoMapFactory->makePhotoList($albumID, count: 2);
        $this->mapper->createAll($newPhotos, $albumID);

        $retrievedAfterClear = $this->mapper->readAll($albumID);
        $this->assertCount(2, $retrievedAfterClear);
    }

    // =====================================================
    // Collection Hydration & Link Table Tests

    /**
     * Verifies that saving an unloaded partial photo object leaves geni_media_tags intact.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param string $albumId
     * @param array <int>|NULL $date
     * @param int    $numTags
     * @param int    $numComments
     * @dataProvider providePhoto
     */
    public function testPartialHydrationPreservesPhotoCollections(
        string  $title,
        ?string $description,
        ?string $fileName,
        ?string $attribution,
        string  $albumId,
        ?array  $date,
        int     $numTags,
        int     $numComments,
    ): void {
        /** @var FactoryMapperPhotos $photoMapFactory */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        // 1. Persist initial photo with tags and comments loaded

        $initialParams = $photoMapFactory->makeParamArray(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            $numTags,
            $numComments,
            $albumId,
            doc_id: null,
            full: true,
        );

        $initialPhoto = API_Photo::newFromArray($initialParams, fromDB: true);
        $photoID      = $initialPhoto->getID();

        $this->mapper->save($initialPhoto);

        $this->assertCollections($photoID, 'Saved', $numTags, $numComments);

        // 2. Instantiate partial photo with unloaded collections (fromDB: false)

        $partialParams = $photoMapFactory->makeParamArray(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            0,
            0,
            $albumId,
            doc_id: $photoID,
            full: false,
        );

        $partialPhoto = API_Photo::newFromArray($partialParams, fromDB: false);

        $this->mapper->save($partialPhoto);

        $msg = 'Unloaded tags must remain untouched during partial saves.';
        $this->assertCollections($photoID, $msg, $numTags, $numComments);
    }

    // =====================================================
    // Sub-Object Lifecycle & Cascade Delete Tests

    /**
     * Verifies that updating a photo replaces sub-objects (Date, Location, Sizes) without leaving orphans.
     */
    public function testSubObjectMutationAndOrphanCleanup(): void
    {
        /** @var FactoryMapperPhotos $photoMapFactory */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        $photoParams = $photoMapFactory->makeParamArray(
            'Sub-Object Test',
            'Desc',
            'photo.jpg',
            'Author',
            ['year' => 2026, 'month' => 10, 'day' => 1],
            numTags: 0,
            numComments: 0,
            doc_id: null,
            full: true,
        );

        $photo   = API_Photo::newFromArray($photoParams, fromDB: true);
        $photoID = $photo->getId();

        $this->mapper->save($photo);

        // Capture initial child foreign keys
        $sql  = "SELECT date_id, location_id, size_id FROM geni_photos WHERE photo_id = :id AND run_id = :rid";
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([':id' => $photoID, ':rid' =>$this->getRunID() ]);
        $initialIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($initialIDs['date_id']);
        $this->assertNotEmpty($initialIDs['location_id']);
        $this->assertNotEmpty($initialIDs['size_id']);

        // Update photo to remove Location and SizeMap
        unset($photoParams['location']);
        unset($photoParams['sizes']);
        $photoParams['id'] = $photoID;

        $updatedPhoto = API_Photo::newFromArray($photoParams, fromDB: true);

        $this->mapper->save($updatedPhoto);

        // Assert old orphan rows were purged
        $this->assertEquals(0, $this->mapper->countTableRows('geni_locations', 'id', $initialIDs['location_id']));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_photo_sizes', 'id', $initialIDs['size_id']));

        // Assert parent record keys were updated
        $stmt->execute([':id' => $photoID, ':rid' =>$this->getRunID() ]);
        $updatedIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNull($updatedIDs['location_id']);
        $this->assertNull($updatedIDs['size_id']);
        $this->assertNotEmpty($updatedIDs['date_id']);
    }

    /**
     * Verifies that deleting a photo purges associated sub-objects and tag links.
     */
    public function testCascadeDeletePhotoAndSubObjects(): void
    {
        /* @var $photoMapFactory \ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperPhotos */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        $photoParams = $photoMapFactory->makeParamArray(
            'Cascade Delete Test',
            'Desc',
            'photo.jpg',
            'Author',
            ['year' => 2026, 'month' => 10, 'day' => 1],
            doc_id: null,
            numTags: 2,
            numComments: 1,
            full: true,
        );

        $photo   = API_Photo::newFromArray($photoParams, fromDB: true);
        $photoID = $photo->getId();

        $this->mapper->save($photo);

        $sql  = "SELECT date_id, location_id, size_id FROM geni_photos WHERE photo_id = :id AND run_id = :rid";
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([':id' => $photoID, ':rid' =>$this->getRunID() ]);
        $subIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->mapper->delete($photoID);

        $this->assertNull($this->mapper->read($photoID, fullRead: false));

        if ($subIDs['location_id'])
        {
            $this->assertEquals(0, $this->mapper->countTableRows('geni_locations', 'id', $subIDs['location_id']));
        }
        $this->assertEquals(0, $this->mapper->countTableRows('geni_media_tags', 'owner_id', $photoID));
    }

    // =====================================================
    // Transaction Atomicity Tests
    // =====================================================

    /**
     * Verifies that multi-table photo saves roll back cleanly on exception.
     */
    public function testPhotoSaveRollbackOnException(): void
    {
        /** @var FactoryMapperPhotos $photoMapFactory */
        $photoMapFactory = FactoryMapperBase::getFactory(API_Photo::class);

        $photoParams = $photoMapFactory->makeParamArray('Rollback Photo', 'Desc', 'photo.jpg', 'Author', null);
        $photo       = API_Photo::newFromArray($photoParams, fromDB: true);
        $photoID     = $photo->getId();

        $this->getPDO()->beginTransaction();

        try
        {
            $this->mapper->save($photo);

            // Force duplicate primary key exception
            $sql  = "INSERT INTO geni_photos (photo_id, run_id) VALUES (:id, :rid)";
            $stmt = $this->getPDO()->prepare($sql);
            $stmt->execute([':id' => $photoID, ':rid' =>$this->getRunID() ]);

            $this->getPDO()->commit();
        }
        catch (\PDOException $e)
        {
            $this->getPDO()->rollBack();
        }

        $this->assertNull($this->mapper->read($photoID, fullRead: false));
    }
}
