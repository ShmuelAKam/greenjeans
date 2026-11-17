<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\Data\API_Relation;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_RelationMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperRelations;

/**
 * DB_RelationMapperTest
 * Unit tests for DB_RelationMapper persistence layer.
 */
class DB_RelationMapperTest extends DB_MapperTestBase
{
    private DB_RelationMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_RelationMapper::class);
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_Relation instances have identical values.
     */
    protected function assertEqualsRelation(API_Relation $expected, API_Relation $actual, string $message = ''): void
    {
        self::assertEquals($expected->getName(), $actual->getName(), $message . ' [name mismatch]');
        self::assertEquals($expected->getRelation(), $actual->getRelation(), $message . ' [relation type mismatch]');
        self::assertEquals($expected->getUrl(), $actual->getUrl(), $message . ' [url mismatch]');
    }

    // =====================================================
    // Persistence & Query Tests

    /**
     * Verifies saving single relation steps and reading them back ordered by sequence.
     */
    public function testSaveAndFindByParentId(): void
    {
        /** @var FactoryMapperRelations $relationFactory */
        global $relationFactory;

        $parentId = 'union-test-uuid-99';
        $rel1     = $relationFactory->makeRelation('Father Step', 'father', 'https://www.geni.com/api/profile-1');
        $rel2     = $relationFactory->makeRelation('Mother Step', 'mother', 'https://www.geni.com/api/profile-2');

        // Save out of sequence order to test usort sorting
        $this->mapper->save($parentId, $rel2, sequence: 2);
        $this->mapper->save($parentId, $rel1, sequence: 1);

        $retrieved = $this->mapper->findByParentId($parentId);

        $this->assertCount(2, $retrieved);
        $this->assertEqualsRelation($rel1, $retrieved[0], 'First sequence entry');
        $this->assertEqualsRelation($rel2, $retrieved[1], 'Second sequence entry');
    }

    /**
     * Verifies batch creation of relation steps with automatic sequence indexing.
     */
    public function testCreateAllAutoSequencesList(): void
    {
        /** @var FactoryMapperRelations $relationFactory */
        global $relationFactory;

        $parentId  = 'union-batch-test-123';
        $relations = $relationFactory->makeRelationList(3);

        $this->mapper->createAll($relations, $parentId, clear: false);

        $retrieved = $this->mapper->findByParentId($parentId);

        $this->assertCount(3, $retrieved);
        $this->assertEqualsRelation($relations[0], $retrieved[0], 'Sequence 1 match');
        $this->assertEqualsRelation($relations[1], $retrieved[1], 'Sequence 2 match');
        $this->assertEqualsRelation($relations[2], $retrieved[2], 'Sequence 3 match');
    }

    /**
     * Verifies that createAll with clear: true wipes existing relations before inserting the new list.
     */
    public function testCreateAllWithClearReplacesExistingRelations(): void
    {
        /** @var FactoryMapperRelations $relationFactory */
        global $relationFactory;

        $parentId    = 'union-replace-test-456';
        $initialList = $relationFactory->makeRelationList(2);
        $newList     = $relationFactory->makeRelationList(1);

        // 1. Initial creation
        $this->mapper->createAll($initialList, $parentId, clear: false);
        $this->assertEquals(2, $this->mapper->countTableRows('geni_relations', 'parent_id', $parentId));

        // 2. Overwrite with new list
        $this->mapper->createAll($newList, $parentId, clear: true);

        $retrieved = $this->mapper->findByParentId($parentId);

        $this->assertCount(1, $retrieved);
        $this->assertEqualsRelation($newList[0], $retrieved[0]);
    }

    /**
     * Verifies that findByParentId isolates relations strictly by parent ID and run_id scope.
     */
    public function testFindByParentIdIsolatesParents(): void
    {
        /** @var FactoryMapperRelations $relationFactory */
        global $relationFactory;

        $parentA = 'union-parent-A';
        $parentB = 'union-parent-B';

        $relA = $relationFactory->makeRelation('Parent A Step', 'father', 'https://www.geni.com/api/profile-a');
        $relB = $relationFactory->makeRelation('Parent B Step', 'mother', 'https://www.geni.com/api/profile-b');

        $this->mapper->save($parentA, $relA, sequence: 1);
        $this->mapper->save($parentB, $relB, sequence: 1);

        $retrievedA = $this->mapper->findByParentId($parentA);
        $retrievedB = $this->mapper->findByParentId($parentB);

        $this->assertCount(1, $retrievedA);
        $this->assertCount(1, $retrievedB);

        $this->assertEquals('Parent A Step', $retrievedA[0]->getName());
        $this->assertEquals('Parent B Step', $retrievedB[0]->getName());
    }

    /**
     * Verifies empty array return when querying a parent ID with no relation steps.
     */
    public function testFindByNonExistentParentReturnsEmptyArray(): void
    {
        $retrieved = $this->mapper->findByParentId('non-existent-parent-id');

        $this->assertEmpty($retrieved);
    }

    // =====================================================
    // Collection Replacement & Deletion Tests

    /**
     * Verifies that deleting relations by parent_id clears rows in geni_relations.
     */
    public function testDeleteAllByParentId(): void
    {
        /** @var FactoryMapperRelations $relationFactory */
        global $relationFactory;

        $parentId  = 'parent-to-delete-123';
        $relations = $relationFactory->makeRelationList(3);

        foreach ($relations as $index => $rel)
        {
            $this->mapper->save($parentId, $rel, sequence: $index + 1);
        }

        $this->assertEquals(3, $this->mapper->countTableRows('geni_relations', 'parent_id', $parentId));

        $this->mapper->deleteAll($parentId);

        $this->assertEquals(0, $this->mapper->countTableRows('geni_relations', 'parent_id', $parentId));
        $this->assertEmpty($this->mapper->findByParentId($parentId));
    }
}
