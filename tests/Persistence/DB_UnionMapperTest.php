<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Union;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_UnionMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperUnions;

/**
 * DB_UnionMapperTest
 * Unit tests for DB_UnionMapper persistence layer across 24 union permutations.
 */
class DB_UnionMapperTest extends DB_MapperTestBase
{
    private DB_UnionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_UnionMapper::class);
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string>>
     */
    public static function provideUnionPresets(): array
    {
        $data = [];

        foreach (array_keys(FactoryMapperUnions::UNION_PRESETS) as $key)
        {
            $data[$key] = [$key];
        }

        return $data;
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_Union instances have identical scalar fields, partners,
     * events, and child classification arrays.
     */
    protected function assertEqualsUnion(API_Union $expected, API_Union $actual, string $message = ''): void
    {
        self::assertEquals($expected->getID(), $actual->getID(), $message . ' [id mismatch]');
        self::assertEquals($expected->getStatus(), $actual->getStatus(), $message . ' [status mismatch]');

        self::assertList($expected->getPartners(), $actual->getPartners(), $message . ' [partners mismatch]');
        self::assertList($expected->getChildren(), $actual->getChildren(), $message . ' [biological children mismatch]');
        self::assertList($expected->getAdoptedChildren(), $actual->getAdoptedChildren(), $message . ' [adopted children mismatch]');
        self::assertList($expected->getFosterChildren(), $actual->getFosterChildren(), $message . ' [foster children mismatch]');

        if ($expected->getMarriage())
        {
            self::assertNotNull($actual->getMarriage(), $message . ' [marriage event missing]');
        }

        if ($expected->getDivorce())
        {
            self::assertNotNull($actual->getDivorce(), $message . ' [divorce event missing]');
        }
    }

    /**
     * @param array  <string> $expected
     * @param array  <string> $actual
     * @param string $message
     */
    protected static function assertList(array $expected, array $actual, string $message): void
    {
        sort($expected);
        sort($actual);

        self::assertEquals($expected, $actual, $message);
    }

    // =====================================================
    // Basic Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations across all 24 union presets.
     * @dataProvider provideUnionPresets
     */
    public function testCreateAndReadPresets(string $presetKey): void
    {
        /** @var FactoryMapperUnions $unionMapFactory */
        global $unionMapFactory;

        $union   = $unionMapFactory->makePresetUnion($presetKey);
        $unionID = $union->getID();

        $savedID = $this->mapper->save($union);
        $this->assertEquals($unionID, $savedID);

        $retrieved = $this->mapper->read($unionID);

        $this->assertNotNull($retrieved);
        $this->assertEqualsUnion($union, $retrieved);
    }

    /**
     * Verifies null response when reading a non-existent union ID.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $retrieved = $this->mapper->read('union-non-existent-id');

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Update & Cascade Delete Tests

    /**
     * Verifies updating a union's events, status, and partner/child collections.
     */
    public function testUpdateUnionReplacesEventsAndCollections(): void
    {
        /** @var FactoryMapperUnions $unionMapFactory */
        global $unionMapFactory;

        $initialUnion = $unionMapFactory->makePresetUnion('married_bio_children');
        $unionID      = $initialUnion->getID();

        $this->mapper->save($initialUnion);

        // Update to divorced preset retaining union ID
        $updatedUnion = $unionMapFactory->makePresetUnion('divorced_bio_children', id: $unionID);

        $this->mapper->save($updatedUnion);

        $retrieved = $this->mapper->read($unionID);

        $this->assertNotNull($retrieved);
        $this->assertEquals('ex_spouse', $retrieved->getStatus());
        $this->assertNotNull($retrieved->getDivorce());
    }

    /**
     * Verifies that deleting a union purges parent row, event sub-objects, and link table entries.
     */
    public function testDeleteUnionClearsEventsAndLinks(): void
    {
        /** @var FactoryMapperUnions $unionMapFactory */
        global $unionMapFactory;

        $union   = $unionMapFactory->makePresetUnion('married_blended_children');
        $unionID = $union->getID();

        $this->mapper->save($union);
        $this->assertEquals(1, $this->mapper->countTableRows('geni_unions', 'union_id', $unionID));

        $this->mapper->delete($unionID);

        $this->assertNull($this->mapper->read($unionID));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_unions', 'union_id', $unionID));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_union_partners', 'union_id', $unionID));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_union_children', 'union_id', $unionID));
    }
}
