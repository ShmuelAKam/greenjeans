<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Revision;

const NULL_REVISION = new TestRevision();

// TODO creat actual revisions.
const TEST_REVISION_1 = new TestRevision('1');
const TEST_REVISION_2 = new TestRevision('2');
const TEST_REVISION_3 = new TestRevision('3');

/**
 * API_Revision test case.
 */
class API_RevisionTest extends API_BaseTest
{
    private ?API_Revision $aPI_Revision;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aPI_Revision = null;
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->aPI_Revision = null;

        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * Hard-coded Revision objects.
     * @return array <array <TestRevision>>
     */
    public static function provideRevision(): array
    {
        return [
            [ TEST_REVISION_1 ],
            [ TEST_REVISION_2 ],
            [ TEST_REVISION_3 ],
        ];
    }

    // =====================================================
    // Helper functions.
    private function assertRevisions(TestRevision $testRevision, API_Revision $getRevision): void
    {
        $this->assertEquals($testRevision->action, $getRevision->getAction(), 'Action');
        $this->assertEquals($testRevision->dateLocal, $getRevision->getDateLocal(), 'Date Local');
        $this->assertEquals($testRevision->GUID, $getRevision->getGUID(), 'GUID');
        $this->assertEquals($testRevision->ID, $getRevision->getID(), 'ID');
        $this->assertEquals($testRevision->story, $getRevision->getStory(), 'Story');
        $this->assertEquals($testRevision->timeLocal, $getRevision->getTimeLocal(), 'Time Local');
        $this->assertEquals($testRevision->timestamp, $getRevision->getTimestamp(), 'Timestamp');
    }

    // =====================================================
    // Test functions.

    // =====================================================
    // Test GET functions.

    /**
     * Tests API_Revision->get()
     *
     * @dataProvider provideRevision
     */
    public function testGet(TestRevision $test): void
    {
        // --------------------------------------------------
        $revision = $this->aPI_Revision = API_Revision::get($test->ID);
        // --------------------------------------------------

        $this->assertRevisions($test, $revision);
    }

    // =====================================================

    /**
     * Tests API_Revision->getAction()
     *
     * @dataProvider provideRevision
     */
    public function testGetAction(TestRevision $test): void
    {
        $this->aPI_Revision = API_Revision::get($test->ID); // Set-up.

        // --------------------------------------------------
        $action = $this->aPI_Revision->getAction();
        // --------------------------------------------------

        $this->assertEquals($test->action, $action);
    }

    /**
     * Tests API_Revision->getDateLocal()
     *
     * @dataProvider provideRevision
     */
    public function testGetDateLocal(TestRevision $test): void
    {
        $this->aPI_Revision = API_Revision::get($test->ID); // Set-up.

        // --------------------------------------------------
        $dateLocal = $this->aPI_Revision->getDateLocal();
        // --------------------------------------------------

        $this->assertEquals($test->dateLocal, $dateLocal);
    }

    /**
     * Tests API_Revision->getGUID()
     *
     * @dataProvider provideRevision
     */
    public function testGetGUID(TestRevision $test): void
    {
        $this->aPI_Revision = API_Revision::get($test->ID); // Set-up.

        // --------------------------------------------------
        $GUID = $this->aPI_Revision->getGUID();
        // --------------------------------------------------

        $this->assertEquals($test->GUID, $GUID);
    }

    /**
     * Tests API_Revision->getID()
     *
     * @dataProvider provideRevision
     */
    public function testGetID(TestRevision $test): void
    {
        $this->aPI_Revision = API_Revision::get($test->ID); // Set-up.

        // --------------------------------------------------
        $ID = $this->aPI_Revision->getID();
        // --------------------------------------------------

        $this->assertEquals($test->ID, $ID);
    }

    /**
     * Tests API_Revision->getStory()
     *
     * @dataProvider provideRevision
     */
    public function testGetStory(TestRevision $test): void
    {
        $this->aPI_Revision = API_Revision::get($test->ID); // Set-up.

        // --------------------------------------------------
        $story = $this->aPI_Revision->getStory();
        // --------------------------------------------------

        $this->assertEquals($test->story, $story);
    }

    /**
     * Tests API_Revision->getTimeLocal()
     *
     * @dataProvider provideRevision
     */
    public function testGetTimeLocal(TestRevision $test): void
    {
        $this->aPI_Revision = API_Revision::get($test->ID); // Set-up.

        // --------------------------------------------------
        $timeLocal = $this->aPI_Revision->getTimeLocal();
        // --------------------------------------------------

        $this->assertEquals($test->timeLocal, $timeLocal);
    }

    /**
     * Tests API_Revision->getTimestamp()
     *
     * @dataProvider provideRevision
     */
    public function testGetTimestamp(TestRevision $test): void
    {
        $this->aPI_Revision = API_Revision::get($test->ID); // Set-up.

        // --------------------------------------------------
        $timestamp = $this->aPI_Revision->getTimestamp();
        // --------------------------------------------------

        $this->assertEquals($test->timestamp, $timestamp);
    }
}

/**
 * Test struct to compare with Revision objects.
 * Mirrors Revision object.
 */
class TestRevision
{
    // Action of the revision
    public string $action;

    // Date of the revision in the local date
    public string $dateLocal;
    public string $GUID;
    public string $ID;

    // HTML story containing full revision description
    public string $story;

    // Time of the revision in the local time
    public string $timeLocal;

    // Server time of the revision
    public string $timestamp;

    public function __construct(
        string $action = "",
        string $dateLocal = "",
        string $GUID = "",
        string $ID = "",
        string $story = "",
        string $timeLocal = "",
        string $timestamp = "",
    ) {
        $this->action    = $action;
        $this->dateLocal = $dateLocal;
        $this->GUID      = $GUID;
        $this->ID        = $ID;
        $this->story     = $story;
        $this->timeLocal = $timeLocal;
        $this->timestamp = $timestamp;
    }
}
