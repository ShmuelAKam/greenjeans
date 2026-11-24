<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_VideoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_VideoMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperVideos;

/**
 * DB_VideoMapperTest
 * Unit tests for DB_VideoMapper persistence layer.
 */
class DB_VideoMapperTest extends DB_MapperTestBase
{
    private DB_VideoMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_VideoMapper::class);
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string|NULL>>
     */
    public static function provideVideoUpdates(): array
    {
        return [
        'Update Title & Desc' => ['New Title',          'New Description', 'new_vid.mp4'],
        'Update Title Only'   => ['Updated Title Only', null,              null],
        ];
    }

    /**
     * @return array <string, array <mixed>>
     */
    public static function provideVideo(): array
    {
        return FactoryMapperVideos::VIDEO_LIST;
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_Video instances have identical field values,
     * sub-objects, and loaded collections without triggering SDK network calls.
     */
    protected function assertEqualsVideo(API_Video $expected, API_Video $actual, bool $full, string $message = ''): void
    {
        // 1. Direct scalar properties on API_MediaBase & API_Video
        self::assertEquals($expected->getID(), $actual->getID(), $message . ' [id mismatch]');
        self::assertEquals($expected->getGUID(), $actual->getGUID(), $message . ' [guid mismatch]');
        self::assertEquals($expected->getTitle(), $actual->getTitle(), $message . ' [title mismatch]');
        self::assertEquals($expected->getDescription(), $actual->getDescription(), $message . ' [description mismatch]');
        self::assertEquals($expected->getAttribution(), $actual->getAttribution(), $message . ' [attribution mismatch]');
        self::assertEquals($expected->getContentType(), $actual->getContentType(), $message . ' [content_type mismatch]');
        self::assertEquals($expected->getURL(), $actual->getURL(), $message . ' [url mismatch]');
        self::assertEquals($expected->getCreatedAt(), $actual->getCreatedAt(), $message . ' [created_at mismatch]');
        self::assertEquals($expected->getUpdatedAt(), $actual->getUpdatedAt(), $message . ' [updated_at mismatch]');

        if ($full)
        {
            // 2. Sub-Object: API_Date
            $expectedDate = $expected->getDate();
            $actualDate   = $actual->getDate();

            if ($expectedDate === null || $actualDate === null)
            {
                self::assertNull($expectedDate, $message . ' [expected date to be NULL]');
                self::assertNull($actualDate, $message . ' [expected actual date to be NULL]');
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
                self::assertNull($expectedLoc, $message . ' [expected location to be NULL]');
                self::assertNull($actualLoc, $message . ' [expected actual location to be NULL]');
            }
            else
            {
                self::assertTrue(API_Location::__equals($expectedLoc, $actualLoc), $message . ' [location value mismatch]');
            }

            // 4. Sub-Object: API_VideoImageSizeMap
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
                    fn(API_Video $p) => $p->makeParamArray(),
                    $expected->getComments()->getList(),
                );
                $actualComments = array_map(
                    fn(API_Video $p) => $p->makeParamArray(),
                    $actual->getComments()->getList(),
                );

                self::assertEquals($expectedComments, $actualComments, $message . ' [comments list mismatch]');
            }
        }
    }

    /**
     * Asserts that two API_VideoImageSizeMap objects have identical size URLs.
     */
    protected function assertEqualsSizeMap(?API_VideoImageSizeMap $expected, ?API_VideoImageSizeMap $actual, string $message = ''): void
    {
        if ($expected === null || $actual === null)
        {
            self::assertNull($expected, $message . ' [expected size map to be NULL]');
            self::assertNull($actual, $message . ' [expected actual size map to be NULL]');
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
     * Assert that videos' collections have expected number of table rows.
     */
    protected function assertCollections(string $videoID, string $stage, int $numTags, int $numComments): void
    {
        $countTags     = $this->mapper->countTableRows('geni_media_tags', 'owner_id', $videoID);
        $countComments = $this->mapper->countTableRows('geni_comments', 'owner_id', $videoID);

        $this->assertEquals($numTags, $countTags, "$stage - Tags");
        $this->assertEquals($numComments, $countComments, "$stage - Comments");
    }

    // =====================================================
    // Basic Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations for an API_Video object.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param array  <string, int> $date
     * @param int    $numTags
     * @param int    $numComments
     * @dataProvider provideVideo
     */
    public function testCreateAndRead(
        string  $title,
        ?string $description,
        ?string $fileName,
        ?string $attribution,
        ?array  $date,
        int     $numTags,
        int     $numComments,
    ): void {
        /** @var FactoryMapperVideos $videoMapFactory */
        $videoMapFactory = FactoryMapperBase::getFactory(API_Video::class);

        $video = $videoMapFactory->makeMapVideo(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            $numTags,
            $numComments,
            full: true,
        );
        $videoID = $video->getId();

        $this->mapper->save($video);

        $retrieved = $this->mapper->read($videoID, fullRead: true);

        $this->assertNotNULL($retrieved);
        $this->assertEqualsVideo($video, $retrieved, true);
    }

    /**
     * Verifies NULL response when reading a non-existent video ID.
     */
    public function testReadNonExistentReturnsNULL(): void
    {
        $retrieved = $this->mapper->read('video-non-existent-id', fullRead: false);

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Scalar Updates

    /**
     * @dataProvider provideVideoUpdates
     */
    public function testUpdateVideoScalarFields(?string $newTitle, ?string $newDesc, ?string $newFile): void
    {
        /** @var FactoryMapperVideos $videoMapFactory */
        $videoMapFactory = FactoryMapperBase::getFactory(API_Video::class);

        $initialVideo = $videoMapFactory->makeMapVideo('Original Title', 'Original Desc', 'original.mp4');
        $videoID      = $initialVideo->getId();

        $this->mapper->save($initialVideo);

        // Build updated object
        $params       = $videoMapFactory->makeParamArray($newTitle ?? 'Original Title', $newDesc, $newFile, doc_id: $videoID);
        $params['id'] = $videoID;

        $updatedVideo = API_Video::newFromArray($params, fromDB: true);
        $this->mapper->save($updatedVideo);

        $retrieved = $this->mapper->read($videoID, fullRead: false);

        $this->assertEquals($newTitle ?? 'Original Title', $retrieved->getTitle());
        if ($newDesc !== null)
        {
            $this->assertEquals($newDesc, $retrieved->getDescription());
        }
    }

    // =====================================================
    // Collection Hydration & Link Table Tests

    /**
     * Verifies that saving an unloaded partial video object leaves geni_media_tags intact.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param array  <string, int> $date
     * @param int    $numTags
     * @param int    $numComments
     * @dataProvider provideVideo
     */
    public function testPartialHydrationPreservesVideoCollections(
        string  $title,
        ?string $description,
        ?string $fileName,
        ?string $attribution,
        ?array  $date,
        int     $numTags,
        int     $numComments,
    ): void {
        /** @var FactoryMapperVideos $videoMapFactory */
        $videoMapFactory = FactoryMapperBase::getFactory(API_Video::class);

        // 1. Persist initial video with tags and comments loaded
        $initialParams = $videoMapFactory->makeParamArray(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            $numTags,
            $numComments,
            doc_id: null,
            full: true,
        );

        $initialVideo = API_Video::newFromArray($initialParams, fromDB: true);
        $videoID      = $initialVideo->getID();

        $this->mapper->save($initialVideo);

        $this->assertCollections($videoID, 'Saved', $numTags, $numComments);

        // 2. Instantiate partial video with unloaded collections (fromDB: false)
        $partialParams = $videoMapFactory->makeParamArray(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            0,
            0,
            doc_id: $videoID,
            full: false,
        );

        $partialVideo = API_Video::newFromArray($partialParams, fromDB: false);

        $this->mapper->save($partialVideo);

        $msg = 'Unloaded tags must remain untouched during partial saves.';
        $this->assertEqualsVideo($initialVideo, $partialVideo, false, $msg);
        $this->assertCollections($videoID, $msg, $numTags, $numComments);
    }

    // =====================================================
    // Sub-Object Lifecycle & Cascade Delete Tests

    /**
     * Verifies that updating a video replaces sub-objects (Date, Location, Sizes) without leaving orphans.
     */
    public function testSubObjectMutationAndOrphanCleanup(): void
    {
        /** @var FactoryMapperVideos $videoMapFactory */
        $videoMapFactory = FactoryMapperBase::getFactory(API_Video::class);

        $videoParams = $videoMapFactory->makeParamArray(
            'Sub-Object Test',
            'Desc',
            'video.mp4',
            'Author',
            ['year' => 2026, 'month' => 10, 'day' => 1],
            numTags: 0,
            numComments: 0,
            doc_id: null,
            full: true,
        );

        $video   = API_Video::newFromArray($videoParams, fromDB: true);
        $videoID = $video->getId();

        $this->mapper->save($video);

        // Capture initial child foreign keys
        $sql  = "SELECT date_id, location_id, size_id FROM geni_videos WHERE video_id = :id AND run_id = :rid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $videoID, ':rid' => $this->runID]);
        $initialIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($initialIDs['date_id']);
        $this->assertNotEmpty($initialIDs['location_id']);
        $this->assertNotEmpty($initialIDs['size_id']);

        // Update video to remove Location and SizeMap
        unset($videoParams['location']);
        unset($videoParams['sizes']);
        $videoParams['id'] = $videoID;

        $updatedVideo = API_Video::newFromArray($videoParams, fromDB: true);

        $this->mapper->save($updatedVideo);

        // Assert old orphan rows were purged
        $this->assertEquals(0, $this->mapper->countTableRows('geni_locations', 'id', $initialIDs['location_id']));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_video_sizes', 'id', $initialIDs['size_id']));

        // Assert parent record keys were updated
        $stmt->execute([':id' => $videoID, ':rid' => $this->runID]);
        $updatedIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNull($updatedIDs['location_id']);
        $this->assertNull($updatedIDs['size_id']);
        $this->assertNotEmpty($updatedIDs['date_id']);
    }

    /**
     * Verifies that deleting a video purges associated sub-objects and tag links.
     */
    public function testCascadeDeleteVideoAndSubObjects(): void
    {
        /** @var FactoryMapperVideos $videoMapFactory */
        $videoMapFactory = FactoryMapperBase::getFactory(API_Video::class);

        $videoParams = $videoMapFactory->makeParamArray(
            'Cascade Delete Test',
            'Desc',
            'video.mp4',
            'Author',
            ['year' => 2026, 'month' => 10, 'day' => 1],
            doc_id: null,
            numTags: 2,
            numComments: 1,
            full: true,
        );

        $video   = API_Video::newFromArray($videoParams, fromDB: true);
        $videoID = $video->getId();

        $this->mapper->save($video);

        $sql  = "SELECT date_id, location_id, size_id FROM geni_videos WHERE video_id = :id AND run_id = :rid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $videoID, ':rid' => $this->runID]);
        $subIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->mapper->delete($videoID);

        $this->assertNull($this->mapper->read($videoID, fullRead: false));

        if ($subIDs['location_id'])
        {
            $this->assertEquals(0, $this->mapper->countTableRows('geni_locations', 'id', $subIDs['location_id']));
        }
        $this->assertEquals(0, $this->mapper->countTableRows('geni_media_tags', 'owner_id', $videoID));
    }

    // =====================================================
    // Transaction Atomicity Tests

    /**
     * Verifies that multi-table video saves roll back cleanly on exception.
     */
    public function testVideoSaveRollbackOnException(): void
    {
        /** @var FactoryMapperVideos $videoMapFactory */
        $videoMapFactory = FactoryMapperBase::getFactory(API_Video::class);

        $videoParams = $videoMapFactory->makeParamArray('Rollback Video', 'Desc', 'video.mp4', 'Author', null);
        $video       = API_Video::newFromArray($videoParams, fromDB: true);
        $videoID     = $video->getId();

        $this->pdo->beginTransaction();

        try
        {
            $this->mapper->save($video);

            // Force duplicate primary key exception
            $sql  = "INSERT INTO geni_videos (video_id, run_id) VALUES (:id, :rid)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $videoID, ':rid' => $this->runID]);

            $this->pdo->commit();
        }
        catch (\PDOException $e)
        {
            $this->pdo->rollBack();
        }

        $this->assertNull($this->mapper->read($videoID, fullRead: false));
    }
}
