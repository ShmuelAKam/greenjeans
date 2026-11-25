<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\API_Union;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryEvents;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProfiles;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryUnions;

/**
 * API_Union test case.
 */
class API_UnionTest extends API_BaseTest
{
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        parent::setUp();

        $unionFactory->reset();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * @return array <int, array <bool|int>>
     */
    public static function provideUnionParameters(): array
    {
        $unionParameters = [
            // Partner Biological Adopted Foster
            '1'  => [  true,   3,  0,  0  ],
            '2'  => [  true,   0,  5,  0  ],
            '3'  => [  true,   0,  0,  4  ],
            '4'  => [  true,   3,  3,  3  ],
            '5'  => [  true,   4,  0,  7  ],
            '6'  => [  false,  2,  0,  0  ],
            '7'  => [  false,  0,  3,  0  ],
            '8'  => [  false,  0,  0,  4  ],
            '9'  => [  false,  2,  3,  5  ],
            '10' => [  false,  4,  0,  7  ]
        ];

        return $unionParameters;
    }

    /**
     * Provides a set of FLAGS to determine which Marriage & Divorce Events to create.
     * Includes an additional value for the testGetStatus function, which contains the
     * expected result.
     *
     * @return array <array <bool|string>>
     */
    public static function provideMarriageDivorceEvents(): array
    {
        $eventFlags = [
        // Marriage, Divorce, Expected Status
        [  true,     true,    true,   'ex_spouse'  ],
        [  true,     false,   true,   'spouse'     ],
        [  false,    true,    true,   'ex_spouse'  ],
        [  false,    false,   false,  'spouse'     ],
        ];

        return $eventFlags;
    }

    // =====================================================
    // Helper functions.

    // =====================================================
    // Custom assert functions.

    private function assertEqualsUnion(API_Union $expected, API_Union $union, API_Profile $base, ?API_Profile $partner): void
    {
        $this->assertEquals($expected->getID(), $union->getID());
        $this->assertPartners($union, $base, $partner);

        $this->assertChildren($expected->getChildren(), $union->getChildren(), '');
        $this->assertChildren($expected->getAdoptedChildren(), $union->getAdoptedChildren(), 'adopted');
        $this->assertChildren($expected->getFosterChildren(), $union->getFosterChildren(), 'foster');
    }

    /**
     * @param array<string> $expectedChildren
     * @param array<string> $actual
     * @param string $type
     */
    private function assertChildren(array $expectedChildren, array $actual, string $type): void
    {
        $this->assertEquals(count($expectedChildren), count($actual), "Wrong number of $type children");

        sort($expectedChildren);
        sort($actual);

        $this->assertEquals($expectedChildren, $actual);
    }

    private function assertPartners(API_Union $union, API_Profile $base, ?API_Profile $partner, string $message = ''): void
    {
        if ($partner != null)
        {
            $inputPartners = [ $base->getID(), $partner->getID() ];
        }
        else
        {
            $inputPartners = [ $base->getID() ];
        }

        $getPartners = $union->getPartners();
        $verifyArray = array_diff($getPartners, $inputPartners);

        $this->assertCount(0, $verifyArray, $message);
    }

    private function assertEqualsShell(API_Profile $shell, string $shellName, API_Profile $profile): void
    {
        $fullName    = $profile->getFullName();
        $profileName = $fullName->getFirstName() . ' ' . $fullName->getLastName();

        $this->assertEquals($shellName, $profileName, 'Name different.');

        $alive  = $shell->isAlive();
        $public = $shell->isPublic();

        $this->assertEquals($alive, $profile->isAlive(), 'isAlive different');

        if ($alive && $public)
        {
            $this->assertEquals(false, $profile->isPublic(), 'isPublic different');
        }
        else
        {
            $this->assertEquals($public, $profile->isPublic(), 'isPublic different');
        }

        $this->assertEquals($shell->getGender(), $profile->getGender(), 'Gender is different');
    }

    private function addAssertChildren(API_Union $union, int $count, string $modifier = ''): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        if ($count == 0)
        {
            return;
        }

        $children = [];
        for ($i = 0; $i < $count; $i++)
        {
            list($childShell, $shellName) = $profileFactory->makeRandomShell();

            $childID    = $union->addChild($childShell, $modifier);
            $children[] = $childID;

            $profileFactory->logCreate($childID);

            // Verify that shell is identical to the partner added.

            $child = API_Profile::get($childID);

            $this->assertEqualsShell($childShell, $shellName, $child);
        }

        $checkArray = [];
        $firstChild = [];

        switch ($modifier)
        {
            case '':
                $checkArray = $union->getChildren();
                $firstChild = $unionFactory->getChildren();
                break;

            case 'adopt':
                $checkArray = $union->getAdoptedChildren();
                $firstChild = $unionFactory->getAdopted();
                break;

            case 'foster':
                $checkArray = $union->getFosterChildren();
                $firstChild = $unionFactory->getFoster();
                break;

            default:
                break;
        }

        $diffsArray = array_diff($checkArray, $children, $firstChild);

        $this->assertEmpty($diffsArray);
    }

    // =====================================================
    // Test functions.

    // ------------------------------------------------------
    // API calls.

    /**
     * Tests API_Union::get()
     *
     * @dataProvider provideRunCount
     */
    public function testGet_Empty(int $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        // Set-up.
        // API calls this function makes (in order) BEFORE the test GET call.
        //   # /profile/add
        //   # /profile/add-partner AND/OR /profile/add-child
        //   # /profile/get (for the 'unions' field).

        list(, $unionID, $parentProfile, $partner) = $unionFactory->makeUnion(addPartner: true, empty: true);

        // ----------------------------------------
        $getUnion = API_Union::get($unionID);
        // ----------------------------------------

        $this->assertNotNull($getUnion, 'Failed to get union');

        $this->assertEquals($unionID, $getUnion->getID(), "IDs don't match");
        $this->assertPartners($getUnion, $parentProfile, $partner, "Union partners don't match");

        $this->assertCount(0, $getUnion->getChildren(), 'Number of children not 0');
        $this->assertCount(0, $getUnion->getAdoptedChildren(), 'Number of adopted children not 0');
        $this->assertCount(0, $getUnion->getFosterChildren(), 'Number of foster children not 0');
    }

    /**
     * Tests API_Union::get()
     *
     * @dataProvider provideUnionParameters
     */
    public function testGet_WithChildren(bool $addPartner, int $biological, int $adopted, int $foster): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        // Set-up.
        // API-calls this function makes (in order) BEFORE the test GET call.
        //   # /profile/add
        //   # /profile/add-partner AND/OR /profile/add-child
        //   # /profile/get (for the 'unions' field).
        //   # /union
        //   # /union/add-child (if not all children added).

        list($union, $unionID, $base, $partner) = $unionFactory->makeUnion($addPartner, false, $biological, $adopted, $foster);

        // ----------------------------------------
        $getUnion = API_Union::get($unionID);
        // ----------------------------------------

        $this->assertNotNull($getUnion, 'Failed to get union');
        $this->assertEqualsUnion($union, $getUnion, $base, $partner);
    }

    /**
     * Tests API_Union->addChild()
     *
     * @dataProvider provideRunCount
     */
    public function testAddChild_Empty(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        // Set-up.
        list(, $unionID, , ) = $unionFactory->makeUnion(addPartner: true, empty: true);

        $union                        = API_Union::get($unionID);
        list($childShell, $shellName) = $profileFactory->makeRandomShell();

        // ----------------------------------------
        $addChildID = $union->addChild($childShell);
        // ----------------------------------------

        $this->assertNotNull($addChildID, 'returned union NULL');

        $getChild = API_Profile::get($addChildID);

        // Verify that shell is identical to the child added.

        $this->assertEqualsShell($childShell, $shellName, $getChild);
    }

    //     /**
    //      * Tests API_Union->addPartner()
    //      *
    //      * @dataProvider provideRunCount
    //      */
    //     public function testAddPartner_Empty(int $ignore): void
    //     {
    //         /** @var FactoryProfiles $profileFactory */
    //         $profileFactory = FactoryBase::getFactory(API_Profile::class);
    //         /** @var FactoryUnions $unionFactory */
    //         $unionFactory = FactoryBase::getFactory(API_Union::class);
    //
    //         // Set-up.
    //         list ( , $unionID, ,) = $unionFactory->makeUnion(addPartner: FALSE, empty: TRUE);

    //         $union = API_Union::get($unionID);
    //         list ($partnerShell, $shellName) = $profileFactory->makeRandomShell();

    //         // ----------------------------------------
    //         $addPartnerID = $union->addPartner($partnerShell);
    //         // ----------------------------------------

    //         $this->assertNotNull($addPartnerID, 'returned union ID is NULL');

    //         $getPartner = API_Profile::get($addPartnerID);

    //         // Verify that shell is identical to the partner added.

    //         $this->assertEqualsShell($partnerShell, $shellName, $getPartner);
    //     }

    /**
     * Tests API_Union->addChild() AND API_Union->addPartner()
     * Create a union with a combination of a partner and children.
     *
     * @dataProvider provideUnionParameters
     */
    public function testAdd_Full(bool $addPartner, int $biological, int $adopted, int $foster): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $base = $profileFactory->makeRandomProfile();

        // The $base profile will include a union only if a partner or child has
        // ALREADY been added. So in order to test adding a partner, we must create
        // a union by adding a child to $base (and reduce number of children to add).
        //
        // It is not possible to TEST adding a partner, if the test also does not
        // include children.

        // Add a child to the union, and lower the count of children.
        $child = $unionFactory->addFirstChild($base, $biological, $adopted, $foster);

        $profileFactory->logCreate($child->getID());

        $unionArray = $base->getUnions(force: true);
        $unionID    = reset($unionArray);
        $union      = API_Union::get($unionID);

        if ($addPartner)
        {
            list($partnerShell, $shellName) = $profileFactory->makeRandomShell();

            $addedPartnerID = $union->addPartner($partnerShell);

            $profileFactory->logCreate($addedPartnerID);

            $this->assertNotNull($addedPartnerID, 'returned union ID is NULL');

            $getPartner = API_Profile::get($addedPartnerID);

            // Verify that shell is identical to the partner added.

            $this->assertEqualsShell($partnerShell, $shellName, $getPartner);

            // Verify that both partners are listed.

            $bothPartners = [
                $base->getID(),
                $addedPartnerID
            ];
            $unionPartners = $union->getPartners();

            $diff = array_diff($bothPartners, $unionPartners);

            $this->assertEmpty($diff);
        }

        // Add children and assert that they were added properly.

        $this->addAssertChildren($union, $biological);
        $this->addAssertChildren($union, $adopted, 'adopt');
        $this->addAssertChildren($union, $foster, 'foster');
    }

    /**
     * Tests API_Union->update()
     *
     * @dataProvider provideMarriageDivorceEvents
     */
    public function testUpdate(bool $marriage, bool $divorce, bool $expected, string $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        // Set-up.
        list($union, /*$unionID*/, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        $baseName    = $base->getName();
        $partnerName = $partner->getName();

        $marriageEvent = null;
        $divorceEvent  = null;

        if ($marriage)
        {
            $marriageEvent = $eventFactory->makeMarriageEvent($baseName, $partnerName);
        }

        if ($divorce)
        {
            $divorceEvent = $eventFactory->makeDivorceEvent($baseName, $partnerName);
        }

        // ----------------------------------------
        $updated = $union->update($marriageEvent, $divorceEvent);
        // ----------------------------------------

        if ($expected)
        {
            $this->assertTrue($updated, 'Failed to update.');

            if ($marriage)
            {
                $addedMarriage = $union->getMarriage();
                $eventSame     = $eventFactory->equalMarriageDivorceEvents($marriageEvent, $addedMarriage, $baseName, $partnerName, 'Marriage');

                $this->assertTrue($eventSame, 'Marriage events are different');
            }

            if ($divorce)
            {
                $addedDivorce = $union->getDivorce();
                $eventSame    = $eventFactory->equalMarriageDivorceEvents($divorceEvent, $addedDivorce, $baseName, $partnerName, 'Divorce');

                $this->assertTrue($eventSame, 'Divorce events are different');
            }
        }
        else
        {
            $this->assertFalse($updated, 'Update returned TRUE on NO update.');
        }
    }

    // ------------------------------------------------------

    /**
     * Tests API_Union->getOtherPartner()
     *
     * @dataProvider provideRunCount
     */
    public function testGetOtherPartner_Second(int $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        // Set-up.
        list($union, /*$unionID*/, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        // ----------------------------------------
        $partnerID = $union->getOtherPartner($base->getID());
        // ----------------------------------------

        $this->assertEquals($partner->getID(), $partnerID);
    }

    /**
     * Tests API_Union->getOtherPartner()
     *
     * @dataProvider provideRunCount
     */
    public function testGetOtherPartner_First(int $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        // Set-up.
        list($union, /*$unionID*/, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        // ----------------------------------------
        $baseID = $union->getOtherPartner($partner->getID());
        // ----------------------------------------

        $this->assertEquals($base->getID(), $baseID);
    }

    // ------------------------------------------------------
    // Getter functions.

    /**
     * Tests API_Union->getID()
     *
     * @dataProvider provideRunCount
     */
    public function testGetID(int $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        // Set-up.
        list($union, $unionID, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        $baseUnions  = $base->getUnions();
        $baseUnionID = $baseUnions[0];

        $partnerUnions  = $partner->getUnions();
        $partnerUnionID = $partnerUnions[0];

        // ----------------------------------------
        $unionID = $union->getID();
        // ----------------------------------------

        $this->assertEquals($baseUnionID, $unionID);
        $this->assertEquals($partnerUnionID, $unionID);
    }

    /**
     * Tests API_Union->getChildren()
     *
     * @dataProvider provideRunCount
     */
    public function testGetChildren(int $count): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        list($union, , , ) = $unionFactory->makeUnion(addPartner: true);

        $childrenIDs = $unionFactory->makeChildren($union, $count);

        // ----------------------------------------
        $unionChildren = $union->getChildren();
        // ----------------------------------------

        $this->assertEquals($childrenIDs, $unionChildren);
    }

    /**
     * Tests API_Union->getAdoptedChildren()
     *
     * @dataProvider provideSetSize
     */
    public function testGetAdoptedChildren(int $count): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        list($union, , , ) = $unionFactory->makeUnion(addPartner: true);

        $childrenIDs = $unionFactory->makeChildren($union, $count, modifier: 'adopt');

        // ----------------------------------------
        $unionChildren = $union->getAdoptedChildren();
        // ----------------------------------------

        $this->assertEquals($childrenIDs, $unionChildren);
    }

    /**
     * Tests API_Union->getFosterChildren()
     *
     * @dataProvider provideSetSize
     */
    public function testGetFosterChildren(int $count): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        list($union, , , ) = $unionFactory->makeUnion(addPartner: true);

        $childrenIDs = $unionFactory->makeChildren($union, $count, modifier: 'foster');

        // ----------------------------------------
        $unionChildren = $union->getFosterChildren();
        // ----------------------------------------

        $this->assertEquals($childrenIDs, $unionChildren);
    }

    /**
     * Tests API_Union->getPartners()
     *
     * @dataProvider provideRunCount
     */
    public function testGetPartners(int $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);

        // Set-up.
        list($union, /*$unionID*/, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        $partnerIDs = [
            $base->getID(),
            $partner->getID()
        ];

        // ----------------------------------------
        $getIDs = $union->getPartners();
        // ----------------------------------------

        $diffs = array_diff($partnerIDs, $getIDs);
        $this->assertEmpty($diffs);
    }

    /**
     * Tests API_Union->getMarriage()
     *
     * @dataProvider provideRunCount
     */
    public function testGetMarriage(int $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        // Set-up.
        list($union, /*$unionID*/, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        $baseName      = $base->getName();
        $partnerName   = $partner->getName();
        $marriageEvent = $eventFactory->makeMarriageEvent($baseName, $partnerName);

        $union->update($marriageEvent);

        // ----------------------------------------
        $getMarriage = $union->getMarriage();
        // ----------------------------------------

        $eventSame = $eventFactory->equalMarriageDivorceEvents($marriageEvent, $getMarriage, $baseName, $partnerName, 'Marriage');

        $this->assertTrue($eventSame, 'Marriage events are different');
    }

    /**
     * Tests API_Union->getDivorce()
     *
     * @dataProvider provideRunCount
     */
    public function testGetDivorce(int $ignore): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        // Set-up.
        list($union, /*$unionID*/, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        $baseName     = $base->getName();
        $partnerName  = $partner->getName();
        $divorceEvent = $eventFactory->makeDivorceEvent($baseName, $partnerName);

        $union->update(divorce: $divorceEvent);

        // ----------------------------------------
        $getDivorce = $union->getDivorce();
        // ----------------------------------------

        $eventSame = $eventFactory->equalMarriageDivorceEvents($divorceEvent, $getDivorce, $baseName, $partnerName, 'Divorce');

        $this->assertTrue($eventSame, 'Divorce events are different');
    }

    /**
     * Tests API_Union->getStatus()
     *
     * @dataProvider provideMarriageDivorceEvents
     */
    public function testGetStatus(bool $marriage, bool $divorce, bool $ignore, string $expectedStatus): void
    {
        /** @var FactoryUnions $unionFactory */
        $unionFactory = FactoryBase::getFactory(API_Union::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        // Set-up.
        list($union, /*$unionID*/, $base, $partner) = $unionFactory->makeUnion(addPartner: true);

        $baseName    = $base->getName();
        $partnerName = $partner->getName();

        $marriageEvent = null;
        $divorceEvent  = null;

        if ($marriage)
        {
            $marriageEvent = $eventFactory->makeMarriageEvent($baseName, $partnerName);
        }

        if ($divorce)
        {
            $divorceEvent = $eventFactory->makeDivorceEvent($baseName, $partnerName);
        }

        $union->update($marriageEvent, $divorceEvent);

        // ----------------------------------------
        $status = $union->getStatus();
        // ----------------------------------------

        $this->assertEquals($expectedStatus, $status);
    }
}
