<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\API_PhotoAlbum;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\API_Project;
use ShmuelAKam\Greenjeans\Api\API_Surname;
use ShmuelAKam\Greenjeans\Api\API_User;
use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Api\Core\API_ListObjects;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_ListStrings;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * API_User test case.
 * The API_User object describes the user running the application, i.e. YOU.
 * This makes testing it thoroughly somewhat of a challenge, because there
 * are NO preset values to use. There are also no setter functions.
 */
class API_UserTest extends API_BaseTest
{
    private ?API_User $aPI_User;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aPI_User = null;
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->aPI_User = null;

        parent::tearDown();
    }

    // ======================================================
    // Test providers.

    /**
     * Provide parameters for adding users.
     * This is NOT implemented, and this test can only be run on the sandbox.
     *
     * @return array <array <string>>
     */
    public static function provideUser(): array
    {
        // string $email, string $firstName, string $lastName, string $gender
        return [
            [
                'zorkmill@gamil.com',
                'Zork',
                'Mill',
                'm'
            ]
        ];
    }

    /**
     * Provide JSON encoded metadata for set and get tests.
     *
     * @return array <array <array <string, bool|int|string>>>
     */
    public static function provideMeta(): array
    {
        $bond = [
            "name"   => "Bond, James",
            "secret" => "007",
            "set"    => time()
        ];
        $sailorJim = [
            "title" => "Sailor Jim Johnson",
            "job"   => "weirdness magnet",
            "circa" => date('Y-m-d H:m:s')
        ];
        $contact = [
            "contact"   => "Jody",
            "message"   => "....Ping ... Ping ... Ping.....",
            "modulated" => true
        ];
        $epoch = [
            "Baby" => "of my life",
            "time" => time(),
            "date" => date('Y-m-d H:m:s')
        ];

        $meta = [
            [ $bond      ],
            [ $sailorJim ],
            [ $contact   ],
            [ $epoch     ],
        ];

        return $meta;
    }

    /**
     * @return array <array <bool>>
     */
    public static function provideGetAll(): array
    {
        return [
            [ false ],
            [ true  ]
        ];
    }

    // ======================================================
    // Helper functions.

    // ======================================================
    // Custom assert functions.

    public function assertListObjects(API_ListObjects  $list, bool $getAll, string $className): void
    {
        $this->assertEquals("\\$className", $list->getClassName());

        $count = $list->getTotalCount();

        if ($count > 0)
        {
            /** @var array <\ShmuelAKam\Greenjeans\Api\API_Base> $listArray */
            $listArray = $list->getList();
            $first     = reset($listArray);

            if (!is_bool($first) && !$list->isIDsOnly())
            {
                // List contains keys with full objects of class.
                $this->assertEquals($className, $first::class);
            }
        }
        else
        {
            $this->assertEmpty($list);
            $this->assertTrue($list->isEmpty());
        }

        if ($getAll)
        {
            $this->assertTrue($list->isGetAll());
        }
    }

    public function assertListProfiles(API_ListProfiles $list, bool $getAll, bool $IDsOnly): void
    {
        $count = $list->getTotalCount();

        $listArray = $list->getList();
        $first     = reset($listArray);

        if ($count > 0)
        {
            if ($IDsOnly)
            {
                $this->assertTrue($first);
            }
            else
            {
                $this->assertTrue(is_a($first, API_Profile::class));
            }
        }
        else
        {
            $this->assertEmpty($list);
            $this->assertTrue($list->isEmpty());
        }

        $this->assertTrue($getAll == $list->isGetAll(), 'getAll mismatch');
        $this->assertTrue($IDsOnly == $list->isIDsOnly(), 'IDs-only mismatch');
    }

    public function assertListStrings(API_ListStrings  $list, bool $getAll): void
    {
        $count = $list->getTotalCount();

        if ($count > 0)
        {
            $listArray = $list->getList();
            $first     = reset($listArray);

            $this->assertTrue(is_string($first));
        }
        else
        {
            $this->assertEmpty($list);
            $this->assertTrue($list->isEmpty());
        }

        if ($getAll)
        {
            $this->assertTrue($list->isGetAll());
        }
    }

    // ======================================================
    // Test functions.

    /**
     * Tests API_User::get()
     */
    public function testGet(): void
    {
        // --------------------------------------------------
        $this->aPI_User = API_User::get();
        // --------------------------------------------------

        $this->assertNotEquals('', $this->aPI_User->getAccountType());
        $this->assertNotEquals('', $this->aPI_User->getName());
    }

    /**
     * Tests API_User::add()
     *
     * @dataProvider provideUser
     */
    public function testAdd(string $email, string $firstName, string $lastName, string $gender): void
    {
        $API_Wrap = API_Wrapper::getInstance();

        if ($API_Wrap->isOnSandbox())
        {
            // TODO Auto-generated API_UserTest->testAdd()
            $this->markTestIncomplete("add test PROVIDER not implemented");

            // --------------------------------------------------
            /** @phpstan-ignore-next-line */
            $added = API_User::add($email, $firstName, $lastName, $gender);
            // --------------------------------------------------

            $this->assertTrue($added, 'Failed to add');
        }
        else
        {
            // $this->markTestSkipped ("add can only be tested in the sandbox");
            /** @phpstan-ignore-next-line */
            $this->assertTrue(true);
        }
    }

    /**
     * Tests API_User->updateMetadata()
     *
     * @dataProvider provideMeta
     * @param array <string, bool|int|string> $meta
     */
    public function testUpdateMetadata(array $meta): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $updated = $this->aPI_User->updateMetadata($meta);
        // --------------------------------------------------

        $metaReply = $this->aPI_User->getMetadata();

        $this->assertTrue($updated, 'Not updated');
        $this->assertEquals($meta, $metaReply, 'Metadata not equal');
    }

    /**
     * Tests API_User->getMetadata()
     *
     * @dataProvider provideMeta
     * @param array <string, bool|int|string> $meta
     */
    public function testGetMetadata(array $meta): void
    {
        $this->aPI_User = API_User::get();

        $updated = $this->aPI_User->updateMetadata($meta);
        $this->assertTrue($updated, 'Not updated');

        // --------------------------------------------------
        $metaReply = $this->aPI_User->getMetadata();
        // --------------------------------------------------

        $this->assertEquals($meta, $metaReply, 'Metadata not equal');
    }

    /**
     * Tests API_User->getAccountType()
     */
    public function testGetAccountType(): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $this->aPI_User->getAccountType();
        // --------------------------------------------------

        $this->assertNotEquals('', $this->aPI_User->getAccountType());
    }

    /**
     * Tests API_User->getName()
     */
    public function testGetName(): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $this->aPI_User->getName();
        // --------------------------------------------------

        $this->assertNotEquals('', $this->aPI_User->getName());
    }

    // ======================================================
    // Tests to get the various lists.

    /**
     * Tests API_User->getFollowedDocuments()
     *
     * @dataProvider provideGetAll
     */
    public function testGetFollowedDocuments(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getFollowedDocuments($getAll);
        // --------------------------------------------------

        $this->assertListObjects($list, $getAll, API_Document::class);
    }

    /**
     * Tests API_User->getFollowedProfiles()
     *
     * @dataProvider provideTwoFlags
     */
    public function testGetFollowedProfiles(bool $getAll, bool $IDsOnly): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getFollowedProfiles($getAll, $IDsOnly);
        // --------------------------------------------------

        $this->assertListProfiles($list, $getAll, $IDsOnly);
    }

    /**
     * Tests API_User->getFollowedProjects()
     *
     * @dataProvider provideGetAll
     */
    public function testGetFollowedProjects(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getFollowedProjects($getAll);
        // --------------------------------------------------

        $this->assertListObjects($list, $getAll, API_Project::class);
    }

    /**
     * Tests API_User->getFollowedSurnames()
     *
     * @dataProvider provideGetAll
     */
    public function testGetFollowedSurnames(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getFollowedSurnames($getAll);
        // --------------------------------------------------

        $this->assertListObjects($list, $getAll, API_Surname::class);
    }

    /**
     * Tests API_User->getManagedProfiles()
     *
     * @dataProvider provideTwoFlags
     */
    public function testGetManagedProfiles(bool $getAll, bool $IDsOnly): void
    {
        // There is [currently] a Server bug that returns a faulty reply
        // for this function when NOT ID-only. So no point in testing as
        // the code can't handle it.

        if (!$IDsOnly)
        {
            /** @phpstan-ignore-next-line */
            $this->assertTrue(true);
            return;
        }

        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getManagedProfiles($getAll, $IDsOnly);
        // --------------------------------------------------

        $this->assertListProfiles($list, $getAll, $IDsOnly);
    }

    /**
     * Tests API_User->getMaxFamily()
     *
     * @dataProvider provideTwoFlags
     */
    public function testGetMaxFamily(bool $getAll, bool $IDsOnly): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getMaxFamily($getAll, $IDsOnly);
        // --------------------------------------------------

        $this->assertListProfiles($list, $getAll, $IDsOnly);
    }

    /**
     * Tests API_User->getMyAlbums()
     *
     * @dataProvider provideGetAll
     */
    public function testGetMyAlbums(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getMyAlbums($getAll);
        // --------------------------------------------------

        $this->assertListObjects($list, $getAll, API_PhotoAlbum::class);
    }

    /**
     * Tests API_User->getMyLabels()
     *
     * @dataProvider provideGetAll
     */
    public function testGetMyLabels(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getMyLabels($getAll);
        // --------------------------------------------------

        $this->assertListStrings($list, $getAll);
    }

    /**
     * Tests API_User->getUploadedDocuments()
     *
     * @dataProvider provideGetAll
     */
    public function testGetUploadedDocuments(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getUploadedDocuments($getAll);
        // --------------------------------------------------

        $this->assertListObjects($list, $getAll, API_Document::class);
    }

    /**
     * Tests API_User->getUploadedPhotos()
     *
     * @dataProvider provideGetAll
     */
    public function testGetUploadedPhotos(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getUploadedPhotos($getAll);
        // --------------------------------------------------

        $this->assertListObjects($list, $getAll, API_Photo::class);
    }

    /**
     * Tests API_User->getUploadedVideos()
     *
     * @dataProvider provideGetAll
     */
    public function testGetUploadedVideos(bool $getAll): void
    {
        $this->aPI_User = API_User::get();

        // --------------------------------------------------
        $list = $this->aPI_User->getUploadedVideos($getAll);
        // --------------------------------------------------

        $this->assertListObjects($list, $getAll, API_Video::class);
    }
}
