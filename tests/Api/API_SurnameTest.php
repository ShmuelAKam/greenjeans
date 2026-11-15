<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Surname;

const TEST_DESCRIPTION_LENGTH = 150;

//                                          ($ID,        $sluggedName,   $URL,                       $followerCount, $profileCount, $description)

const testSurname0         = new testSurname('surname-799', 'andersen', 'https://www.geni.com/api/surname-799', 4, 209492, "'''Andersen'''\r\n\r\nScandinavian\r\n\r\nThis last name was originally a typical '''Scandinavian [https://en.wikipedia.org/wiki/Patronymic Patronymic]''':  For Scandinavian families. It was only introduced as a last name or family name around 1850-1900.\r\n\r\n* Søn af Anders = Andersen (Danish)\r\n* Son of Anders = Andersen\r\n\r\n[http://en.wikipedia.org/wiki/Hans_Christian_Andersen Hans Christian Andersen] was a famous Danish writer, he wrote a lot of fairy-tales, e.g. \"''The Little Mermaid''\". ");
const testCutDescription   = new testSurname('surname-1003', 'dash', 'https://www.geni.com/api/surname-1003', 1, 1340, "Dash is an Oriya brahmin surname who belong to the Kanyakubja origin,but later grouped under Utkala Brahmins. They are settled throughout Orissa and follow mainly the vaishnavism tradition of hinduism. A minority of oriya Brahmins brahmins sometimes use the shorter form 'Das'. The shorter surname 'Das', which is more commonly a non-brahmin surname, is quite common in Orissa and West Bengal. So all family surnames ending as Dash indicate brahmin ancestry in Orissa, while some Das could be of brahmin ancestry.\n\nIn 9th and 10th century CE.,the king of Orissa invited Brahmins of Kannauj (called Kanyakubja branch of Brahmins 1 [1], Brahmin Communities) to come and settle in present Puri, Cuttack & Ganjam district and take care of spiritual activities, Yajna,Teaching and as advisers to the King. The king offered them agricultural land, temple management, ministership. In Puri district 48 such Brahmin villages called 'Sasanas', 'Karabads' and 'Bisa' were established. These brahmins later migrated to various other parts of Orissa. Many Kanyakubja brahmins also migrated to the area corresponding to present day lower Bengal in 9th and 10th century CE\n\n----------\nDash Name Meaning \n\nEnglish: topographic name for someone who lived near an ash tree, or a habitational name from a place named with the Old English word æsc (see Ash). The Anglo-Norman French preposition de ‘of’, ‘from’ has become fused to the name. \n\nAmericanized spelling of German Dasch. \n\nIndian: variant of Das.");
const testLongDescription  = new testSurname('surname-2503', 'potter', 'https://www.geni.com/api/surname-2503', 7, 37324, "==origin==\r\nFrom an unidentified source:\r\n\r\n>English, Dutch, and North German (Pötter): " . "occupational name for a maker of drinking and storage vessels, from an agent derivative of Middle " . "English, Middle Low German pot. In the Middle Ages the term covered workers in metal as well as " . "earthenware and clay. \r\n\r\n==request from https://www.geni.com/people/Linda-Meadows/6000000044991345154==\r\n" . "Henry Potter  from Hutton Cranswick  nr Driffield  East Yorkshire . is anyone  researching in this area  please.\r\n\r\n" . "==links==\r\n* https://www.findagrave.com/memorial/search?&lastname=Potter&includeMaidenName=true&exactName=true\r\n" . "* https://www.wikitree.com/genealogy/POTTER\r\n");
const testNoDescription    = new testSurname('surname-4237', 'kentner', 'https://www.geni.com/api/surname-4237', 0, 1341, '');
const testShortDescription = new testSurname('surname-5555', 'wait', 'https://www.geni.com/api/surname-5555', 2, 3642, 'Desendents of Thomas of Rhode Island 1636');
const testNoName           = new testSurname('surname-100', '', '', 0, 0, '');

// Name doesn't exist.
const testSurnameReset = new testSurname('', '', '', 0, 0, '');

/**
 * API_Surname test case.
 */
class API_SurnameTest extends API_BaseTest
{
    private ?API_Surname $aPI_Surname;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aPI_Surname = new API_Surname();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->aPI_Surname = null;

        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * @return array <string, array <testSurname>>
     */
    public static function provideSurname(): array
    {
        return [
            'Surname0'          => [ testSurname0         ],
            'cut description'   => [ testCutDescription   ],
            'long description'  => [ testLongDescription  ],
            'NO description'    => [ testNoDescription    ],
            'short description' => [ testShortDescription ],
            'Name not exist'    => [ testNoName           ],
        ];
    }

    // =====================================================
    // Helper functions.

    private function assertSurname(testSurname $test, API_Surname $actual): void
    {
        $this->assertEquals($test->ID, $actual->getID(), 'ID not same');
        $this->assertEquals($test->sluggedName, $actual->getSluggedName(), 'Slug not same');
        $this->assertEquals($test->URL, $actual->getURL(), 'URL not same');

        $this->assertEquals($test->description, $actual->getDescription(), 'Description not the same.');
    }

    /** @phpstan-ignore-next-line */
    private function assertListsEmpty(API_Surname $real): void
    {
        $this->assertEquals(- 1, $real->getFollowerCount(), 'Follower count');
        $this->assertEquals(- 1, $real->getProfileCount(), 'Profile  count');

        $this->assertEmpty($real->getFollowers(), 'Follower list');
        $this->assertEmpty($real->getProfiles(), 'Profile  list');
    }

    // =====================================================
    // Test functions.

    /**
     * Tests API_Surname->__construct()
     */
    public function test__construct(): void
    {
        // ----------------------------------------
        $this->aPI_Surname = new API_Surname();
        // ----------------------------------------

        $this->assertSurname(testSurnameReset, $this->aPI_Surname);
    }

    // =====================================================
    // Test GET function.

    /**
     * Tests API_Surname->get()
     */
    public function testGet_NotExist(): void
    {
        // ----------------------------------------
        $this->aPI_Surname = API_Surname::get(testNoName->ID);
        // ----------------------------------------

        $this->assertNull($this->aPI_Surname);
    }

    /**
     * Tests API_Surname->get()
     *
     * @dataProvider provideSurname
     */
    public function testGet(testSurname $tester): void
    {
        // ----------------------------------------
        $this->aPI_Surname = API_Surname::get($tester->ID);
        // ----------------------------------------

        if ($this->aPI_Surname != null)
        {
            $this->assertSurname($tester, $this->aPI_Surname);
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    // =====================================================
    // Test GET list functions.

    /**
     * Tests API_Surname->getFollowers()
     *
     * @dataProvider provideSurname
     */
    public function testGetFollowers(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $followers = $this->aPI_Surname->getFollowers();
            // ----------------------------------------

            $followerCount = $this->aPI_Surname->getFollowerCount();

            $this->assertNotEquals(- 1, $followerCount, 'Follower count');

            if ($followerCount > 0)
            {
                $this->assertNotEmpty($followers, 'Follower list not empty');
            }
            else
            {
                $this->assertEmpty($followers, 'Follower list empty');
            }
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    /**
     * Tests API_Surname->getProfiles()
     *
     * @dataProvider provideSurname
     */
    public function testGetProfiles(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $profiles = $this->aPI_Surname->getProfiles();
            // ----------------------------------------

            $profileCount = $this->aPI_Surname->getProfileCount();
            $this->assertNotEquals(- 1, (int) $profileCount, 'Profile count');

            if ($profileCount > 0)
            {
                $this->assertNotEmpty($profiles, 'Profile list not empty');
            }
            else
            {
                $this->assertEmpty($profiles, 'Profile list empty');
            }
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    // =====================================================
    // Test GETTER functions.

    /**
     * Tests API_Surname->getFollowerCount()
     *
     * @dataProvider provideSurname
     */
    public function testGetFollowerCount(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $followerCount = $this->aPI_Surname->getFollowerCount();
            // ----------------------------------------

            $this->assertNotEquals(- 1, (int) $followerCount, 'Follower count');
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    /**
     * Tests API_Surname->getProfileCount()
     *
     * @dataProvider provideSurname
     */
    public function testGetProfileCount(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $profileCount = $this->aPI_Surname->getProfileCount();
            // ----------------------------------------

            $this->assertNotEquals(- 1, (int) $profileCount, 'Profile count');
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    /**
     * Tests API_Surname->getDescription()
     *
     * @dataProvider provideSurname
     */
    public function testGetDescription(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $description = $this->aPI_Surname->getDescription();
            // ----------------------------------------

            $this->assertEquals($tester->description, $description, 'Description not the same.');
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    /**
     * Tests API_Surname->getID()
     *
     * @dataProvider provideSurname
     */
    public function testGetID(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $id = $this->aPI_Surname->getID();
            // ----------------------------------------

            $this->assertEquals($tester->ID, $id);
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    /**
     * Tests API_Surname->getSluggedName()
     *
     * @dataProvider provideSurname
     */
    public function testGetSluggedName(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $sluggedName = $this->aPI_Surname->getSluggedName();
            // ----------------------------------------

            $this->assertEquals($tester->sluggedName, $sluggedName);
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }

    /**
     * Tests API_Surname->getURL()
     *
     * @dataProvider provideSurname
     */
    public function testGetURL(testSurname $tester): void
    {
        $this->aPI_Surname = API_Surname::get($tester->ID); // Setup.

        if ($this->aPI_Surname != null)
        {
            // ----------------------------------------
            $url = $this->aPI_Surname->getURL();
            // ----------------------------------------

            $this->assertEquals($tester->URL, $url);
        }
        else
        {
            $this->assertTrue($tester == testNoName, 'NULL received.');
        }
    }
}

class testSurname
{
    public string $ID;
    public string $sluggedName;
    public string $description;
    public string $URL;

    /**
     * @var array <string> $followers
     */
    public array  $followers;

    /**
     * @var array <string> $profiles
     */
    public array  $profiles;
    public int    $followerCount;
    public int    $profileCount;

    public function __construct(string $ID, string $sluggedName, string $URL, int $followerCount = 0, int $profileCount = 0, string $description = '')
    {
        $this->description = $description;
        $this->ID          = $ID;
        $this->sluggedName = $sluggedName;
        $this->URL         = $URL;

        $this->followerCount = $followerCount;
        $this->profileCount  = $profileCount;

        $this->followers = [];
        $this->profiles  = [];
    }

    /**
     * @param array <string> $followers
     */
    public function setFollowers(array $followers): void
    {
        $this->followers     = $followers;
        $this->followerCount = count($followers);
    }

    /**
     * @param array <string> $profiles
     */
    public function setProfiles(array $profiles): void
    {
        $this->profiles     = $profiles;
        $this->profileCount = count($profiles);
    }
}
