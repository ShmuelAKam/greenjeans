<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Project;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProjects;

/**
 * API_Project test case.
 */
class API_ProjectTest extends API_BaseTest
{
    private ?API_Project $aPI_Project;

    public static function setUpBeforeClass(): void {}

    public static function tearDownAfterClass(): void {}

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aPI_Project = new API_Project();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->aPI_Project = null;

        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * @return array <array <int>>
     */
    public static function provideAddSetSize(): array
    {
        // Return number of profiles to add.
        return [
            [  5 ],
            [  8 ],
            [ 10 ]
        ];
    }

    // =====================================================
    // Helper functions.

    /**
     * @param array <string> $addedObjectIDs
     * @param array <int|array <string>> $addReply
     */
    private function assertAddedObjects(array $addedObjectIDs, array $addReply): void
    {
        // Structure of $AddReply is:
        // [
        // 'Num Added' => int - count of added.
        // 'Not Added' => Array - IDs NOT added
        // ]
        $NumAdded  = $addReply ['Num Added'];
        $NOT_Added = $addReply ['Not Added'];

        $this->assertEquals($NumAdded, count($addedObjectIDs), 'Failed to add ' . count($NOT_Added) . ' profiles.');
        $this->assertEquals(0, count($NOT_Added), "'Not added' is not empty.");
    }

    // =====================================================
    // Test functions.

    /**
     * Tests API_Project->get ()
     */
    public function test__get(): void
    {
        // --------------------------------------------------
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);
        // --------------------------------------------------

        $this->assertNotNull($this->aPI_Project);

        $projName = $this->aPI_Project->getName();

        $this->assertEquals(FactoryProjects::TEST_PROJECT ['Name'], $projName);
    }

    /**
     * Tests API_Project->get ()
     */
    public function test__get_NotExist(): void
    {
        // --------------------------------------------------
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['NON_Exist']);
        // --------------------------------------------------

        $this->assertNull($this->aPI_Project);
    }

    // ==========================================================

    /**
     * Tests API_Project::verifyProjectExists()
     */
    public function testVerifyProjectExists(): void
    {
        // --------------------------------------------------
        $exists = API_Project::verifyProjectExists(FactoryProjects::PROJECT_IDS ['Test_Project']);
        // --------------------------------------------------

        $this->assertTrue($exists);
    }

    /**
     * Tests API_Project::verifyProjectExists()
     */
    public function testVerifyProjectExists_NOT(): void
    {
        // --------------------------------------------------
        $exists = API_Project::verifyProjectExists(FactoryProjects::PROJECT_IDS ['NON_Exist']);
        // --------------------------------------------------

        $this->assertFalse($exists);
    }

    /**
     * Tests API_Project->addProfiles()
     */
    public function testAddProfiles_Empty(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        $profilesToAdd = [];

        // --------------------------------------------------
        $addReply = $this->aPI_Project->addProfiles($profilesToAdd);
        // --------------------------------------------------

        $this->assertArrayHasKey('Not Added', $addReply);
        $this->assertArrayHasKey('Num Added', $addReply);
        $this->assertEquals(0, $addReply['Num Added']);
        $this->assertEmpty($addReply['Not Added']);
    }

    /**
     * Tests API_Project->addProfiles()
     *
     * @dataProvider provideAddSetSize
     */
    public function testAddProfiles_ID(int $addCount): void
    {
        global $profileFactory;

        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // Projects can only contain profiles that are dead and public.
        $profilesToAdd = $profileFactory->makeProfiles($addCount, allDead: true, allPublic: true);
        $profileIDs    = $profileFactory->makeArrayIDs($profilesToAdd);

        // --------------------------------------------------
        $AddReply = $this->aPI_Project->addProfiles($profileIDs, isGUID: false);
        // --------------------------------------------------

        $this->assertAddedObjects($profileIDs, $AddReply);
    }

    /**
     * Tests API_Project->addProfiles()
     *
     * @dataProvider provideAddSetSize
     */
    public function testAddProfiles_GUID(int $addCount): void
    {
        global $profileFactory;

        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        $profilesToAdd = $profileFactory->makeProfiles($addCount, allDead: true, allPublic: true);
        $profileIDs    = $profileFactory->makeArrayIDs($profilesToAdd, getID: false);

        // --------------------------------------------------
        $AddReply = $this->aPI_Project->addProfiles($profileIDs, isGUID: true);
        // --------------------------------------------------

        $this->assertAddedObjects($profileIDs, $AddReply);
    }

    /**
     * Tests API_Project->addDocuments()
     *
     * @dataProvider provideAddSetSize
     */
    public function testAddDocuments(int $addCount): void
    {
        global $documentFactory;

        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        $documentsToAdd = $documentFactory->makedocuments($addCount);
        $documentIDs    = $documentFactory->makeArrayIDs($documentsToAdd, getID: true);

        // --------------------------------------------------
        $AddReply = $this->aPI_Project->addDocuments($documentIDs);
        // --------------------------------------------------

        $this->assertAddedObjects($documentIDs, $AddReply);
    }

    /**
     * Tests API_Project->getCollaborators()
     */
    public function testGetCollaborators(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // --------------------------------------------------
        $listOfCollaborators = $this->aPI_Project->getCollaborators(getAll: true);
        // --------------------------------------------------

        $list = $listOfCollaborators->getList();

        $myID = 'profile-9288190';

        $this->assertEquals(1, count($listOfCollaborators), 'Wrong number of collaborators.');
        $this->assertEquals(1, count($list), 'Wrong number of collaborators.');
        $this->assertArrayHasKey($myID, $list);
    }

    /**
     * Tests API_Project->getCollaborators()
     */
    public function testGetCollaborators_Biblical(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Bible_Tree']);

        // --------------------------------------------------
        $listOfCollaborators = $this->aPI_Project->getCollaborators();
        // --------------------------------------------------

        $list = $listOfCollaborators->getList();

        $this->assertGreaterThanOrEqual(50, count($listOfCollaborators), 'Wrong number of collaborators (list obj).');
        $this->assertGreaterThanOrEqual(50, count($list), 'Wrong number of collaborators (list).');
    }

    /**
     * Tests API_Project->getCollaborators()
     */
    public function testGetCollaborators_Biblical_GetAll(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Bible_Tree']);

        // --------------------------------------------------
        $listOfCollaborators = $this->aPI_Project->getCollaborators(getAll: true);
        // --------------------------------------------------

        $list = $listOfCollaborators->getList();

        $myID = 'profile-9288190';

        $this->assertGreaterThan(50, count($listOfCollaborators), 'Wrong number of collaborators (list obj).');
        $this->assertGreaterThan(50, count($list), 'Wrong number of collaborators (list).');
        $this->assertArrayHasKey($myID, $list);
    }

    /**
     * Tests API_Project->getFollowers()
     */
    public function testGetFollowers(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // --------------------------------------------------
        $listOfFollowers = $this->aPI_Project->getFollowers();
        // --------------------------------------------------

        $list = $listOfFollowers->getList();

        $myID = 'profile-9288190';

        $this->assertArrayHasKey($myID, $list);
        $this->assertCount(1, $listOfFollowers);
    }

    /**
     * Tests API_Project->getFollowers()
     */
    public function testGetFollowers_Biblical(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Bible_Tree']);

        // --------------------------------------------------
        $listOfFollowers = $this->aPI_Project->getFollowers();
        // --------------------------------------------------

        $list = $listOfFollowers->getList();

        $this->assertGreaterThanOrEqual(50, count($listOfFollowers), 'Wrong number of followers (list obj).');
        $this->assertGreaterThanOrEqual(50, count($list), 'Wrong number of followers (list).');
    }

    /**
     * Tests API_Project->getFollowers()
     */
    public function testGetFollowers_Biblical_GetAll(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Bible_Tree']);

        // --------------------------------------------------
        $listOfFollowers = $this->aPI_Project->getFollowers(getAll: true);
        // --------------------------------------------------

        $list = $listOfFollowers->getList();

        $myID = 'profile-9288190';

        $this->assertArrayHasKey($myID, $list);
        $this->assertGreaterThan(50, count($listOfFollowers), 'Wrong number of followers (list obj).');
        $this->assertGreaterThan(50, count($list), 'Wrong number of followers (list).');
    }

    /**
     * Tests API_Project->getFollowers()
     */
    public function testGetFollowers_NONE(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Empty_Project']);

        // --------------------------------------------------
        $listOfFollowers = $this->aPI_Project->getFollowers();
        // --------------------------------------------------

        $list = $listOfFollowers->getList();

        $this->assertEquals(0, count($listOfFollowers), 'Followers in project.');
        $this->assertEquals(0, count($list), 'Followers in project.');
    }

    /**
     * Tests API_Project->getProfiles()
     */
    public function testGetProfiles(): void
    {
        $TEST_PROJECT_PROFILES = [
            'profile-34838408159', // Adam Test Profile .
            'profile-34838408177', // Seth Test Profile .
            'profile-34838408195'  // Enosh Test Profile .
        ];

        $projectID         = FactoryProjects::PROJECT_IDS ['TEST_Biblical'];
        $this->aPI_Project = API_Project::get($projectID); // Set-up

        // --------------------------------------------------
        $listOfProfiles = $this->aPI_Project->getProfiles(IDsOnly: true);
        // --------------------------------------------------

        $listGet = $listOfProfiles->getListIDs();

        sort($TEST_PROJECT_PROFILES);
        sort($listGet);

        $difference = array_diff($listGet, $TEST_PROJECT_PROFILES);

        $this->assertEquals(count($listOfProfiles), count($TEST_PROJECT_PROFILES), 'Failed to get all profiles.');
        $this->assertEquals(0, count($difference), 'Not all profiles in project.');
    }

    /**
     * Tests API_Project->getProfiles()
     */
    public function testGetProfiles_Empty(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Empty_Project']);

        // -------------------------------
        $listOfProfiles = $this->aPI_Project->getProfiles();
        // -------------------------------

        $this->assertEquals(0, count($listOfProfiles), 'No profiles in project.');
    }

    /**
     * Tests API_Project->getProfiles()
     */
    public function testGetProfiles_Biblical(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Bible_Tree']);

        // -------------------------------
        $listOfProfiles = $this->aPI_Project->getProfiles(getAll: false);
        // -------------------------------

        $list = $listOfProfiles->getList();

        $pageCount  = $listOfProfiles->getPageCount();
        $listCount  = count($listOfProfiles);
        $arrayCount = count($list);

        $this->assertEquals(50, $pageCount, 'Wrong number of profiles (list page).');
        $this->assertEquals(50, $listCount, 'Wrong number of profiles (list obj).');
        $this->assertEquals(50, $arrayCount, 'Wrong number of profiles (list).');
    }

    /**
     * Tests API_Project->getProfiles()
     */
    public function testGetProfiles_Biblical_GetAll(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Bible_Tree']);

        // -------------------------------
        $listOfProfiles = $this->aPI_Project->getProfiles(getAll: true, IDsOnly: true);
        // -------------------------------

        $listArray  = $listOfProfiles->getList();
        $listCount  = count($listOfProfiles);
        $arrayCount = count($listArray);

        $this->assertGreaterThan(50, $listCount, 'Wrong number of profiles (list obj).');
        $this->assertGreaterThan(50, $arrayCount, 'Wrong number of profiles (list).');
        $this->assertEqualsWithDelta($listCount, $this->aPI_Project->getNumProfiles(), 3);

        // A Server error returns slightly different values.
        $this->assertEqualsWithDelta($listCount, $arrayCount, 3);
    }

    /**
     * Tests API_Project->getNumProfiles()
     */
    public function testGetNumProfiles(): void
    {
        $TEST_PROJECT_PROFILES = [
            'profile-34838408159', // Adam Test Profile .
            'profile-34838408177', // Seth Test Profile .
            'profile-34838408195', // Enosh Test Profile .
        ];

        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['TEST_Biblical']);

        $this->aPI_Project->addProfiles($TEST_PROJECT_PROFILES); // Set-Up

        // --------------------------------------------------
        $NumProfiles = $this->aPI_Project->getNumProfiles();
        // --------------------------------------------------

        $this->assertEquals(count($TEST_PROJECT_PROFILES), $NumProfiles, 'Num profiles in Project does not match.');
    }

    /**
     * Tests API_Project->getNumProfiles()
     */
    public function testGetNumProfiles_Biblical(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Bible_Tree']);

        $listOfProfiles = $this->aPI_Project->getProfiles(); // Set-Up

        // --------------------------------------------------
        $NumProfiles = $this->aPI_Project->getNumProfiles();
        // --------------------------------------------------

        $this->assertEquals($NumProfiles, $listOfProfiles->getTotalCount(), 'Num profiles in Project does not match.');
    }

    /**
     * Tests API_Project->getNumProfiles()
     */
    public function testGetNumProfiles_Empty(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Empty_Project']);

        // --------------------------------------------------
        $NumProfiles = $this->aPI_Project->getNumProfiles();
        // --------------------------------------------------

        $this->assertEquals($NumProfiles, 0, 'Project is not empty.');
    }

    /**
     * Tests API_Project->getID()
     */
    public function testGetID(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // --------------------------------------------------
        $UID = $this->aPI_Project->getID();
        // --------------------------------------------------

        $this->assertEquals(FactoryProjects::PROJECT_IDS ['Test_Project'], $UID, 'Project UID not as expected.');
    }

    /**
     * Tests API_Project->getName()
     */
    public function testGetName(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // --------------------------------------------------
        $Name = $this->aPI_Project->getName();
        // --------------------------------------------------

        $this->assertEquals(FactoryProjects::TEST_PROJECT ['Name'], $Name, 'Got Project Name.');
    }

    /**
     * Tests API_Project->getDescription()
     */
    public function testGetDescription(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // --------------------------------------------------
        $description = $this->aPI_Project->getDescription();
        // --------------------------------------------------

        $expectedDescription = FactoryProjects::TEST_PROJECT ['Description'];

        $this->assertEquals($expectedDescription, $description, 'Got Project Description.');
    }

    /**
     * Tests API_Project->getCreatedAt()
     */
    public function testGetCreatedAt(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // --------------------------------------------------
        $createdAt = $this->aPI_Project->getCreatedAt();
        // --------------------------------------------------

        $this->assertEquals(FactoryProjects::TEST_PROJECT ['Created_at'], $createdAt, 'Got Project CreatedAt.');
    }

    /**
     * Tests API_Project->getUpdatedAt()
     */
    public function testGetUpdatedAt(): void
    {
        global $profileFactory;

        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // Projects can only contain profiles that are dead and public.
        $profile   = $profileFactory->makeRandomProfile(alive: false, public: true);
        $profileID = $profile->getID();

        // Add profile to protect to update it.
        $this->aPI_Project->addProfiles([ $profileID ]); // Set-up.

        $updateTime = time();

        // --------------------------------------------------
        $updatedAt = (int) $this->aPI_Project->getUpdatedAt();
        // --------------------------------------------------

        $this->assertEqualsWithDelta($updateTime, $updatedAt, 50, 'Project UpdatedAt delayed.');
    }

    /**
     * Tests API_Project->getURL()
     */
    public function testGetURL(): void
    {
        $this->aPI_Project = API_Project::get(FactoryProjects::PROJECT_IDS ['Test_Project']);

        // --------------------------------------------------
        $projectURL = $this->aPI_Project->getURL();
        // --------------------------------------------------

        $this->assertEquals(FactoryProjects::TEST_PROJECT ['URL'], $projectURL, 'Got Project URL.');
    }
}
