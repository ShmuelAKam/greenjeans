<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_PhotoAlbum;

/**
 * API_PhotoAlbum test case.
 * BEWARE: There is NO API to delete an album after creating it.
 * This test class creates a total of TWENTY SEVEN albums.
 * These must all be deleted MANUALLY. What a mess!
 *
 * [ I really wish Geni would get the sandbox working again. ]
 */
class API_PhotoAlbumTest extends API_BaseTest
{
    private ?API_PhotoAlbum $aPI_PhotoAlbum;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aPI_PhotoAlbum = null;
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->aPI_PhotoAlbum = null;

        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * Provide parameters for tests that DO NOT call the API.
     *
     * @return array <array <string|int>>
     */
    public static function provideAlbumParameters_FAKE(): array
    {
        // cover_photo are NOT included because it is too complicated to test.

        // ALL of these values are meaningless.
        // @formatter:off
        $albumParameters = [
            // Description,            Name,            ID,      Count,   URL
            [ 'Hot Crossed Buns',     'Bugs Bunnie',   'Hottie', 5,      'http://bunny.com'   ],
            [ 'Tall Tell Tails',      'Giraffes R Us', 'Nelly',  77,     'http://highest.org' ],
            [ 'Gnus Found Here',      'Non-Sense',     'Snorf',  666,    'http://cnn.com'     ],
            [ 'Hebrews on the Run',   'Exodus',        'Moses',  600000, 'http://exodus.com'  ],
            [ 'Nannu Nannu Shuzbutt', 'Mork & Mindy',  'Robin',  307,    'http://mork.com'    ],
        ];
        // @formatter:on

        return $albumParameters;
    }

    /**
     * Provide parameters for tests that call the API.
     * The API only enables updating these two fields: Descriptionand name.
     * o add uses only 'name'
     * o update uses both.
     * The other values are created by the server.
     *
     * @return array <array <string>>
     */
    public static function provideAlbumParameters_ForAPI(): array
    {
        // The API only enables setting these two values.
        // add uses only 'name', and update uses both.
        // @formatter:off
        $albumParameters = [
            // Description,                          Name
            [ 'With a Little Help from My Friends', "Sgt. Pepper's Lonely Hearts Club Band" ],
            [ 'The Show Must Go On',                'Innuendo'                              ],
            [ 'Another Brick in the Wall',          'The Wall'                              ],
            [ 'Forever Young',                      'Alphaville'                            ],
            [ 'Modeh Ani',                          'Songs of Regesh'                       ],
            [ '',                                   'Songs of Distant Earth'                ],
        ];
        // @formatter:on

        return $albumParameters;
    }

    /**
    // =====================================================
    // Helper functions.

    /**
     * @param string $description
     * @param string $name
     * @param string $ID
     * @param int $count
     * @param string $URL
     * @return array <string, string|int>
     */
    private function makeUpdateArray(string $description, string $name, string $ID, int $count, string $URL)
    {
        $updateArray = [
            'description' => $description,
            'id' => $ID,
            'name' => $name,
            'photos_count' => $count,
            'url' => $URL
        ];

        return $updateArray;
    }

    /**
     * Make array of API_Photo objects.
     * They are added to the photo-album when created.
     *
     * @param  int $count
     * @param  string $albumID
     * @return array <string, \ShmuelAKam\Greenjeans\Api\API_Photo> Array of API_Photo objects that were added to the album.
     */
    private function makePhotos(int $count, string $albumID): array
    {
        global $photoFactory;

        $photos = [];

        for ($i = 0; $i < $count; $i++)
        {
            $newPhoto = $photoFactory->makeRandomPhoto($albumID);

            $photos [$newPhoto->getID()] = $newPhoto;
        }

        return $photos;
    }

    /**
     * Create an album for the tests that check values WITHOUT calling the API.
     *
     * @param string $description
     * @param string $ID
     * @param string $name
     * @param int $count
     * @param string $URL
     * @return \ShmuelAKam\Greenjeans\Api\API_PhotoAlbum
     */
    private function makeFakeAlbum(string $description, string $name, string $ID, int $count, string $URL)
    {
        $updateArray = $this->makeUpdateArray($description, $name, $ID, $count, $URL);

        $fake = new API_PhotoAlbum();
        $fake->updateFromArray($updateArray);

        return $fake;
    }

    // =====================================================
    // Custom assert functions.

    /**
     * Assert that the FAKE album is identical to the values that created it.
     *
     * @param API_PhotoAlbum $album
     * @param string $description
     * @param string $name
     * @param string $ID
     * @param int $count
     * @param string $URL
     */
    private function assertEqualsFields(API_PhotoAlbum $album, ?string $description, string $name, string $ID, int $count, string $URL): void
    {
        $this->assertEquals($description, $album->getDescription());
        $this->assertEquals($ID, $album->getID());
        $this->assertEquals($name, $album->getName());
        $this->assertEquals($count, $album->getPhotosCount());
        $this->assertEquals($URL, $album->getURL());
    }

    /**
     * Assert real album.
     *
     * @param API_PhotoAlbum $album
     * @param string $description
     * @param string $name
     */
    private function assertAlbum(API_PhotoAlbum $album, string $description, string $name): void
    {
        $this->assertEquals($description, $album->getDescription(), 'Descriptions not the same');
        $this->assertEquals($name, $album->getName(), 'Album name not the same');
        $this->assertGreaterThanOrEqual(0, $album->getPhotosCount(), 'ZERO photos in album');
        $this->assertNotEquals('', $album->getURL(), 'URL not blank');

        $albumID    = $album->getID();
        $lengthHead = strlen('album-');
        $albumHead  = substr($albumID, 0, $lengthHead);
        $albumNum   = substr($albumID, $lengthHead); // 'number'

        $this->assertEquals('album-', $albumHead, 'Album ID is broken');
        $this->assertGreaterThanOrEqual(0, (int) $albumNum, 'Album ID is broken');
    }

    // =====================================================
    // Test functions.

    // The following three tests do NOT call the API (it's not required,
    // and gives more comprehensive values). So the test parameters are
    // meaningless.

    /**
     * Tests API_PhotoAlbum->__construct()
     */
    public function test__construct(): void
    {
        // ----------------------------------------
        $this->aPI_PhotoAlbum = new API_PhotoAlbum();
        // ----------------------------------------

        $this->assertEqualsFields($this->aPI_PhotoAlbum, null, '', '', 0, '');
    }

    /**
     * Tests API_PhotoAlbum->updateFromArray()
     *
     * @dataProvider provideAlbumParameters_FAKE
     */
    public function testUpdateFromArray(string $description, string $name, string $ID, int $count, string $URL): void
    {
        $updateArray = $this->makeUpdateArray($description, $name, $ID, $count, $URL);

        $this->aPI_PhotoAlbum = new API_PhotoAlbum();

        // ----------------------------------------
        $this->aPI_PhotoAlbum->updateFromArray($updateArray);
        // ----------------------------------------

        $this->assertEqualsFields($this->aPI_PhotoAlbum, $description, $name, $ID, $count, $URL);
    }

    /**
     * Tests API_PhotoAlbum::newFromArray()
     *
     * @dataProvider provideAlbumParameters_FAKE
     */
    public function testNewFromArray(string $description, string $name, string $ID, int $count, string $URL): void
    {
        $updateArray = $this->makeUpdateArray($description, $name, $ID, $count, $URL);

        // ----------------------------------------
        $this->aPI_PhotoAlbum = API_PhotoAlbum::newFromArray($updateArray);
        // ----------------------------------------

        $this->assertEqualsFields($this->aPI_PhotoAlbum, $description, $name, $ID, $count, $URL);
    }

    // ------------------------------------------------------
    // API calls.

    /**
     * Tests API_PhotoAlbum::get()
     *
     * @dataProvider provideRunCount
     */
    public function testGet(int $ignore): void
    {
        $name = 'Test ' . date('Y-m-d');

        $this->aPI_PhotoAlbum = API_PhotoAlbum::add($name);
        $albumID              = $this->aPI_PhotoAlbum->getID();

        // ----------------------------------------
        $album = API_PhotoAlbum::get($albumID);
        // ----------------------------------------

        $this->assertEquals($this->aPI_PhotoAlbum, $album);
    }

    /**
     * Tests API_PhotoAlbum::add()
     *
     * @dataProvider provideAlbumParameters_ForAPI
     */
    public function testAdd(string $description, string $name): void
    {
        // ----------------------------------------
        $this->aPI_PhotoAlbum = API_PhotoAlbum::add($name, $description);
        // ----------------------------------------

        $this->assertAlbum($this->aPI_PhotoAlbum, $description, $name);
    }

    /**
     * Tests API_PhotoAlbum->getPhotos()
     *
     * @dataProvider provideRunCount
     */
    public function testGetPhotos_NONE(int $ignore): void
    {
        $name = (string) time();

        // Create an empty album. Do NOT add any photos.
        $this->aPI_PhotoAlbum = API_PhotoAlbum::add($name);
        $this->aPI_PhotoAlbum->getID();

        // ----------------------------------------
        $photoList = $this->aPI_PhotoAlbum->getPhotos();
        // ----------------------------------------

        $this->assertCount(0, $photoList);
        $this->assertTrue($photoList->isEmpty());
        $this->assertEmpty($photoList->getList());
    }

    /**
     * Tests API_PhotoAlbum->getPhotos()
     *
     * @dataProvider provideSetSize
     * @ dataProvider provideLargeSetSize
     */
    public function testGetPhotos(int $count): void
    {
        $name = (string) time();

        $this->aPI_PhotoAlbum = API_PhotoAlbum::add($name);
        $albumID              = $this->aPI_PhotoAlbum->getID();

        // Create photos (this also adds them to the album).
        // This array uses photo IDs as keys.
        $photos = $this->makePhotos($count, $albumID);

        // Get album from Server.
        $album = API_PhotoAlbum::get($albumID);

        // ----------------------------------------
        $photoList = $album->getPhotos();
        // ----------------------------------------

        $photoKeys = array_keys($photos);
        $getKeys   = $photoList->getListIDs();

        $diffs = array_diff_key($photoKeys, $getKeys);

        $this->assertEmpty($diffs);
    }

    /**
     * Tests API_PhotoAlbum->update()
     *
     * @dataProvider provideAlbumParameters_ForAPI
     */
    public function testUpdate(string $description, string $name): void
    {
        $time = time();

        // Generate initial album name using time().
        $this->aPI_PhotoAlbum = API_PhotoAlbum::add("$time");
        $albumID              = $this->aPI_PhotoAlbum->getID();

        // ----------------------------------------
        $this->aPI_PhotoAlbum->update($name, $description);
        // ----------------------------------------

        // Get album from Server.
        $album = API_PhotoAlbum::get($albumID);

        $this->assertAlbum($album, $description, $name);
    }

    // ------------------------------------------------------
    // Getter functions.

    // The following tests do NOT use any API calls. This means that the albums
    // these tests create DO NOT exist on the server. So the test parameters are
    // meaningless.

    /**
     * Tests API_PhotoAlbum->getCoverPhoto()
     */
    public function testgetCoverPhoto(): void
    {
        // Setting a cover-photo can only be done manually (No API).
        // So it is not practical to test this function. Oh well.

        /** @phpstan-ignore-next-line */
        $this->assertTrue(true);
    }

    /**
     * Tests API_PhotoAlbum->getDescription()
     *
     * @dataProvider provideAlbumParameters_FAKE
     */
    public function testGetDescription(string $description, string $ignore1, string $ignore2, int $ignore3, string $ignore4): void
    {
        $this->aPI_PhotoAlbum = $this->makeFakeAlbum($description, $ignore1, $ignore2, $ignore3, $ignore4);

        // ----------------------------------------
        $getDescription = $this->aPI_PhotoAlbum->getDescription();
        // ----------------------------------------

        $this->assertEquals($description, $getDescription);
    }

    /**
     * Tests API_PhotoAlbum->getName()
     *
     * @dataProvider provideAlbumParameters_FAKE
     */
    public function testGetName(string $ignore1, string $name, string $ignore2, int $ignore3, string $ignore4): void
    {
        $this->aPI_PhotoAlbum = $this->makeFakeAlbum($ignore1, $name, $ignore2, $ignore3, $ignore4);

        // ----------------------------------------
        $getName = $this->aPI_PhotoAlbum->getName();
        // ----------------------------------------

        $this->assertEquals($name, $getName);
    }

    /**
     * Tests API_PhotoAlbum->getID()
     *
     * @dataProvider provideAlbumParameters_FAKE
     */
    public function testGetID(string $ignore1, string $ignore2, string $ID, int $ignore3, string $ignore4): void
    {
        $this->aPI_PhotoAlbum = $this->makeFakeAlbum($ignore1, $ignore2, $ID, $ignore3, $ignore4);

        // ----------------------------------------
        $getID = $this->aPI_PhotoAlbum->getID();
        // ----------------------------------------

        $this->assertEquals($ID, $getID);
    }

    /**
     * Tests API_PhotoAlbum->getPhotosCount()
     *
     * @dataProvider provideAlbumParameters_FAKE
     */
    public function testGetPhotosCount(string $ignore1, string $ignore2, string $ignore3, int $count, string $ignore4): void
    {
        $this->aPI_PhotoAlbum = $this->makeFakeAlbum($ignore1, $ignore2, $ignore3, $count, $ignore4);

        // ----------------------------------------
        $getPhotosCount = $this->aPI_PhotoAlbum->getPhotosCount();
        // ----------------------------------------

        $this->assertEquals($count, $getPhotosCount);
    }

    /**
     * Tests API_PhotoAlbum->getURL()
     *
     * @dataProvider provideAlbumParameters_FAKE
     */
    public function testgetURL(string $ignore1, string $ignore2, string $ignore3, int $ignore4, string $URL): void
    {
        $this->aPI_PhotoAlbum = $this->makeFakeAlbum($ignore1, $ignore2, $ignore3, $ignore4, $URL);

        // ----------------------------------------
        $getURL = $this->aPI_PhotoAlbum->getURL();
        // ----------------------------------------

        $this->assertEquals($URL, $getURL);
    }
}
