<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;

/**
 * DB_LinkTableMapperTest
 * Unit tests for DB_LinkTableMapper generic link and junction table operations.
 */
class DB_LinkTableMapperTest extends DB_MapperTestBase
{
    private DB_LinkTableMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->persistor->getMapper(DB_LinkTableMapper::class);
    }

    // =====================================================

    /**
     * Test saving an array of simple string IDs and retrieving them via readCol.
     */
    public function testSaveAndReadColSimpleStrings(): void
    {
        $parentId = 'profile-test-101';
        $managers = ['profile-mgr-003', 'profile-mgr-001', 'profile-mgr-002'];

        $this->mapper->save(
            table: 'geni_profile_managers',
            keyCol: 'profile_id',
            key: $parentId,
            column: 'manager_id',
            items: $managers,
            clear: true,
        );

        // readCol defaults to sorting by valCol ASC ('profile-mgr-001', 'profile-mgr-002', 'profile-mgr-003')
        $retrieved = $this->mapper->readCol(
            table: 'geni_profile_managers',
            keyCol: 'profile_id',
            key: $parentId,
            valCol: 'manager_id',
        );

        $expectedSorted = ['profile-mgr-001', 'profile-mgr-002', 'profile-mgr-003'];
        $this->assertEquals($expectedSorted, $retrieved);
    }

    /**
     * Test saving objects with getID() method.
     */
    public function testSaveAndReadColObjects(): void
    {
        $parentId = 'profile-test-102';

        $obj1 = new class
        {
            public function getID(): string
            {
                return 'doc-002';
            }
        };

        $obj2 = new class
        {
            public function getID(): string
            {
                return 'doc-001';
            }
        };

        $this->mapper->save(
            table: 'geni_profile_documents',
            keyCol: 'profile_id',
            key: $parentId,
            column: 'doc_id',
            items: [$obj1, $obj2],
            clear: true,
        );

        $retrieved = $this->mapper->readCol(
            table: 'geni_profile_documents',
            keyCol: 'profile_id',
            key: $parentId,
            valCol: 'doc_id',
        );

        $this->assertEquals(['doc-001', 'doc-002'], $retrieved);
    }

    /**
     * Test reading complete rows with optional sorting.
     */
    public function testReadRowWithSorting(): void
    {
        $unionId = 'union-test-201';

        // Manually save children using save()
        $this->mapper->save('geni_union_children', 'union_id', $unionId, 'profile_id', ['profile-child-b', 'profile-child-a'], true);

        // Read all rows sorted by profile_id DESC
        $rows = $this->mapper->readRow(
            table: 'geni_union_children',
            keyCol: 'union_id',
            key: $unionId,
            orderBy: 'profile_id',
            orderDir: 'DESC',
        );

        $this->assertCount(2, $rows);
        $this->assertEquals('profile-child-b', $rows[0]['profile_id']);
        $this->assertEquals('profile-child-a', $rows[1]['profile_id']);
    }

    /**
     * Test clear parameter behavior in save method.
     */
    public function testSaveClearBehavior(): void
    {
        $parentId = 'profile-test-103';

        // Initial save
        $this->mapper->save('geni_profile_projects', 'profile_id', $parentId, 'project_id', ['proj-001', 'proj-002'], true);

        // Overwrite save with clear = true
        $this->mapper->save('geni_profile_projects', 'profile_id', $parentId, 'project_id', ['proj-003'], true);

        $retrieved = $this->mapper->readCol('geni_profile_projects', 'profile_id', $parentId, 'project_id');

        $this->assertEquals(['proj-003'], $retrieved);

        // Append save with clear = false
        $this->mapper->save('geni_profile_projects', 'profile_id', $parentId, 'project_id', ['proj-004'], false);

        $retrievedAppended = $this->mapper->readCol('geni_profile_projects', 'profile_id', $parentId, 'project_id');

        $this->assertEquals(['proj-003', 'proj-004'], $retrievedAppended);
    }

    /**
     * Test clearing rows directly.
     */
    public function testClear(): void
    {
        $parentId = 'profile-test-104';

        $this->mapper->save('geni_profile_photos', 'profile_id', $parentId, 'photo_id', ['photo-001', 'photo-002'], true);
        $this->assertCount(2, $this->mapper->readCol('geni_profile_photos', 'profile_id', $parentId, 'photo_id'));

        $this->mapper->clear('geni_profile_photos', 'profile_id', $parentId);

        $this->assertCount(0, $this->mapper->readCol('geni_profile_photos', 'profile_id', $parentId, 'photo_id'));
    }
}
