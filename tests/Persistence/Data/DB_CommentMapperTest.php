<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_CommentMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\DB_MapperTestBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperComments;

/**
 * DB_CommentMapperTest
 * Unit tests for DB_CommentMapper database persistence layer.
 */
class DB_CommentMapperTest extends DB_MapperTestBase
{
    private DB_CommentMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->persistor->getMapper(DB_CommentMapper::class);
    }

    // =====================================================
    // Test Data Providers

    /**
     * @return array <string, array <string>>
     */
    public static function provideCommentPresets(): array
    {
        $data = [];

        foreach (array_keys(FactoryMapperComments::COMMENT_PRESETS) as $key)
        {
            $data [$key] = [$key];
        }

        return $data;
    }

    // =====================================================
    // Custom Assert Helpers

    /**
     * Custom assertion comparing scalar comment properties.
     */
    protected function assertEqualsComment(API_Comment $expected, API_Comment $actual, string $message = ''): void
    {
        self::assertEquals($expected->getComment(), $actual->getComment(), $message . ' [comment text mismatch]');
        self::assertEquals($expected->getCreatedAt(), $actual->getCreatedAt(), $message . ' [created_at mismatch]');
        self::assertEquals($expected->getTitle(), $actual->getTitle(), $message . ' [title mismatch]');
    }

    // =====================================================
    // Basic Persistence Tests

    /**
     * Verifies creation and reading of single comments across all presets with auto-increment IDs.
     * @dataProvider provideCommentPresets
     */
    public function testCreateAndReadCommentPresets(string $presetKey): void
    {
        /** @var FactoryMapperComments $commentMapFactory */
        global $commentMapFactory;

        $ownerID = 'profile-test-owner-001';
        $comment = $commentMapFactory->makePresetComment($presetKey);

        //-------------------------------
        $insertedID = $this->mapper->save($comment, $ownerID);

        $retrieved = $this->mapper->read($insertedID);
        //-------------------------------

        $this->assertGreaterThan(0, $insertedID);
        $this->assertNotNull($retrieved);
        $this->assertEqualsComment($comment, $retrieved);
    }

    /**
     * Verifies reading a non-existent comment ID returns null or empty payload.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        //-------------------------------
        $retrieved = $this->mapper->read(999999);
        //-------------------------------

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Batch Collection Tests (`createAll` / `readAll` / `deleteAll`)

    /**
     * Verifies creating multiple comments for an owner and reading them pre-sorted by created_at.
     */
    public function testCreateAllAndReadAll(): void
    {
        /** @var FactoryMapperComments $commentMapFactory */
        global $commentMapFactory;

        $ownerID = 'profile-test-owner-002';

        $comments = [
            $commentMapFactory->makePresetComment('minimal_comment'),   // created_at: 2021
            $commentMapFactory->makePresetComment('multiline_comment'), // created_at: 2022
            $commentMapFactory->makePresetComment('basic_comment'),     // created_at: 2023
        ];

        $this->mapper->createAll($comments, $ownerID);

        $retrievedComments = $this->mapper->readAll($ownerID);

        $this->assertCount(3, $retrievedComments);
        $this->assertEquals('Unverified claim.', $retrievedComments[0]->getComment());
        $this->assertEquals('Birth Record Note', $retrievedComments[2]->getTitle());
    }

    /**
     * Verifies that createAll with clear = true replaces existing comments for the owner.
     */
    public function testCreateAllWithClearOverwritesExisting(): void
    {
        /** @var FactoryMapperComments $commentMapFactory */
        global $commentMapFactory;

        $ownerID = 'profile-test-owner-003';

        $initialComments = [
            $commentMapFactory->makePresetComment('basic_comment'),$commentMapFactory->makePresetComment('unicode_comment'),
        ];

        $this->mapper->createAll($initialComments, $ownerID);
        $this->assertCount(2, $this->mapper->readAll($ownerID));

        // Overwrite with a single new comment
        $replacementComments = [$commentMapFactory->makePresetComment('multiline_comment'),
        ];

        //-------------------------------
        $this->mapper->createAll($replacementComments, $ownerID);
        //-------------------------------

        $retrievedComments = $this->mapper->readAll($ownerID);

        $this->assertCount(1, $retrievedComments);
        $this->assertEquals('Parish Census Analysis', $retrievedComments[0]->getTitle());
    }

    /**
     * Verifies single and bulk deletion of comments.
     */
    public function testDeleteAndDeleteAll(): void
    {
        /** @var FactoryMapperComments $commentMapFactory */
        global $commentMapFactory;

        $ownerID = 'profile-test-owner-004';
        $comment = $commentMapFactory->makePresetComment('basic_comment');

        $insertedID = $this->mapper->save($comment, $ownerID);
        $this->assertCount(1, $this->mapper->readAll($ownerID));

        //-------------------------------
        // Single delete
        $this->mapper->delete($insertedID);
        $this->assertCount(0, $this->mapper->readAll($ownerID));
        //-------------------------------

        // Bulk delete via deleteAll
        $this->mapper->createAll([$comment,$comment], $ownerID);
        $this->assertCount(2, $this->mapper->readAll($ownerID));

        //-------------------------------
        $this->mapper->deleteAll($ownerID);
        $this->assertCount(0, $this->mapper->readAll($ownerID));
        //-------------------------------
    }
}
