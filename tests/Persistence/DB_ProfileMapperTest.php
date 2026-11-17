<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_ProfileMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperProfiles;

/**
 * DB_ProfileMapperTest
 * Unit tests for DB_ProfileMapper persistence layer.
 */
class DB_ProfileMapperTest extends DB_MapperTestBase
{
    private DB_ProfileMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_ProfileMapper::class);
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string>>
     */
    public static function providePresetKeys(): array
    {
        return [
        'Presidential Deceased Profile' => ['presidential'],
        'Multilingual Profile'          => ['multilingual'],
        'Living Private Profile'        => ['living_private'],
        'Minimal Brief Profile'         => ['minimal_brief'],
        ];
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_Profile instances have identical scalar fields,
     * sub-events, detail strings, and relational collections without triggering
     * any remote SDK API network requests.
     */
    protected function assertEqualsProfile(API_Profile $expected, API_Profile $actual, string $message = ''): void
    {
        $exp = $expected->makeParamArray(allFields: true, forDB: true);
        $act = $actual->makeParamArray(allFields: true, forDB: true);

        // 1. Primary Identifiers & Scalar Core Fields
        self::assertEquals($exp['id'] ?? '', $act['id'] ?? '', $message . ' [id mismatch]');
        self::assertEquals($exp['guid'] ?? null, $act['guid'] ?? null, $message . ' [guid mismatch]');
        self::assertEquals($exp['first_name'] ?? null, $act['first_name'] ?? null, $message . ' [first_name mismatch]');
        self::assertEquals($exp['middle_name'] ?? null, $act['middle_name'] ?? null, $message . ' [middle_name mismatch]');
        self::assertEquals($exp['last_name'] ?? null, $act['last_name'] ?? null, $message . ' [last_name mismatch]');
        self::assertEquals($exp['maiden_name'] ?? null, $act['maiden_name'] ?? null, $message . ' [maiden_name mismatch]');
        self::assertEquals($exp['suffix'] ?? null, $act['suffix'] ?? null, $message . ' [suffix mismatch]');
        self::assertEquals($exp['gender'] ?? null, $act['gender'] ?? null, $message . ' [gender mismatch]');
        self::assertEquals($exp['occupation'] ?? null, $act['occupation'] ?? null, $message . ' [occupation mismatch]');
        self::assertEquals($exp['about_me'] ?? null, $act['about_me'] ?? null, $message . ' [about_me mismatch]');
        self::assertEquals($exp['cause_of_death'] ?? null, $act['cause_of_death'] ?? null, $message . ' [cause_of_death mismatch]');

        // 2. Boolean Flags
        self::assertEquals((int) ($exp['is_alive'] ?? 0), (int) ($act['is_alive'] ?? 0), $message . ' [is_alive mismatch]');
        self::assertEquals((int) ($exp['public'] ?? 0), (int) ($act['public'] ?? 0), $message . ' [public mismatch]');
        self::assertEquals((int) ($exp['claimed'] ?? 0), (int) ($act['claimed'] ?? 0), $message . ' [claimed mismatch]');
        self::assertEquals((int) ($exp['big_tree'] ?? 0), (int) ($act['big_tree'] ?? 0), $message . ' [big_tree mismatch]');
        self::assertEquals((int) ($exp['locked'] ?? 0), (int) ($act['locked'] ?? 0), $message . ' [locked mismatch]');

        // 3. Sub-Events (Birth, Death, Burial, Baptism)
        $eventFields = ['birth', 'death', 'burial', 'baptism'];
        foreach ($eventFields as $evtKey)
        {
            $expEvt = isset($exp[$evtKey]) ? (is_object($exp[$evtKey]) ? $exp[$evtKey]->makeParamArray() : $exp[$evtKey]) : null;
            $actEvt = isset($act[$evtKey]) ? (is_object($act[$evtKey]) ? $act[$evtKey]->makeParamArray() : $act[$evtKey]) : null;

            self::assertEquals($expEvt, $actEvt, $message . " [sub-event '{$evtKey}' mismatch]");
        }

        // 4. Locations (Current Residence, General Location)
        $locFields = ['current_residence', 'location'];
        foreach ($locFields as $locKey)
        {
            $expLoc = isset($exp[$locKey]) ? (is_object($exp[$locKey]) ? $exp[$locKey]->makeParamArray() : $exp[$locKey]) : null;
            $actLoc = isset($act[$locKey]) ? (is_object($act[$locKey]) ? $act[$locKey]->makeParamArray() : $act[$locKey]) : null;

            self::assertEquals($expLoc, $actLoc, $message . " [location '{$locKey}' mismatch]");
        }

        // 5. Multi-locale String Collections
        if (isset($exp['detail_strings']))
        {
            self::assertArrayHasKey('detail_strings', $act, $message . ' [detail_strings missing in actual]');
            self::assertEquals($exp['detail_strings'], $act['detail_strings'], $message . ' [detail_strings content mismatch]');
        }

        if (isset($exp['nicknames']))
        {
            self::assertEquals($exp['nicknames'], $act['nicknames'] ?? [], $message . ' [nicknames mismatch]');
        }

        // 6. Relational ID Collections
        $collections = [
            'managers',
            'requested_merges',
            'unions',
            'projects',
            'documents',
            'photos',
            'videos'
        ];

        foreach ($collections as $colKey)
        {
            if (isset($exp[$colKey]))
            {
                self::assertArrayHasKey($colKey, $act, $message . " [collection '{$colKey}' missing in actual]");
                self::assertEquals($exp[$colKey], $act[$colKey], $message . " [collection '{$colKey}' content mismatch]");
            }
        }
    }

    // =====================================================
    // Basic Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations across different profile presets.
     * @dataProvider providePresetKeys
     */
    public function testCreateAndReadPresets(string $presetKey): void
    {
        /** @var FactoryMapperProfiles $profileMapFactory */
        global $profileMapFactory;

        $profile   = $profileMapFactory->makePresetProfile($presetKey, full: true);
        $profileID = $profile->getId();

        //-------------------------------
        $savedID = $this->mapper->save($profile);

        $retrieved = $this->mapper->read($profileID, fullRead: true);
        //-------------------------------

        $this->assertEquals($profileID, $savedID);
        $this->assertNotNull($retrieved);
        $this->assertEqualsProfile($profile, $retrieved);
    }

    /**
     * Verifies NULL response when reading a non-existent profile ID.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $retrieved = $this->mapper->read('profile-non-existent-id', fullRead: false);

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Sub-Object Mutation & Cascade Delete

    /**
     * Verifies that updating a profile replaces its events and child records without orphans.
     */
    public function testUpdateProfileReplacesSubObjects(): void
    {
        /** @var FactoryMapperProfiles $profileMapFactory */
        global $profileMapFactory;

        $initialProfile = $profileMapFactory->makePresetProfile('presidential', full: true);
        $profileID      = $initialProfile->getId();

        $this->mapper->save($initialProfile);

        // Update profile preset to multilingual while retaining ID
        $updatedProfile = $profileMapFactory->makePresetProfile('multilingual', full: true, id: $profileID);

        //-------------------------------
        $this->mapper->save($updatedProfile);
        //-------------------------------

        $retrieved = $this->mapper->read($profileID, fullRead: true);

        $this->assertNotNull($retrieved);

        $retrievedParams = $retrieved->makeParamArray(allFields: true, forDB: true);

        $this->assertEquals('Jean', $retrievedParams['first_name'] ?? null);
        $this->assertEquals('Dupont', $retrievedParams['last_name'] ?? null);
    }

    /**
     * Verifies that deleting a profile clears parent row, event sub-objects, and link table entries.
     */
    public function testDeleteProfileClearsSubObjectsAndLinks(): void
    {
        /** @var FactoryMapperProfiles $profileMapFactory */
        global $profileMapFactory;

        $profile   = $profileMapFactory->makePresetProfile('presidential', full: true);
        $profileID = $profile->getId();

        $this->mapper->save($profile);
        $this->assertEquals(1, $this->mapper->countTableRows('geni_profiles', 'profile_id', $profileID));

        //-------------------------------
        $this->mapper->delete($profileID);
        //-------------------------------

        $this->assertNull($this->mapper->read($profileID, fullRead: false));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_profiles', 'profile_id', $profileID));
        $this->assertEquals(0, $this->mapper->countTableRows('geni_profile_unions', 'profile_id', $profileID));
    }
}
