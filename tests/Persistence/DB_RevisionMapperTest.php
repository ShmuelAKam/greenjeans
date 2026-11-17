<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Revision;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_RevisionMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperRevisions;

/**
 * DB_RevisionMapperTest
 * Unit tests for DB_RevisionMapper persistence layer.
 */
class DB_RevisionMapperTest extends DB_MapperTestBase
{
    private DB_RevisionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_RevisionMapper::class);
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string>>
     */
    public static function provideRevision(): array
    {
        return FactoryMapperRevisions::REVISION_LIST;
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_Revision instances have identical property values.
     */
    protected function assertEqualsRevision(API_Revision $expected, API_Revision $actual, string $message = ''): void
    {
        self::assertEquals($expected->getID(), $actual->getID(), $message . ' [id mismatch]');
        self::assertEquals($expected->getGUID(), $actual->getGUID(), $message . ' [guid mismatch]');
        self::assertEquals($expected->getAction(), $actual->getAction(), $message . ' [action mismatch]');
        self::assertEquals($expected->getStory(), $actual->getStory(), $message . ' [story mismatch]');
        self::assertEquals($expected->getDateLocal(), $actual->getDateLocal(), $message . ' [date_local mismatch]');
        self::assertEquals($expected->getTimeLocal(), $actual->getTimeLocal(), $message . ' [time_local mismatch]');
        self::assertEquals($expected->getTimestamp(), $actual->getTimestamp(), $message . ' [timestamp mismatch]');
    }

    // =====================================================
    // Basic Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations for an API_Revision object.
     * @dataProvider provideRevision
     */
    public function testCreateAndRead(
        string  $action,
        ?string $story,
        string  $dateLocal,
        string  $timeLocal,
        string  $timestamp,
    ): void {
        /** @var FactoryMapperRevisions $revisionMapFactory */
        global $revisionMapFactory;

        $revision   = $revisionMapFactory->makeMapRevision($action, $story, $dateLocal, $timeLocal, $timestamp);
        $revisionID = $revision->getID();

        $savedID = $this->mapper->save($revision);
        $this->assertEquals($revisionID, $savedID);

        $retrieved = $this->mapper->read($revisionID);

        $this->assertNotNull($retrieved);
        $this->assertEqualsRevision($revision, $retrieved);
    }

    /**
     * Verifies null response when reading a non-existent revision ID.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $retrieved = $this->mapper->read('revision-non-existent-id');

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Update Operations

    /**
     * Verifies updating scalar fields of an existing revision record.
     */
    public function testUpdateRevision(): void
    {
        /** @var FactoryMapperRevisions $revisionMapFactory */
        global $revisionMapFactory;

        $initialRevision = $revisionMapFactory->makeMapRevision('create', 'Initial story');
        $revisionID      = $initialRevision->getID();

        $this->mapper->save($initialRevision);

        // Build updated revision object with same ID
        $params          = $revisionMapFactory->makeParamArray('update', 'Corrected story text', id: $revisionID);
        $updatedRevision = API_Revision::newFromArray($params);

        $this->mapper->save($updatedRevision);

        $retrieved = $this->mapper->read($revisionID);

        $this->assertNotNull($retrieved);
        $this->assertEquals('update', $retrieved->getAction());
        $this->assertEquals('Corrected story text', $retrieved->getStory());
    }

    // =====================================================
    // Deletion Operations

    /**
     * Verifies deleting a revision record removes it completely.
     */
    public function testDeleteRevision(): void
    {
        /** @var FactoryMapperRevisions $revisionMapFactory */
        global $revisionMapFactory;

        $revision   = $revisionMapFactory->makeMapRevision('delete', 'Story to be deleted');
        $revisionID = $revision->getID();

        $this->mapper->save($revision);
        $this->assertEquals(1, $this->mapper->countTableRows('geni_revisions', 'revision_id', $revisionID));

        $this->mapper->delete($revisionID);

        $this->assertNull($this->mapper->read($revisionID));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_revisions', 'revision_id', $revisionID));
    }
}
