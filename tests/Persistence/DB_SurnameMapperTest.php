<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Surname;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_SurnameMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperSurnames;

/**
 * DB_SurnameMapperTest
 * Unit tests for DB_SurnameMapper persistence layer.
 */
class DB_SurnameMapperTest extends DB_MapperTestBase
{
    private DB_SurnameMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_SurnameMapper::class);
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string|NULL>>
     */
    public static function provideSurnameUpdates(): array
    {
        return [
        'Update Slug & Desc' => ['smith-updated', 'Updated family history description'],
        'Update Slug Only'   => ['smith-updated', null],
        ];
    }

    /**
     * @return array <string, array <string|int|NULL>>
     */
    public static function provideSurname(): array
    {
        return FactoryMapperSurnames::SURNAME_LIST;
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_Surname instances have identical scalar values
     * and link table collections without triggering SDK network calls.
     */
    protected function assertEqualsSurname(API_Surname $expected, API_Surname $actual, string $message = ''): void
    {
        // 1. Scalar fields
        self::assertEquals($expected->getID(), $actual->getID(), $message . ' [id mismatch]');
        self::assertEquals($expected->getSluggedName(), $actual->getSluggedName(), $message . ' [slugged_name mismatch]');
        self::assertEquals($expected->getDescription(), $actual->getDescription(), $message . ' [description mismatch]');
        self::assertEquals($expected->getURL(), $actual->getURL(), $message . ' [url mismatch]');

        // 2. Relational Collection: Followers

        $this->assertList('followers', $expected, $actual, $message);

        // 3. Relational Collection: Profiles

        $this->assertList('profiles', $expected, $actual, $message);
    }

    /**
     * Asserts row counts in link tables for a surname.
     */
    protected function assertCollections(string $surnameID, int $numFollowers, int $numProfiles, string $stage = ''): void
    {
        $countFollowers = $this->mapper->countTableRows('geni_surname_followers', 'surname_id', $surnameID);
        $countProfiles  = $this->mapper->countTableRows('geni_surname_profiles', 'surname_id', $surnameID);

        $this->assertEquals($numFollowers, $countFollowers, $stage . ' [followers count]');
        $this->assertEquals($numProfiles, $countProfiles, $stage . ' [profiles count]');
    }

    protected function assertList(string $listName, API_Surname $expected, API_Surname $actual, string $message = ''): void
    {
        $expectedLoaded = $expected->isLoaded($listName);
        $actualLoaded   = $actual->isLoaded($listName);

        self::assertEquals($expectedLoaded, $actualLoaded, $message . " [$listName isLoaded mismatch]");

        if ($expectedLoaded)
        {
            $getterMethod = 'get' . ucfirst($listName);

            $expectedList = $expected->$getterMethod()->getListIDs();
            $actualList   = $actual  ->$getterMethod()->getListIDs();

            sort($expectedList);
            sort($actualList);

            self::assertEquals($expectedList, $actualList, $message . " [$listName list mismatch]");
        }

    }

    // =====================================================
    // Basic Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations for an API_Surname object.
     * @dataProvider provideSurname
     */
    public function testCreateAndRead(
        string  $sluggedName,
        ?string $description,
        int     $numFollowers,
        int     $numProfiles,
    ): void {
        /** @var FactoryMapperSurnames $surnameMapFactory */
        $surnameMapFactory = FactoryMapperBase::getFactory(API_Surname::class);

        $surname = $surnameMapFactory->makeMapSurname(
            $sluggedName,
            $description,
            $numFollowers,
            $numProfiles,
            full: true,
        );
        $surnameID = $surname->getID();

        //-------------------------------
        $savedID = $this->mapper->save($surname);

        $this->assertCollections($surnameID, $numFollowers, $numProfiles);

        $retrieved = $this->mapper->read($surnameID, fullRead: true);
        //-------------------------------

        $this->assertEquals($surnameID, $savedID);
        $this->assertNotNULL($retrieved);

        $this->assertEqualsSurname($surname, $retrieved);
        $this->assertCollections($surnameID, $numFollowers, $numProfiles, 'CreateAndRead');
    }

    /**
     * Verifies NULL response when reading a non-existent surname ID.
     */
    public function testReadNonExistentReturnsNULL(): void
    {
        $retrieved = $this->mapper->read('surname-non-existent-id', fullRead: false);

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Scalar Updates

    /**
     * @dataProvider provideSurnameUpdates
     */
    public function testUpdateSurnameScalarFields(?string $newSlug, ?string $newDesc): void
    {
        /** @var FactoryMapperSurnames $surnameMapFactory */
        $surnameMapFactory = FactoryMapperBase::getFactory(API_Surname::class);

        $initialSurname = $surnameMapFactory->makeMapSurname('original-slug', 'Original Desc', full: true);
        $surnameID      = $initialSurname->getID();

        $this->mapper->save($initialSurname);

        // Build updated object
        $params         = $surnameMapFactory->makeParamArray($newSlug ?? 'original-slug', $newDesc, id: $surnameID, full: false);
        $updatedSurname = API_Surname::newFromArray($params);

        //-------------------------------
        $this->mapper->save($updatedSurname);
        //-------------------------------

        $retrieved = $this->mapper->read($surnameID, fullRead: false);

        $this->assertEquals($newSlug ?? 'original-slug', $retrieved->getSluggedName());
        if ($newDesc !== null)
        {
            $this->assertEquals($newDesc, $retrieved->getDescription());
        }
    }

    // =====================================================
    // Partial Hydration & Link Lifecycle Tests

    /**
     * Verifies that saving an unloaded partial surname preserves link table rows.
     * @dataProvider provideSurname
     */
    public function testPartialHydrationPreservesSurnameLinks(string  $sluggedName, ?string $description, int $numFollowers, int $numProfiles): void
    {
        /** @var FactoryMapperSurnames $surnameMapFactory */
        $surnameMapFactory = FactoryMapperBase::getFactory(API_Surname::class);

        // 1. Save fully loaded surname
        $initialParams = $surnameMapFactory->makeParamArray(
            $sluggedName,
            $description,
            $numFollowers,
            $numProfiles,
            id: null,
            full: true,
        );

        $initialSurname = API_Surname::newFromArray($initialParams, fromDB: true);
        $surnameID      = $initialSurname->getID();

        $this->mapper->save($initialSurname);

        $this->assertCollections($surnameID, $numFollowers, $numProfiles, 'Initial Save');

        // 2. Save partial surname with unloaded collections
        $partialParams = $surnameMapFactory->makeParamArray(
            $sluggedName,
            $description,
            0,
            0,
            id: $surnameID,
            full: false,
        );

        $partialSurname = API_Surname::newFromArray($partialParams);

        //-------------------------------
        $this->mapper->save($partialSurname);
        //-------------------------------

        // Collections in DB must remain intact
        $this->assertCollections($surnameID, $numFollowers, $numProfiles, 'After Partial Save');
    }

    // =====================================================
    // Cascade Delete Tests

    /**
     * Verifies that deleting a surname purges the parent row and clears link tables.
     * @dataProvider provideSurname
     */
    public function testDeleteSurnameClearsLinks(
        string  $sluggedName,
        ?string $description,
        int     $numFollowers,
        int     $numProfiles,
    ): void {
        /** @var FactoryMapperSurnames $surnameMapFactory */
        $surnameMapFactory = FactoryMapperBase::getFactory(API_Surname::class);

        $surnameParams = $surnameMapFactory->makeParamArray(
            $sluggedName,
            $description,
            $numFollowers,
            $numProfiles,
            full: true,
        );

        $surname   = API_Surname::newFromArray($surnameParams, fromDB: true);
        $surnameID = $surname->getID();

        $this->mapper->save($surname);

        $this->assertCollections($surnameID, $numFollowers, $numProfiles, 'Before Delete');

        //-------------------------------
        $this->mapper->delete($surnameID);
        //-------------------------------

        $this->assertNull($this->mapper->read($surnameID, fullRead: false));
        $this->assertCollections($surnameID, 0, 0, 'After Delete');
    }

    // =====================================================
    // Transaction Atomicity Tests

    /**
     * Verifies that multi-table surname saves roll back cleanly on exception.
     */
    public function testSurnameSaveRollbackOnException(): void
    {
        /** @var FactoryMapperSurnames $surnameMapFactory */
        $surnameMapFactory = FactoryMapperBase::getFactory(API_Surname::class);

        $surnameParams = $surnameMapFactory->makeParamArray('rollback-surname', 'Desc', numFollowers: 2, full: true);
        $surname       = API_Surname::newFromArray($surnameParams);
        $surnameID     = $surname->getID();

        $this->getPDO()->beginTransaction();

        try
        {
            $this->mapper->save($surname);

            // Force duplicate primary key exception
            $sql  = "INSERT INTO geni_surnames (surname_id, run_id) VALUES (:id, :rid)";
            $stmt = $this->getPDO()->prepare($sql);
            $stmt->execute([':id' => $surnameID, ':rid' => $this->getRunID() ]);

            $this->getPDO()->commit();
        }
        catch (\PDOException $e)
        {
            $this->getPDO()->rollBack();
        }

        $this->assertNull($this->mapper->read($surnameID, fullRead: false));
        $this->assertCollections($surnameID, 0, 0, 'After Rollback');
    }
}
