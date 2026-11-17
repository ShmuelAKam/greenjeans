<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_EventMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\DB_MapperTestBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperEvents;

/**
 * DB_EventMapperTest
 * Unit tests for DB_EventMapper database persistence layer.
 */
class DB_EventMapperTest extends DB_MapperTestBase
{
    private DB_EventMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_EventMapper::class);
    }

    // =====================================================
    // Test Data Providers

    /**
     * @return array <string, array <string>>
     */
    public static function provideEventPresets(): array
    {
        $data = [];

        foreach (array_keys(FactoryMapperEvents::EVENT_PRESETS) as $key)
        {
            $data[$key] = [$key];
        }

        return $data;
    }

    // =====================================================
    // Custom Assert Helpers

    /**
     * Custom assertion comparing core event properties, dates, and locations.
     */
    protected function assertEqualsEvent(API_Event $expected, API_Event $actual, string $message = ''): void
    {
        self::assertEquals($expected->getName(), $actual->getName(), $message . ' [name mismatch]');
        self::assertEquals($expected->getDescription(), $actual->getDescription(), $message . ' [description mismatch]');

        if ($expected->getDate() !== null)
        {
            self::assertNotNull($actual->getDate(), $message . ' [expected date object but got null]');
            self::assertEquals(
                $expected->getDate()->getSortKey(),
                $actual->getDate()->getSortKey(),
                $message . ' [date sort key mismatch]',
            );
        }
        else
        {
            self::assertNull($actual->getDate(), $message . ' [expected null date]');
        }

        if ($expected->getLocation() !== null)
        {
            self::assertNotNull($actual->getLocation(), $message . ' [expected location object but got null]');
        }
        else
        {
            self::assertNull($actual->getLocation(), $message . ' [expected null location]');
        }
    }

    // =====================================================
    // Single CRUD Tests

    /**
     * Verifies basic creation and reading of single events across presets.
     * @dataProvider provideEventPresets
     */
    public function testCreateAndReadEventPresets(string $presetKey): void
    {
        /** @var FactoryMapperEvents $eventMapFactory */
        global $eventMapFactory;

        $ownerID = 'profile-test-owner-001';
        $event   = $eventMapFactory->makePresetEvent($presetKey);

        //-------------------------------
        $insertedID = $this->mapper->save($event, $ownerID);

        $retrieved = $this->mapper->read($insertedID);
        //-------------------------------

        $this->assertGreaterThan(0, $insertedID);
        $this->assertNotNull($retrieved);
        $this->assertEqualsEvent($event, $retrieved);
    }

    /**
     * Verifies reading non-existent event ID returns null.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $this->assertNull($this->mapper->read(999999));
    }

    /**
     * Verifies readArray hydrations omit raw *_id columns and include nested sub-arrays.
     */
    public function testReadArrayHydratesSubObjectsAndUnsetsForeignKeys(): void
    {
        /** @var FactoryMapperEvents $eventMapFactory */
        global $eventMapFactory;

        $ownerID = 'profile-test-owner-002';
        $event   = $eventMapFactory->makePresetEvent('birth');

        $insertedID = $this->mapper->save($event, $ownerID);

        //-------------------------------
        $row = $this->mapper->readArray($insertedID);
        //-------------------------------

        $this->assertIsArray($row);
        $this->assertArrayNotHasKey('date_id', $row);
        $this->assertArrayNotHasKey('location_id', $row);
        $this->assertArrayHasKey('date', $row);
        $this->assertArrayHasKey('location', $row);
    }

    // =====================================================
    // Batch Operations & Global Chronological Sorting

    /**
     * Verifies readAll globally sorts returned events chronologically,
     * regardless of insertion order into the database.
     */
    public function testReadAllReturnsChronologicallySortedEvents(): void
    {
        /** @var FactoryMapperEvents $eventMapFactory */
        global $eventMapFactory;

        $ownerID = 'profile-test-owner-003';

        $death    = $eventMapFactory->makePresetEvent('death');    // 1799
        $birth    = $eventMapFactory->makePresetEvent('birth');    // 1732
        $marriage = $eventMapFactory->makePresetEvent('marriage'); // 1759
        $undated  = $eventMapFactory->makePresetEvent('undated');  // No date (sorts last)

        // Intentionally insert out of chronological order
        $this->mapper->create($death, $ownerID);
        $this->mapper->create($birth, $ownerID);
        $this->mapper->create($marriage, $ownerID);
        $this->mapper->create($undated, $ownerID);

        //-------------------------------
        $retrievedEvents = $this->mapper->readAll($ownerID);
        //-------------------------------

        $this->assertCount(4, $retrievedEvents);

        // Assert chronological sequence
        $this->assertEquals('birth', $retrievedEvents[0]->getName()); // 1732
        $this->assertEquals('marriage', $retrievedEvents[1]->getName()); // 1759
        $this->assertEquals('death', $retrievedEvents[2]->getName()); // 1799
        $this->assertEquals('residence', $retrievedEvents[3]->getName()); // Undated
    }

    /**
     * Verifies createAll sorts input events chronologically prior to saving.
     */
    public function testCreateAllSortsAndPersistsEvents(): void
    {
        /** @var FactoryMapperEvents $eventMapFactory */
        global $eventMapFactory;

        $ownerID = 'profile-test-owner-004';

        $unsortedEvents = [
            $eventMapFactory->makePresetEvent('death'),    // 1799
            $eventMapFactory->makePresetEvent('birth'),    // 1732
            $eventMapFactory->makePresetEvent('marriage'), // 1759
        ];

        //-------------------------------
        $this->mapper->createAll($unsortedEvents, $ownerID);
        //-------------------------------

        $retrievedEvents = $this->mapper->readAll($ownerID);

        $this->assertCount(3, $retrievedEvents);
        $this->assertEquals('birth', $retrievedEvents[0]->getName());
        $this->assertEquals('marriage', $retrievedEvents[1]->getName());
        $this->assertEquals('death', $retrievedEvents[2]->getName());
    }

    // =====================================================
    // Deletion Tests

    /**
     * Verifies that deleting an event also cascades deletion to its sub-objects.
     */
    public function testDeleteEventCascadesToSubObjects(): void
    {
        /** @var FactoryMapperEvents $eventMapFactory */
        global $eventMapFactory;

        $ownerID = 'profile-test-owner-005';
        $event   = $eventMapFactory->makePresetEvent('birth');

        $insertedID = $this->mapper->save($event, $ownerID);
        $row        = $this->mapper->readArray($insertedID);
        $this->assertNotNull($row);

        //-------------------------------
        $this->mapper->delete($insertedID);
        //-------------------------------

        $this->assertNull($this->mapper->read($insertedID));
    }

    /**
     * Verifies deleteAll removes all events for an owner.
     */
    public function testDeleteAll(): void
    {
        /** @var FactoryMapperEvents $eventMapFactory */
        global $eventMapFactory;

        $ownerID = 'profile-test-owner-006';

        $this->mapper->createAll([
            $eventMapFactory->makePresetEvent('birth'),
            $eventMapFactory->makePresetEvent('marriage'),
        ], $ownerID);

        $this->assertCount(2, $this->mapper->readAll($ownerID));

        //-------------------------------
        $this->mapper->deleteAll($ownerID);
        //-------------------------------

        $this->assertCount(0, $this->mapper->readAll($ownerID));
    }
}
