<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Project;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_ProjectMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperProjects;

/**
 * DB_ProjectMapperTest
 * Unit tests for DB_ProjectMapper persistence layer.
 */
class DB_ProjectMapperTest extends DB_MapperTestBase
{
    private DB_ProjectMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_ProjectMapper::class);
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string|NULL>>
     */
    public static function provideProjectUpdates(): array
    {
        return [
        'Update Name & Desc' => ['Updated Project Name', 'New Description'],
        'Update Name Only'   => ['Updated Project Name', null],
        ];
    }

    /**
     * @return array <string, array <string|int|NULL>>
     */
    public static function provideProject(): array
    {
        return FactoryMapperProjects::PROJECT_LIST;
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_Project instances have identical scalar values
     * and link table collections without triggering SDK network calls.
     */
    protected function assertEqualsProject(API_Project $expected, API_Project $actual, string $message = ''): void
    {
        // 1. Scalar fields
        self::assertEquals($expected->getID(), $actual->getID(), $message . ' [id mismatch]');
        self::assertEquals($expected->getName(), $actual->getName(), $message . ' [name mismatch]');
        self::assertEquals($expected->getDescription(), $actual->getDescription(), $message . ' [description mismatch]');
        self::assertEquals($expected->getCreatedAt(), $actual->getCreatedAt(), $message . ' [created_at mismatch]');
        self::assertEquals($expected->getUpdatedAt(), $actual->getUpdatedAt(), $message . ' [updated_at mismatch]');
        self::assertEquals($expected->getURL(), $actual->getURL(), $message . ' [url mismatch]');

        // 2. Relational Collection: Profiles
        $this->assertList('profiles', $expected, $actual, $message);

        // 3. Relational Collection: Collaborators
        $this->assertList('collaborators', $expected, $actual, $message);

        // 4. Relational Collection: Followers
        $this->assertList('followers', $expected, $actual, $message);
    }

    /**
     * Asserts row counts in link tables for a project.
     */
    protected function assertCollections(string $projectID, int $numProfiles, int $numCollabs, int $numFollowers, string $stage = ''): void
    {
        $countProfiles  = $this->mapper->countTableRows('geni_project_profiles', 'project_id', $projectID);
        $countCollabs   = $this->mapper->countTableRows('geni_project_collaborators', 'project_id', $projectID);
        $countFollowers = $this->mapper->countTableRows('geni_project_followers', 'project_id', $projectID);

        $this->assertEquals($numProfiles, $countProfiles, $stage . ' [profiles count]');
        $this->assertEquals($numCollabs, $countCollabs, $stage . ' [collaborators count]');
        $this->assertEquals($numFollowers, $countFollowers, $stage . ' [followers count]');
    }

    protected function assertList(string $listName, API_Project $expected, API_Project $actual, string $message = ''): void
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
     * Verifies basic create and read operations for an API_Project object.
     * @dataProvider provideProject
     */
    public function testCreateAndRead(
        string  $name,
        ?string $description,
        int     $numProfiles,
        int     $numCollabs,
        int     $numFollowers,
    ): void {
        /** @var FactoryMapperProjects $projectMapFactory */
        global $projectMapFactory;

        $project = $projectMapFactory->makeMapProject(
            $name,
            $description,
            $numProfiles,
            $numCollabs,
            $numFollowers,
            full: true,
        );
        $projectID = $project->getID();

        //-------------------------------
        $savedID = $this->mapper->save($project, fullSave: true);

        $retrieved = $this->mapper->read($projectID, fullRead: true);
        //-------------------------------

        $this->assertEquals($projectID, $savedID);

        $this->assertNotNull($retrieved);
        $this->assertEqualsProject($project, $retrieved);
        $this->assertCollections($projectID, $numProfiles, $numCollabs, $numFollowers, 'CreateAndRead');
    }

    /**
     * Verifies null response when reading a non-existent project ID.
     */
    public function testReadNonExistentReturnsNull(): void
    {
        $retrieved = $this->mapper->read('project-non-existent-id', fullRead: false);

        $this->assertNull($retrieved);
    }

    // =====================================================
    // Scalar Updates

    /**
     * @dataProvider provideProjectUpdates
     */
    public function testUpdateProjectScalarFields(?string $newName, ?string $newDesc): void
    {
        /** @var FactoryMapperProjects $projectMapFactory */
        global $projectMapFactory;

        $initialProject = $projectMapFactory->makeMapProject('Original Name', 'Original Desc', full: true);
        $projectID      = $initialProject->getID();

        $this->mapper->save($initialProject, fullSave: true);

        // Build updated object
        $params         = $projectMapFactory->makeParamArray($newName ?? 'Original Name', $newDesc, id: $projectID, full: false);
        $updatedProject = API_Project::newFromArray($params, true);

        $this->mapper->save($updatedProject, fullSave: false);

        $retrieved = $this->mapper->read($projectID, fullRead: false);

        $this->assertEquals($newName ?? 'Original Name', $retrieved->getName());
        if ($newDesc !== null)
        {
            $this->assertEquals($newDesc, $retrieved->getDescription());
        }
    }

    // =====================================================
    // Partial Hydration & Link Lifecycle Tests

    /**
     * Verifies that saving an unloaded partial project preserves link table rows.
     * @dataProvider provideProject
     */
    public function testPartialHydrationPreservesProjectLinks(
        string  $name,
        ?string $description,
        int     $numProfiles,
        int     $numCollabs,
        int     $numFollowers,
    ): void {
        /** @var FactoryMapperProjects $projectMapFactory */
        global $projectMapFactory;

        // 1. Save fully loaded project
        $initialParams = $projectMapFactory->makeParamArray(
            $name,
            $description,
            $numProfiles,
            $numCollabs,
            $numFollowers,
            id: null,
            full: true,
        );

        $initialProject = API_Project::newFromArray($initialParams, fromDB: true);
        $projectID      = $initialProject->getID();

        $this->mapper->save($initialProject, fullSave: true);
        $this->assertCollections($projectID, $numProfiles, $numCollabs, $numFollowers, 'Initial Save');

        // 2. Save partial project with unloaded collections
        $partialParams = $projectMapFactory->makeParamArray(
            $name,
            $description,
            0,
            0,
            0,
            id: $projectID,
            full: false,
        );

        $partialProject = API_Project::newFromArray($partialParams, fromDB: false);

        $this->mapper->save($partialProject, fullSave: false);

        // Collections in DB must remain intact
        $this->assertCollections($projectID, $numProfiles, $numCollabs, $numFollowers, 'After Partial Save');
    }

    // =====================================================
    // Cascade Delete Tests

    /**
     * Verifies that deleting a project purges the parent row and clear link tables.
     */
    public function testDeleteProjectClearsLinks(): void
    {
        /** @var FactoryMapperProjects $projectMapFactory */
        global $projectMapFactory;

        $projectParams = $projectMapFactory->makeParamArray(
            'Delete Project Test',
            'Desc',
            numProfiles: 3,
            numCollabs: 2,
            numFollowers: 1,
            full: true,
        );

        $project   = API_Project::newFromArray($projectParams, true);
        $projectID = $project->getID();

        $this->mapper->save($project, fullSave: true);
        $this->assertCollections($projectID, 3, 2, 1, 'Before Delete');

        $this->mapper->delete($projectID);

        $this->assertNull($this->mapper->read($projectID, fullRead: false));
        $this->assertCollections($projectID, 0, 0, 0, 'After Delete');
    }

    // =====================================================
    // Transaction Atomicity Tests

    /**
     * Verifies that multi-table project saves roll back cleanly on exception.
     */
    public function testProjectSaveRollbackOnException(): void
    {
        /** @var FactoryMapperProjects $projectMapFactory */
        global $projectMapFactory;

        $projectParams = $projectMapFactory->makeParamArray('Rollback Project', 'Desc', numProfiles: 2, full: true);
        $project       = API_Project::newFromArray($projectParams, true);
        $projectID     = $project->getID();

        $this->pdo->beginTransaction();

        try
        {
            $this->mapper->save($project, fullSave: true);

            // Force duplicate primary key exception
            $sql  = "INSERT INTO geni_projects (project_id, run_id) VALUES (:id, :rid)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $projectID, ':rid' => $this->runID]);

            $this->pdo->commit();
        }
        catch (\PDOException $e)
        {
            $this->pdo->rollBack();
        }

        $this->assertNull($this->mapper->read($projectID, fullRead: false));
        $this->assertCollections($projectID, 0, 0, 0, 'After Rollback');
    }
}
