<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_LocationMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\DB_MapperTestBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperLocations;

/**
 * DB_LocationMapperTest
 * Unit tests for DB_LocationMapper database persistence layer.
 */
class DB_LocationMapperTest extends DB_MapperTestBase
{
    private DB_LocationMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_LocationMapper::class);
    }

    // =====================================================
    // Test Data Providers

    /**
     * @return array <string, array <string>>
     */
    public static function provideLocationPresets(): array
    {
        $data = [];

        foreach (array_keys(FactoryMapperLocations::LOCATION_PRESETS) as $key)
        {
            $data[$key] = [$key];
        }

        return $data;
    }

    // =====================================================
    // Custom Assert Helpers

    /**
     * Compare two API_Location instances using API_Location::__equals.
     */
    protected function assertEqualsLocation(API_Location $expected, API_Location $actual, string $message = ''): void
    {
        self::assertTrue(
            API_Location::__equals($expected, $actual),
            $message . ' [API_Location::__equals structural check failed]',
        );

        // Also check read-only fields calculated by Geni/Database
        self::assertEquals($expected->getLongitude(), $actual->getLongitude(), $message . ' [longitude mismatch]');
        self::assertEquals($expected->getLatitude(), $actual->getLatitude(), $message . ' [latitude mismatch]');
        self::assertEquals($expected->getFormattedLocation(), $actual->getFormattedLocation(), $message . ' [formatted_location mismatch]');
        self::assertEquals($expected->getCountryCode(), $actual->getCountryCode(), $message . ' [country_code mismatch]');
    }

    // =====================================================
    // Basic Persistence Tests

    /**
     * Verifies persistence and retrieval across all location presets.
     * @dataProvider provideLocationPresets
     */
    public function testCreateAndReadLocationPresets(string $presetKey): void
    {
        /** @var FactoryMapperLocations $locationMapFactory */
        global $locationMapFactory;

        $ownerID  = 'event-test-owner-101';
        $location = $locationMapFactory->makePresetLocation($presetKey);

        //-------------------------------
        $insertedID = $this->mapper->save($location, $ownerID);

        $retrieved = $this->mapper->read($insertedID);
        //-------------------------------

        $this->assertGreaterThan(0, $insertedID);
        $this->assertNotNull($retrieved);
        $this->assertEqualsLocation($location, $retrieved);
    }

    /**
     * Verifies that readArray cleans up internal primary key, owner_id, and run_id columns.
     */
    public function testReadArrayUnsetsInternalColumns(): void
    {
        /** @var FactoryMapperLocations $locationMapFactory */
        global $locationMapFactory;

        $ownerID  = 'event-test-owner-102';
        $location = $locationMapFactory->makePresetLocation('full_location');

        $insertedID = $this->mapper->save($location, $ownerID);

        //-------------------------------
        $row = $this->mapper->readArray($insertedID);
        //-------------------------------

        $this->assertIsArray($row);
        $this->assertArrayNotHasKey('id', $row);
        $this->assertArrayNotHasKey('owner_id', $row);
        $this->assertArrayNotHasKey('run_id', $row);

        $this->assertEquals('Mount Vernon Estate', $row['place_name']);
        $this->assertEquals('3200 Mount Vernon Memorial Hwy', $row['street_address1']);
    }

    /**
     * Verifies reading non-existent location ID returns null.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $this->assertNull($this->mapper->readArray(999999));
    }

    /**
     * Verifies deleting a location record by integer ID.
     */
    public function testDeleteLocation(): void
    {
        /** @var FactoryMapperLocations $locationMapFactory */
        global $locationMapFactory;

        $ownerID  = 'event-test-owner-103';
        $location = $locationMapFactory->makePresetLocation('city_state_only');

        $insertedID = $this->mapper->save($location, $ownerID);
        $this->assertNotNull($this->mapper->read($insertedID));

        //-------------------------------
        $this->mapper->delete($insertedID);
        //-------------------------------

        $this->assertNull($this->mapper->readArray($insertedID));
    }
}
