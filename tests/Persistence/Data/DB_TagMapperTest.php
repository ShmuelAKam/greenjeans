<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_TagMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\DB_MapperTestBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperTags;

/**
 * DB_TagMapperTest
 * Unit tests for DB_TagMapper database persistence layer.
 */
class DB_TagMapperTest extends DB_MapperTestBase
{
    private DB_TagMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_TagMapper::class);
    }

    // =====================================================
    // Single Save & Read Tests

    /**
     * Verifies saving a single media tag link.
     */
    public function testSaveAndReadTag(): void
    {
        $ownerID   = 'media-doc-001';
        $profileID = 'profile-tagged-101';

        $this->mapper->save($profileID, $ownerID);

        $retrieved = $this->mapper->readAll($ownerID);

        $this->assertCount(1, $retrieved);
        $this->assertEquals($profileID, $retrieved[0]);
    }

    /**
     * Verifies reading tags for an owner with no records returns an empty array.
     */
    public function testReadAllEmptyReturnsEmptyArray(): void
    {
        $retrieved = $this->mapper->readAll('media-non-existent-999');

        $this->assertEmpty($retrieved);
    }

    // =====================================================
    // Batch Operations (`createAll` / `deleteAll`)

    /**
     * Verifies createAll correctly processes indexed ID lists ['profile-123'].
     */
    public function testCreateAllWithListFormat(): void
    {
        /** @var FactoryMapperTags $tagMapFactory */
        global $tagMapFactory;

        $ownerID = 'media-photo-002';
        $tags    = $tagMapFactory->makePresetTags('multiple_profiles_list');

        $this->mapper->createAll($tags, $ownerID);

        $retrieved = $this->mapper->readAll($ownerID);

        $this->assertCount(3, $retrieved);
        $this->assertContains('profile-tag-101', $retrieved);
        $this->assertContains('profile-tag-102', $retrieved);
        $this->assertContains('profile-tag-103', $retrieved);
    }

    /**
     * Verifies createAll correctly processes lookup table formats ['profile-123' => true].
     */
    public function testCreateAllWithLookupTableFormat(): void
    {
        /** @var FactoryMapperTags $tagMapFactory */
        global $tagMapFactory;

        $ownerID = 'media-photo-003';
        $tags    = $tagMapFactory->makePresetTags('multiple_profiles_lookup');

        $this->mapper->createAll($tags, $ownerID);

        $retrieved = $this->mapper->readAll($ownerID);

        $this->assertCount(3, $retrieved);
        $this->assertContains('profile-tag-101', $retrieved);
        $this->assertContains('profile-tag-102', $retrieved);
        $this->assertContains('profile-tag-103', $retrieved);
    }

    /**
     * Verifies createAll with clear = true replaces existing tags for the owner.
     */
    public function testCreateAllWithClearOverwritesExisting(): void
    {
        $ownerID = 'media-photo-004';

        $initialTags = ['profile-old-001', 'profile-old-002'];

        $this->mapper->createAll($initialTags, $ownerID);
        $this->assertCount(2, $this->mapper->readAll($ownerID));

        // Overwrite with a single new tag in lookup format
        $newTags = ['profile-new-001' => true];

        $this->mapper->createAll($newTags, $ownerID);

        $retrieved = $this->mapper->readAll($ownerID);

        $this->assertCount(1, $retrieved);
        $this->assertEquals('profile-new-001', $retrieved[0]);
    }

    /**
     * Verifies deleteAll removes all media tags for an owner.
     */
    public function testDeleteAll(): void
    {
        /** @var FactoryMapperTags $tagMapFactory */
        global $tagMapFactory;

        $ownerID = 'media-doc-005';
        $tags    = $tagMapFactory->makePresetTags('multiple_profiles_list');

        $this->mapper->createAll($tags, $ownerID);
        $this->assertCount(3, $this->mapper->readAll($ownerID));

        $this->mapper->deleteAll($ownerID);

        $this->assertCount(0, $this->mapper->readAll($ownerID));
    }
}
