<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Base;
use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\API_Union;
use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Gender;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_Name;
use ShmuelAKam\Greenjeans\Api\Data\API_Path;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryEvents;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotos;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProfiles;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryVideos;
use ShmuelAKam\Greenjeans\Tests\Api\Help\Reflectrovitch;

use function ShmuelAKam\Greenjeans\Api\Core\boolToString;

/**
 * API_Profile test case.
 */
class API_ProfileTest extends API_BaseTest
{
    /**
     *
     * @var API_Profile
     */
    private API_Profile $aPI_Profile;

    public static function setUpBeforeClass(): void {}

    public static function tearDownAfterClass(): void {}

    /**
     * Prepares the environment before running each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Recreate before each test to avoid side-effects.
        // This is NOT yet a server profile, so there is no need to log it.
        $this->aPI_Profile = new API_Profile();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        unset($this->aPI_Profile);

        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * Provider for test profiles.
     * The values for each are: ID, GUID, Name, AboutMe, and the NEW AboutMe.
     *
     * @return array <array <string>>
     */
    public static function provideProfile(): array
    {
        /*
         * [https://www.geni.com/people/Adam/6000000214522348827 Adam Test Profile .]
         * [https://www.geni.com/people/Seth/6000000214522520831 Seth Test Profile .]
         * [https://www.geni.com/people/Enosh/6000000214522461839 Enosh Test Profile .]
         */
        return [
            'Adam' => [
                'profile-34838408159',
                '6000000214522348827',
                'Adam Test Profile .',
                "* [http://www.mechon-mamre.org/p/pt/pt0101.htm#26 Genesis 1:26-31]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0102.htm#7 Genesis 2:7-25]\r\n" . "* [http://quran.",
                'Yankee Dooble'
            ],
            // com/2#2:29 Quran 2:29-38]. * [http://quran.com/3#3:59 Quran 3:59]. Wikipedia: * [http://en.wikipedia.org/wiki/Adam Adam] & [http://he.wikipedia.org/wiki/%D7%90%D7%93%D7%9D_%D7%95%D7%97%D7%95%D7%94 אדם וחוה] * [http://en.wikipedia.org/wiki/Generations_of_Adam Generations of Adam] * [http://en.wikipedia.org/wiki/Adam_in_Islam Adam in Islam] The name 'Adam' originated from the Hebrew word, pronounced aw-dawm, which means man, as a species, male and female. The Holy Scriptures use this literal meaning for Adam, meaning simply humans. '

            'Seth' => [
                'profile-34838408177',
                '6000000214522520831',
                'Seth Test Profile .',
                "* [http://www.mechon-mamre.org/p/pt/pt0104.htm#25 Genesis 4:25-26]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0105.htm#3 Genesis 5:3-8] lists his place in",
                'Went to Town'
            ],
            // the ten generations between Adam & Noah. Normally genealogies are traced by the first-born sons, but Cain killed Abel and then was banished, so Seth became almost like a new “first-born”. * [http://www.biblestudytools.com/history/flavius-josephus/antiquities-jews/book-1/chapter-2.html Antiquities of the Jews, Book 1 Chapter 2:3.] Wikipedia: * [http://en.wikipedia.org/wiki/Seth Seth] and [http://he.wikipedia.org/wiki/%D7%A9%D7%AA שת]. * [https://en.wikipedia.org/wiki/Seth#Islam Seth in Islam.] Seth, 3rd son of Adam & Eve, was born after Cain murdered Abel. So the family line is continued through him. His name denoted this being derived from the root meaning 'appointed-given' instead of his brother (s), as Eve herself explains. ---- See also: [http://www.geni.com/profile/index/6000000003645707049 Seth Sat-naal]

            'Enosh' => [
                'profile-34838408195',
                '6000000214522461839',
                'Enosh Test Profile .',
                "* [http://www.mechon-mamre.org/p/pt/pt0104.htm#26 Genesis 4:26.]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0105.htm#6 Genesis 5:6-11.]\r\n" . "* [http://www.pse",
                'Riding on his Pony'
            ]
            // udoepigrapha.com/jubilees/4.htm Book of Jubilees: 4:11-13.] Wikipedia: [http://en.wikipedia.org/wiki/Enos_%28Bible%29 Enos] See also (don't merge): [http://www.geni.com/profile/index/6000000003645882249 Henokh] He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), Ethiopian Orthodox tradition considers him a 'faithful and righteous servant of God', and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, 'as an instrument for codifying the laws'. He is commemorated as one of the Holy Forefathers in the Calendar of Saints of the Armenian Apostolic Church on July 30. According to the Book of Jubilees (4:11-13) in the Ethiopian Orthodox Bible, Enos was born in the Year of the World 235, and 'he began to call on the name of the Lord on the earth.' He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), and she bare him Kenan in the year 325. Ethiopian Orthodox tradition considers him a 'faithful and righteous servant of God', and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, 'as an instrument for codifying the laws'[1]. Enos in Hinduism is mentioned as Anoohan and is considered being the son of Seth (Shwetha-nama)[citation needed]. The text carried about Enoch was mentioned in the last Purana the Bhavishya Purana which was summarised text done in Sanskrit and attributed to Rishi Vyasa, the compiler of the Vedas. '
        ];
    }

    /**
     * @return array <array <NULL>>
     */
    public static function provideNullProfile(): array
    {
        return [
            [
                null,
                null,
                null,
                null
            ]
        ];
    }

    /**
     * Provider for getProjects test.
     * No need for AboutMe, so set to NULL.
     *
     * @return array <string, array <string>>
     */
    public static function provideProfilesHaveProject(): array
    {
        return [
            'Adam'  => [ 'profile-53597431' ],
            'Seth'  => [ 'profile-4095031'  ],
            'Enosh' => [ 'profile-4094989'  ]
        ];
    }

    /**
     * @return array <string, array <string|array <string, mixed>>>
     * string, string, array <string, mixed>
     */
    public static function provideFullProfile(): array
    {
        return [
            'Adam'  => [ 'profile-53597431', '6000000003538706117', TestProfiles::$Adam  ],
            'Seth'  => [ 'profile-4095031',  '6000000006375916501', TestProfiles::$Seth  ],
            'Enosh' => [ 'profile-4094989',  '6000000000190131271', TestProfiles::$Enosh ]
        ];
    }

    /**
     * @return array <string, array <array <string, mixed>>>
     */
    public static function provideAddProfile(): array
    {
        return TestData::$addProfiles;
    }

    /**
     * Provider for testUpdateSetPublic test.
     * The first 2 columns define the set-up of the test (isAlive and isPublic)
     * The 3rd coulmn defines the value to set, and the fourth coulmn defines the
     * expected result. E.g. If the profile is alive it can NOT be set public.
     * @return array <string, array <string>|bool>
     */
    public static function provideAddProfileWithFlags(): array
    {
        $addFunctions = [
            'Child'   => [  'addChild'   ],
            'Parent'  => [  'addParent'  ],
            'Partner' => [  'addPartner' ],
            'Sibling' => [  'addSibling' ],
        ];

        $flagged = self::flagify($addFunctions, 4);

        return $flagged;
    }

    /**
     * Provides a set of FLAGS that determine the alive & public status of the two
     * profiles in an add-partner relation, and which Marriage & Divorce events to
     * create.
     * The 'base' profile is the one that the 'partner' is being added to.
     *
     * @return array <array <bool>>
     */
    public static function provideMarriageDivorceEvents(): array
    {
        //         $broken404 = [
        //             [  TRUE,  TRUE,  TRUE,  TRUE,  TRUE,      TRUE  ],
        //             [  TRUE,  TRUE,  TRUE,  TRUE,  TRUE,      FALSE ],
        //             [  TRUE,  TRUE,  TRUE,  FALSE, TRUE,      TRUE  ],
        //             [  TRUE,  TRUE,  TRUE,  FALSE, FALSE,     TRUE  ],
        //         ];

        //         return $broken404;

        $eventCombos = [
        // Marriage, Divorce
        [ true,      true  ],
        [ true,      false ],
        [ false,     true  ],
        [ false,     false ]
        ];

        //     base   base   partner partner
        //     Alive  Public Alive   Public
        $alivePublicCombos = [
            [  true,  true,  true,  true   ],
            [  true,  true,  true,  false  ],
            [  true,  true,  false, true   ],
            [  true,  true,  false, false  ],
            [  true,  false, true,  true   ],
            [  true,  false, true,  false  ],
            [  true,  false, false, true   ],
            [  true,  false, false, false  ],
            [  false, true,  true,  true   ],
            [  false, true,  true,  false  ],
            [  false, true,  false, true   ],
            [  false, true,  false, false  ],
            [  false, false, true,  true   ],
            [  false, false, true,  false  ],
            [  false, false, false, true   ],
            [  false, false, false, false  ]
        ];

        $combinedArray = [];

        foreach ($alivePublicCombos as $alivePublic)
        {
            foreach ($eventCombos as $events)
            {
                $combinedArray[] = array_merge($alivePublic, $events);
            }
        }

        return $combinedArray;
    }

    /**
     * Provides a set of FLAGS to determine which Marriage & Divorce Events to create.
     *
     * @return array <array <bool>>
     */
    public static function provideAddMarriageDivorceEvents(): array
    {
        $eventFlags = [
        // Marriage, Divorce
        [ true,      true  ],
        [ true,      false ],
        [ false,     true  ],
        [ false,     false ]
        ];

        return $eventFlags;
    }

    /**
     * @return array <array <bool>>
     */
    public static function provideMarriageFlags(): array
    {
        $allCombos = [
        // base   base    partner partner
        // Alive  Public   Alive  Public
        [  true,  true,    true,  true   ],
        [  true,  true,    true,  false  ],
        [  true,  true,    false, true   ],
        [  true,  true,    false, false  ],
        [  true,  false,   true,  true   ],
        [  true,  false,   true,  false  ],
        [  true,  false,   false, true   ],
        [  true,  false,   false, false  ],
        [  false, true,    true,  true   ],
        [  false, true,    true,  false  ],
        [  false, true,    false, true   ],
        [  false, true,    false, false  ],
        [  false, false,   true,  true   ],
        [  false, false,   true,  false  ],
        [  false, false,   false, true   ],
        [  false, false,   false, false  ]
        ];

        return $allCombos;
    }

    /**
     * Combines the test-profiles as SOURCE of data, to update to Test Profiles.
     * @return array <string, array <string|array <string, mixed>>>
     */
    public static function provideSetProfile(): array
    {
        return [
            // $targetID, $targetGUID, $targetName, $source

            'Adam'  => [ 'profile-34838408159', '6000000214522348827', 'Adam Test Profile .',  TestProfiles::$Adam  ],
            'Seth'  => [ 'profile-34838408195', '6000000214522461839', 'Seth Test Profile .',  TestProfiles::$Seth  ],
            'Enosh' => [ 'profile-34838408177', '6000000214522520831', 'Enosh Test Profile .', TestProfiles::$Enosh ]
        ];
    }

    /**
     * Provider for data for set tests, that we do NOT want to the other test data objects.
     *
     * @return array <string, array <string, array <string, string>>>
     */
    public static function provideSetData(): array
    {
        return TestData::$setProfiles;
    }

    /**
     * @return array <array <\ShmuelAKam\Greenjeans\Api\Data\API_Gender>>
     */
    public static function provideSetGender(): array
    {
        return FactoryProfiles::TEST_SetGender;
    }

    /**
     * Provider for testing flag setting tests.
     * The first columns enables setting an additional flag that can impact
     * the test. For example when testig setPublic, the other flag can be
     * is-alive.
     * I was lazy so I'm also using this for generic permutations of two flags.
     * @return array <array <bool>>
     */
    public static function provideSetFlag(): array
    {
        return [
        // Other   From    To      Expected
        // Flag
        [  true,   true,   true,   true   ],
        [  true,   true,   false,  false  ],
        [  true,   false,  false,  false  ],
        [  true,   false,  true,   true   ],
        [  false,  true,   true,   true   ],
        [  false,  true,   false,  false  ],
        [  false,  false,  false,  false  ],
        [  false,  false,  true,   true   ]
        ];
    }

    /**
     * @return array <string, array <string, mixed>>
     */
    public static function provideSetNames(): array
    {
        return TestData::$setProfilesNames;
    }

    /**
     * @return array <string, array <string, mixed>>
     */
    public static function provideProfileGETEvents(): array
    {
        return TestData::$testGET_Events;
    }

    /**
     * @return array <array <array <string>>>
     */
    public static function provideLifeEvents(): array
    {
        $eventSet = [
            [
                [
                    'Birth',
                    'Baptism',
                    'Death',
                    'Burial'
                ]
            ],
            [
                [
                    'Birth',
                    'Baptism',
                    'Death'
                ]
            ],
            [
                [
                    'Birth',
                    'Baptism',
                    'Burial'
                ]
            ],
            [
                [
                    'Birth',
                    'Baptism'
                ]
            ],
            [
                [
                    'Birth',
                    'Death',
                    'Burial'
                ]
            ],
            [
                [
                    'Birth',
                    'Death'
                ]
            ],
            [
                [
                    'Birth',
                    'Burial'
                ]
            ],
            [
                [
                    'Birth'
                ]
            ],
            [
                [
                    'Death',
                    'Burial'
                ]
            ],
            [
                [
                    'Death'
                ]
            ],
            [
                [
                    'Burial'
                ]
            ]
        ];

        return $eventSet;
    }

    /**
     * @return array <\ShmuelAKam\Greenjeans\Api\Data\API_Location>
     */
    public static function provideCurrentResidence(): array
    {
        return TestData::$currentResidences;
    }

    /**
     * @return array <array <string, string|array <string, string>>>
     */
    public static function provideDetailStrings(): array
    {
        return TestData::$detailStrings;
    }

    /**
     * @return array <string, array <string|\ShmuelAKam\Greenjeans\Api\Data\API_Path>>
     */
    public static function providePathTo(): array
    {
        $adamPath   = API_Path::newFromarray(TestData::$adamPathArray);
        $noahPath   = API_Path::newFromarray(TestData::$noahPathArray);
        $shmuelPath = API_Path::newFromarray(TestData::$shmuelPathArray);

        $pathToData = [
            'Adam to Cainan' =>         [ '6000000003538706117', '6000000000190220038', $adamPath ],
            'Noah to Jared.' =>         [ '6000000002145556219', '6000000001048812668', $noahPath ],
            'Shmuel A. to Shmuel E.' => [ '5098603059080070794', '4048631877200038987', $shmuelPath ],
        ];

        return $pathToData;
    }

    /**
     * @return array <array <int|bool|string>>
     */
    public static function provideSearchNames(): array
    {
        $searchNames = [
        // Name,               Found  Estimate
        [ 'Amalek',            true,   9 ],
        [ 'Agag',              true,  70 ],
        [ 'Haman the Agagite', true,   1 ],
        [ 'Zorkmill32',        false,  0 ]
        ];

        $flagged = self::flagify($searchNames);

        return $flagged;
    }

    /**
     * @return array <string, array <string>>
     */
    public static function provideCompareProfiles(): array
    {
        return [
            //            Test Profile GUID     Real Profile GUID
            'Adam'  => [ "6000000214522348827", "6000000003538706117" ],
            'Seth'  => [ "6000000214522520831", "6000000000190131271" ],
            'Enosh' => [ "6000000214522461839", "6000000006375916501" ],
        ];
    }

    /**
     * @return array <array <string>>
     */
    public static function provideAboutMe(): array
    {
        $Test_About_Mes = [
        [ 'Yankee Doodle'      ],
        [ 'Went to Town'       ],
        [ 'Riding on his Pony' ],
        ];

        return $Test_About_Mes;
    }

    /**
     *
     * @return array <array <string>>
     */
    public static function provideUpdateDetailStrings(): array
    {
        $Bokers = [];
        foreach (TestData::$detailStringsGoodMorning as $locale => $text)
        {
            $Bokers[] = [
                $locale,
                $text
            ];
        }

        return $Bokers;
    }

    /**
     *
     * @return array <array <string>>
     */
    public static function provideUpdateTwoDetailStrings(): array
    {
        $prevLocale = 'he';
        $prevText   = 'בוקר טוב';

        $Bokers = [];
        foreach (TestData::$detailStringsGoodMorning as $locale => $text)
        {
            $Bokers[] = [
                $prevLocale,
                $prevText,
                $locale,
                $text
            ];

            $prevLocale = $locale;
            $prevText   = $text;
        }

        return $Bokers;
    }

    /**
     *
     * @return array<array<int|string, bool|string|\ShmuelAKam\Greenjeans\Api\Data\API_Gender>>
     */
    public static function provideTesterProfiles(): array
    {
        $Test_Profile_Params = [
        [ 'LetMe',    'TellYou', 'alive' => false,  'public' => false, 'gender' => API_Gender::Female  ],
        [ 'DonWana',  'Know',    'alive' => false,  'public' => false, 'gender' => API_Gender::Male    ],
        [ 'Funan',    'Games',   'alive' => false,  'public' => true,  'gender' => API_Gender::Female  ],
        [ 'Superman', 'Sucks',   'alive' => false,  'public' => true,  'gender' => API_Gender::Male    ],
        [ 'Sabra',    'NProud',  'alive' => true,   'public' => false, 'gender' => API_Gender::Female  ],
        [ 'Hillel',   'OneFoot', 'alive' => true,   'public' => false, 'gender' => API_Gender::Male    ],
        [ 'ISee',     'You',     'alive' => true,   'public' => true,  'gender' => API_Gender::Female  ],
        [ 'SoFar',    'SoGood',  'alive' => true,   'public' => true,  'gender' => API_Gender::Male    ]
        ];

        return $Test_Profile_Params;
    }

    /**
     * @return array <array <int>>
     */
    public static function provideMergeSize(): array
    {
        return [
            [ 2 ],
            [ 3 ],
            [ 4 ]
        ];
    }

    /**
     * Provider for test documents.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideTestDocument(): array
    {
        return FactoryDocuments::TEST_DOCS;
    }

    /**
     * Provider for test Photos.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideTestPhoto(): array
    {
        return FactoryPhotos::TEST_PHOTOS;
    }

    /**
     * Provider for test Videos.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideTestVideo(): array
    {
        return FactoryVideos::TEST_VIDEO_FIELDS;
    }

    /**
     * Return number of media objects (documents, photos, videos) to
     * create for the get media tests.
     * @see API_ProfileTest::testGetDocuments
     * @see API_ProfileTest::testGetPhotos
     * @see API_ProfileTest::testGetVideos
     *
     * @return array <array <int>>
     */
    public static function provideMediaCount(): array
    {
        return [
            [ 1 ],
            [ 2 ],
            [ 5 ],
            [ 7 ]
        ];
    }

    /**
     * Return LARGE number of media objects (documents, photos, videos) to
     * create for the get media tests.
     * @see API_ProfileTest::testGetDocuments_ALL
     * @see API_ProfileTest::testGetPhotos_ALL
     * @see API_ProfileTest::testGetVideos_ALL
     * @return array <array <int>>
     */
    public static function provideMediaLargeCount(): array
    {
        return [
            [ 15 ],
            [ 25 ],
        ];
    }

    /**
     * @return array <array <string>>
     */
    public static function provideMugshotFiles(): array
    {
        // @formatter:off
        return [
            // Filename,        FileType,     Title,   Description
            [ 'File-SDK-1.jpg', 'image/jpeg', 'One',   'There can only be'                   ],
            [ 'File-SDK-2.jpg', 'image/jpeg', 'Too',   'I love you two'                      ],
            [ 'File-SDK-3.jpg', 'image/jpeg', 'Three', 'Cord of three is not quickly broken' ],
            [ 'File-SDK-4.png', 'image/png',  'Four',  'One for all, and Four for one'       ],
            [ 'File-SDK-5.png', 'image/png',  'Five',  'Five of Nine. Trek'                  ],
            [ 'File-SDK-6.png', 'image/png',  'Six',   'Six Six Six. Beast'                  ],
        ];
        // @formatter:on
    }

    // =====================================================
    // Helper functions.

    private function compareStrings(string $string1, string $string2): void
    {
        // Find first diff character by using XOR.
        $diffOffset = strspn($string1 ^ $string2, "\0");

        $str1Char = substr($string1, $diffOffset - 1, 10);
        $str2Char = substr($string2, $diffOffset - 1, 10);

        if (strcmp($str1Char, $str2Char) != 0)
        {
            echo "Diff at $diffOffset:\r\n";
            echo "  1: " . bin2hex($str1Char) . " => " . addcslashes($str1Char, "\0..\37") . "<<\n";
            echo "  2: " . bin2hex($str2Char) . " => " . addcslashes($str2Char, "\0..\37") . "<<\n";
        }
        // else {
        // if (strcmp ($string1, $string2) == 0) {
        // echo "Same.\r\n";
        // }
        // }
    }

    /**
     * This function does NOT create a API_Profile object.
     *
     * @param  array <string|array <string, mixed>> $profileTestArray
     * @return array <string, mixed>
     */
    private function makeProfileBrief(array $profileTestArray): array
    {
        $testProfileBrief = [
            'projects'  => $profileTestArray['projects'],
            'documents' => $profileTestArray['documents'],
            'photos'    => $profileTestArray['photos'],
            'videos'    => $profileTestArray['videos'],

            'values' => [
                'id'       => $profileTestArray['values']['id'],
//              'guid'     => $profileTestArray['values']['guid'],
                'name'     => $profileTestArray['values']['name'],
                'is_alive' => $profileTestArray['values']['is_alive'],
                'public'   => $profileTestArray['values']['public']
            ]
        ];

        return $testProfileBrief;
    }

    /**
     * @param  string|array <string, string> $map
     * @return string
     */
    private function removeHashValues(string|array $map): string
    {
        // TODO check this function
        $mapString = print_r($map, true);
        $mapArray  = explode("\n", $mapString);

        foreach ($mapArray as $key => $line)
        {
            $hashPos = strpos($line, "?hash");
            $line    = substr($line, 0, $hashPos);

            $mapArray[$key] = $line;
        }

        $cleanMap = implode("\n", $mapArray);

        return $cleanMap;
    }

    /**
     * @param bool $birth
     * @param bool $baptism
     * @param bool $marriage
     * @param bool $divorce
     * @param bool $death
     * @return array <API_Event|NULL>
     */
    private function createBMDEvents(bool $birth, bool $baptism, bool $marriage, bool $divorce, bool $death): array
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $randomName1 = implode(' ', $profileFactory->makeRandomName());
        $partnerName = implode(' ', $profileFactory->makeRandomName());
        $events      = [];

        if ($birth)
        {
            $events[] = $eventFactory->makeBirthEvent($randomName1);
        }
        else
        {
            $events[] = null;
        }

        if ($baptism)
        {
            $events[] = $eventFactory->makeBaptismEvent($randomName1);
        }
        else
        {
            $events[] = null;
        }

        if ($marriage)
        {
            $events[] = $eventFactory->makeMarriageEvent($randomName1, $partnerName);
        }
        else
        {
            $events[] = null;
        }

        if ($divorce)
        {
            $events[] = $eventFactory->makeDivorceEvent($randomName1, $partnerName);
        }
        else
        {
            $events[] = null;
        }

        if ($death)
        {
            $events[] = $eventFactory->makeDeathEvent($randomName1);
        }
        else
        {
            $events[] = null;
        }

        return $events;
    }

    /**
     * @param  array <API_Event> $eventArray
     * @return array <string, API_Event>
     */
    private function assignEventNames(array $eventArray): array
    {
        $namedEvents = [];

        foreach ($eventArray as $unNamedEvent)
        {
            $fullEventName = $unNamedEvent->getName();
            $eventName     = substr($fullEventName, 0, strpos($fullEventName, ' '));

            $namedEvents[$eventName] = $unNamedEvent;
        }

        return $namedEvents;
    }

    // =====================================================
    // Custom assert functions.
    private function assertMemberArray(API_Profile $profile, string $arrayName, ?int $expectedSize): void
    {
        $memberArray = Reflectrovitch::getArrayFromObject($profile, $arrayName);

        if ($memberArray !== null)
        {
            $this->assertNotNull($expectedSize);
            $this->assertCount($expectedSize, $memberArray, "$arrayName expected size- $expectedSize, " . "actual- " . count($memberArray));
        }
        else
        {
            $this->assertNull($expectedSize, "$arrayName unexpected NULL");
        }
    }

    private function assertValuesFieldEqual(API_Profile $profile, string $fieldName, string $expectedValue, bool $nullAllowed = false): void
    {
        $fieldValue = Reflectrovitch::getValuesField($profile, $fieldName);

        if (is_string($fieldValue))
        {
            $diffMessage = "Field '$fieldName' different.\n\nExpected: " . strlen($expectedValue)
                                                          . ", value: " . strlen($fieldValue) . "\r\n";

            $this->assertEquals($expectedValue, $fieldValue, $diffMessage);
        }
        else
        {
            $this->assertTrue($nullAllowed, "Field '$fieldName' NOT found in Profile.");
        }
    }

    /**
     * @param API_Profile $profile
     * @param string      $fieldName
     * @param array <mixed> $expectedValue
     */
    private function assertValuesArrayEqual(API_Profile $profile, string $fieldName, array $expectedValue): void
    {
        $arrayField = Reflectrovitch::getValuesFieldArray($profile, $fieldName);

        if (is_array($arrayField))
        {
            $diffMessage = "Field '$fieldName' different.\n\nExpected:\n" . print_r($expectedValue, true) . "Value:\n" . print_r($arrayField, true);

            $this->assertEquals($expectedValue, $arrayField, $diffMessage);
        }
        else
        {
            // returned NULL, because field not found.
            $this->fail("Field '$fieldName' NOT found in Profile.");
        }
    }

    private function assertValuesBoolEqual(API_Profile $profile, string $fieldName, bool $expectedValue): void
    {
        $fieldValue = Reflectrovitch::getValuesFieldBool($profile, $fieldName);

        if ($fieldValue === null)
        {
            $fieldValue = false;
        }

        $diffMessage = "Field '$fieldName' different.\n\nExpected: $expectedValue, value: $fieldValue";

        $this->assertEquals($fieldValue, $expectedValue, $diffMessage);
    }

    /**
     * @param array <string, mixed> $testProfile
     * @param string $fieldName
     * @param mixed  $APIValue
     * @param bool   $nullAllowed
     */
    private function assertTestFieldEquals(array $testProfile, string $fieldName, mixed $APIValue, bool $nullAllowed = false): void
    {
        if (array_key_exists($fieldName, $testProfile['values']))
        {
            $testValue = $testProfile['values'][$fieldName];

            if (is_array($APIValue))
            {
                sort($testValue);
                sort($APIValue);

                $this->assertEquals($testValue, $APIValue, "Array $fieldName not equal.");
            }
            elseif (is_object($APIValue))
            {
                if (get_class($APIValue) == \ShmuelAKam\Greenjeans\Api\Data\API_Event::class)
                {
                    $this->assertTrue(API_Event::__equals($testValue, $APIValue));
                }
                elseif (get_class($APIValue) == API_Location::class)
                {
                    $this->assertTrue(API_Location::__equals($testValue, $APIValue));
                }
            }
            else
            {
                $this->assertEquals($testValue, $APIValue, "$fieldName not equal.");
            }
        }
        else
        {
            // No test value. Check for empty values
            $nullFieldOK = (($APIValue == '') || (is_array($APIValue) && count($APIValue) == 0) || ($APIValue == null && $nullAllowed));

            if (is_array($APIValue))
            {
                $failMessage = "Field '$fieldName' NOT found in Test-Profile. Expected:\n" . print_r($APIValue, true);
            }
            else
            {
                $failMessage = "Field '$fieldName' NOT found in Test-Profile (Expected: $APIValue).";
            }

            $this->assertTrue($nullFieldOK, $failMessage);
        }
    }

    /**
     * @param array <string, mixed> $testProfile
     * @param string $fieldName
     * @param bool $APIValue
     * @param bool $nullAllowed
     */
    private function assertTestBoolSame(array $testProfile, string $fieldName, ?bool $APIValue, bool $nullAllowed = false): void
    {
        if (array_key_exists($fieldName, $testProfile['values']))
        {
            $failMessage = '';
            $testValue   = $testProfile['values'][$fieldName];

            $this->assertEquals($APIValue, $testValue, $failMessage);
        }
        else
        {
            // No test value. Check for empty values
            $nullFieldOK = (($APIValue == false) || $nullAllowed);

            $failMessage = "Field '$fieldName' NOT found in Test-Profile (Expected: $APIValue).";

            $this->assertTrue($nullFieldOK, $failMessage);
        }
    }

    /**
     * @param array <string, mixed> $testData
     * @param API_Event $actual
     * @param string $eventName
     */
    private function assertEventTestdata(array $testData, ?API_Event $actual, string $eventName): void
    {
        if (array_key_exists($eventName, $testData['values']))
        {
            // Specific event exists in test data.
            $expectedEvent = $testData['values'][$eventName];

            if ($expectedEvent != null)
            {
                //Verify that the server returned an event and that it is
                // identical to expected.

                $this->assertNotNull($actual, 'Missing server event data');

                $this->assertTrue(API_Event::__equals($expectedEvent, $actual), 'Events NOT equal');
            }
            else
            {
                // Expected event is NULL. Verify that actual event ALSO NULL.

                $this->assertNull($actual, 'Unexpected server event data');
            }
        }
        else
        {
            // Nothing to test.
            /** @phpstan-ignore-next-line */
            $this->assertTrue(true);
        }
    }

    private function assertEvents_MarriageDivorce(?API_Event $expected, ?API_Event $actual, string $spouse1, string $spouse2, string $eventType = ''): void
    {
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $eventsSame = $eventFactory->equalMarriageDivorceEvents($expected, $actual, $spouse1, $spouse2, $eventType);

        $this->assertTrue($eventsSame, "$eventType Events are not the same");
    }

    private function assertEvents_Other(?API_Event $expected, ?API_Event $actual, string $eventName = ''): void
    {
        $eventsSame = true;

        if ($expected != null && $actual != null)
        {
            // Both are NOT NULL. Compare the events details.

            $differences = '';

            if ($expected->getName() != $actual->getName())
            {
                $eventsSame  = false;
                $differences = 'name, ';
            }

            if ($expected->getDescription() != $actual->getDescription())
            {
                $eventsSame = false;
                $differences .= 'description, ';
            }

            if (! API_Date::__equals($expected->getDate(), $actual->getDate()))
            {
                $eventsSame = false;
                $differences .= 'date, ';
            }

            $expectedLocation = $expected->getLocation();
            $actualLocation   = $actual->getLocation();

            if ($expectedLocation != null && $actualLocation != null)
            {
                if ($expectedLocation->getCountry() != $actualLocation->getCountry())
                {
                    $eventsSame = false;
                    $differences .= 'country, ';
                }

                if ($expectedLocation->getCity() != $actualLocation->getCity())
                {
                    $eventsSame = false;
                    $differences .= 'city, ';
                }

                if ($expectedLocation->getStreetAddress() != $actualLocation->getStreetAddress())
                {
                    $eventsSame = false;
                    $differences .= 'street, ';
                }
            }
            elseif ($expectedLocation != null || $actualLocation != null)
            {
                // Only ONE is not NULL (if both are NULL that's OK).

                $eventsSame = false;
                $differences .= 'location, ';
            }

            if ($differences != '')
            {
                echo "Differences: $differences";
            }
        }
        elseif ($expected == null && $actual == null)
        {
            $eventsSame = true;
        }

        $this->assertTrue($eventsSame, "$eventName Events are not the same");
    }

    /**
     * @param array<string, string> $expected
     * @param API_Name $actual
     */
    private function assertFullName(array $expected, API_Name $actual): void
    {
        // Create API_Name object based on the test-value.
        $expectedNameObj = API_Name::newFromArray($expected);

        $this->assertEquals($expectedNameObj, $actual);
    }

    private function assertGetEquals(API_Profile $get, API_Profile $base): void
    {
        $msg = 'Profiles are not the same. ';

        // Depending on the test, $base might NOT have ID & GUID values.
        $this->assertNotEquals(0, $get->getID());
        $this->assertNotEquals(0, $get->getGUID());

        $baseFullName = $base->getFullName();
        $getFullName  = $get->getFullName();

        if ($baseFullName->getFirstName() != null)
        {
            $this->assertEquals($baseFullName->getFirstName(), $getFullName->getFirstName(), $msg . 'First name');
        }

        if ($baseFullName->getMiddleName() != null)
        {
            $this->assertEquals($baseFullName->getMiddleName(), $getFullName->getMiddleName(), $msg . 'Middle name');
        }

        if ($baseFullName->getLastName() != null)
        {
            $this->assertEquals($baseFullName->getLastName(), $getFullName->getLastName(), $msg . 'Last name');
        }

        $this->assertEquals($base->isAlive(), $get->isAlive(), $msg . 'is Alive');
        $this->assertEquals($base->isPublic(), $get->isPublic(), $msg . 'is Public');
        $this->assertEquals($base->getGender(), $get->getGender(), $msg . 'Gender');
    }

    /**
     * @param array <string, array <string, string>>  $detailStrings
     * @param array <string, array <string, string>>  $actual
     */
    private function assertDetailStringsEquals(array $detailStrings, array $actual): void
    {
        $this->assertEquals(count($detailStrings), count($actual));

        foreach ($detailStrings as $locale => $detail)
        {
            $this->assertArrayHasKey($locale, $actual);

            $this->assertEquals($detail, $actual[$locale]['about_me']);
        }
    }

    /**
     * @param array <array <string, string|mixed>>  $compareResult
     * @param string $firstGUID
     * @param string $secondGUID
     */
    private function assertCompareProfiles(array $compareResult, string $firstGUID, string $secondGUID): void
    {
        $this->assertCount(2, $compareResult);

        $this->assertArrayHasKey('focus', $compareResult[0]);
        $this->assertArrayHasKey('focus', $compareResult[1]);

        $this->assertEquals($firstGUID, $compareResult[0]['focus']['guid']);
        $this->assertEquals($secondGUID, $compareResult[1]['focus']['guid']);
    }

    /**
     * @param array <\ShmuelAKam\Greenjeans\Api\API_MediaBase> $expectedList
     * @param array <string>                  $expectedIDs
     * @param array <\ShmuelAKam\Greenjeans\Api\API_MediaBase> $actualList
     * @param array <string>                  $actualIDs
     */
    private function assertTagList(array $expectedList, array $expectedIDs, array $actualList, array $actualIDs): void
    {
        // Assert object IDs.

        $expectIDs = array_unique($expectedIDs);
        sort($expectIDs);
        sort($actualIDs);

        $this->assertEquals($expectIDs, $actualIDs, 'IDs are different.');

        // Assert objects.

        sort($expectedList);
        sort($actualList);

        // $this->assertEquals($expectedList, $actualList, 'Data is different.');
    }

    /**
     * @param array <string> $expectedLabels
     * @param array <string> $actualLabels
     */
    private function assertLabels(array $expectedLabels, array $actualLabels): void
    {
        // Some "cheating" because Geni returns SOME of the values with
        // Capital letters made lowercase. So convering all to lowercase
        // before comparing.

        // Convert both arrays to lowercase for a case-insensitive comparison
        $expectedLabels = array_map('strtolower', $expectedLabels);
        $actualLabels   = array_map('strtolower', $actualLabels);

        sort($expectedLabels);
        sort($actualLabels);

        $this->assertEquals($expectedLabels, $actualLabels, 'Labels');
    }

    // =====================================================
    // Test functions.

    /**
     * Tests API_Profile->__construct ()
     */
    public function test__construct(): void
    {
        $this->aPI_Profile = new API_Profile();

        // ----------------------------------------
        // Check that array members are empty.

        $this->assertMemberArray($this->aPI_Profile, 'projects', 0);

        $this->assertMemberArray($this->aPI_Profile, 'documents', null);
        $this->assertMemberArray($this->aPI_Profile, 'photos', null);
        $this->assertMemberArray($this->aPI_Profile, 'videos', null);

        // setFields value should be [ 'id', 'guid', 'name', 'is_alive', 'public' ]
        $this->assertMemberArray($this->aPI_Profile, 'setFields', 4);

        $this->assertMemberArray($this->aPI_Profile, 'values', 0);
    }

    /**
     * Tests API_Profile->add ()
     *
     * @dataProvider provideAddProfile
     * @param array <array <string, mixed>> $addProfileArray
     */
    public function testAdd(array $addProfileArray): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        // Using newFromArray to provide the information, WITHOUT creating a profile.
        $this->aPI_Profile = API_Profile::newFromArray($addProfileArray['values']);

        // ----------------------------------------
        $addedProfile = $this->aPI_Profile->add();
        // ----------------------------------------

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());
    }

    /**
     * Tests API_Profile->add ()
     *
     * @dataProvider provideTwoFlags
     */
    public function testAddWithFlags(bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        list($this->aPI_Profile, ) = $profileFactory->makeRandomShell();

        $this->aPI_Profile->setIsAlive($alive);
        $this->aPI_Profile->setPublic($public);

        // ----------------------------------------
        $addedProfile = $this->aPI_Profile->add();
        // ----------------------------------------

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());

        $addedAlive  = $addedProfile->isAlive();
        $addedPublic = $addedProfile->isPublic();

        if ($alive && $public)
        {
            // It is NOT possible to set both of these flags to TRUE.
            // The Server will automtically set 'public' to FALSE.

            $this->assertEquals($alive, $addedAlive);
            $this->assertEquals($addedPublic, false);
        }
        else
        {
            $this->assertEquals($alive, $addedAlive);
            $this->assertEquals($addedPublic, $addedPublic);
        }
    }

    /**
     * Tests API_Profile->addChild ()
     *
     * @param $addProfileArray array
     *            Data of child to add.
     *
     * @dataProvider provideAddProfile
     * @param array <array <string, mixed>> $addProfileArray
     */
    public function testAddChild(array $addProfileArray): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $parentProfile = $profileFactory->makeRandomProfile(); // Set-up.

        // Using newFromArray to provide the information, WITHOUT creating a profile.
        $this->aPI_Profile = API_Profile::newFromArray($addProfileArray['values']);

        // --------------------------------------------------
        $addedProfile = $parentProfile->addChild($this->aPI_Profile);
        // --------------------------------------------------

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.
    }

    /**
     * Tests API_Profile->addParent ()
     *
     * @dataProvider provideAddProfile
     * @param array <array <string, mixed>> $addProfileArray
     */
    public function testAddParent(array $addProfileArray): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $childProfile = $profileFactory->makeRandomProfile(); // Set-up.

        // Using newFromArray to provide the information, WITHOUT creating a profile.
        $this->aPI_Profile = API_Profile::newFromArray($addProfileArray['values']);

        // --------------------------------------------------
        $addedProfile = $childProfile->addParent($this->aPI_Profile);
        // --------------------------------------------------

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.
    }

    /**
     * Tests API_Profile->addPartner ()
     *
     * @dataProvider provideAddProfile
     * @param array <array <string, mixed>> $addProfileArray
     */
    public function testAddPartner(array $addProfileArray): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $partnerProfile = $profileFactory->makeRandomProfile(); // Set-up.

        $this->aPI_Profile = API_Profile::newFromArray($addProfileArray['values']);

        // ----------------------------------------
        $addedProfile = $partnerProfile->addPartner($this->aPI_Profile, null, null);
        // ----------------------------------------

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.
    }

    /*
     * TODO Finish these tests or delete them.
     * Tests API_Profile->addPartner ()
     * @dataProvider provideAddMarriageDivorceEvents
     * @dataProvider provideMarriageFlags
     */

    /*
     * public function testAddPartner_WithEvents (bool $marriage, bool $divorce): void
     * public function testAddPartner_WithEvents_ERROR (bool $baseAlive, bool $basePublic, bool $partnerAlive, bool $partnerPublic): void
     * {
     * static $count = 1; //XXX
     * static $tag = substr(time(), -4);
     *
     *   /**
     *    * @var FactoryEvents   $eventFactory
     *    * @var FactoryProfiles $profileFactory
     *    * /
     *   $eventFactory   = FactoryBase::getFactory(API_Event::class);
     *   $profileFactory = FactoryBase::getFactory(API_Profile::class);
     *
     * echo "========================================================\r\n"; //XXX
     *
     *  // $base = $profileFactory->makeRandomProfile (dead: FALSE);
     * $base = $profileFactory->makeTestProfile ('Shmuel A.', "Kam $tag", $baseAlive, $basePublic, API_Gender::Male);
     * $baseName = $base->getName ();
     *
     *  // list ($partner, $partnerName) = $profileFactory->makeRandomShell ($partnerAlive); //<<===== XXX
     * list ($partner, $partnerName) = $profileFactory->makeShell ('Anat', "Laufer $tag", $partnerAlive, $partnerPublic, API_Gender::Female); //<<===== XXX
     *
     * $marriageEvent = NULL;
     * $divorceEvent = NULL;
     *
     * $marriage = TRUE; //XXX
     * $divorce = FALSE; //XXX
     *
     * if ($marriage) $marriageEvent = $eventFactory->makeMarriageEvent ($baseName, $partnerName);
     * if ($divorce) $divorceEvent = $eventFactory->makeDivorceEvent ($baseName, $partnerName);
     *
     * $count++; //XXX
     * $this->printFail ($count, $baseAlive, $basePublic, $partnerAlive, $partnerPublic);
     *
     *  //----------------------------------------
     * $addedProfile = $base->addPartner ($partner, $marriageEvent, $divorceEvent);
     *  //----------------------------------------
     *
     * if ($addedProfile == NULL) //XXX
     * {
     * }
     *
     * $this->assertNotNull ($addedProfile);
     * $this->assertNotEquals (0, $addedProfile->getID ());
     *
     * $profileFactory->logCreate($addedProfile->getID()); // Clean-up.
     *
     *  // There is NO direct way to get back the marriage & divorce events.
     *  // They can only be found via the Union object. There is only the one
     *  // union for these test profiles.
     *
     * $unionID = $base->getUnions()[0];
     * $union = API_Union::get ($unionID);
     *
     * $addedMarriage = $union->getMarriage();
     * $addedDivorce = $union->getDivorce();
     *
     * $this->assertEvents_MarriageDivorce ($marriageEvent, $addedMarriage, $baseName, $partnerName, 'Marriage');
     * $this->assertEvents_MarriageDivorce ($divorceEvent, $addedDivorce, $baseName, $partnerName, 'Divorce');
     * }
     *
     */

    /**
     * Tests API_Profile->addPartner ()
     *
     * @dataProvider provideMarriageDivorceEvents
     * @param bool $baseAlive
     * @param bool $basePublic
     * @param bool $partnerAlive
     * @param bool $partnerPublic
     * @param bool $marriage
     * @param bool $divorce
     */
    public function testAddPartner_WithEvents(
        bool $baseAlive,
        bool $basePublic,
        bool $partnerAlive,
        bool $partnerPublic,
        bool $marriage,
        bool $divorce,
    ): void {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $base     = $profileFactory->makeRandomProfile($baseAlive);
        $baseName = $base->getName();

        list($partner, $partnerName) = $profileFactory->makeRandomShell($partnerAlive);
        $partner->setPublic($partnerPublic);

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
        $addedProfile = $base->addPartner($partner, $marriageEvent, $divorceEvent);
        // ----------------------------------------

        // There is NO direct way to get back the marriage & divorce events.
        // They can only be found via the Union object. There is only the one
        // union for these test profiles.

        $unionID = $base->getUnions()[0];
        $union   = API_Union::get($unionID);

        if ($addedProfile == null)
        {
            // This can happen when there is an error on the Server. So we validate
            // the marriage by getting the partner's profile through the union object.

            $partnerID = $union->getOtherPartner($base->getID());

            $addedProfile = API_Profile::get($partnerID);
        }

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.

        $addedMarriage = $union->getMarriage();
        $addedDivorce  = $union->getDivorce();

        $this->assertEvents_MarriageDivorce($marriageEvent, $addedMarriage, $baseName, $partnerName, 'Marriage');
        $this->assertEvents_MarriageDivorce($divorceEvent, $addedDivorce, $baseName, $partnerName, 'Divorce');
    }

    /**
     * Tests all API_Profile->add_____ ($addProfile) functions (except add itself).
     *
     * @dataProvider provideAddProfileWithFlags
     * @param string $addFunction
     * @param bool $baseAlive
     * @param bool $basePublic
     * @param bool $alive
     * @param bool $public
     */
    public function testAddersWithFlags(string $addFunction, bool $baseAlive, bool $basePublic, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $relativeProfile = $profileFactory->makeRandomProfile($baseAlive, $basePublic); // Set-up.

        list($this->aPI_Profile, ) = $profileFactory->makeRandomShell();

        $this->aPI_Profile->setIsAlive($alive);
        $this->aPI_Profile->setPublic($public);

        // ----------------------------------------
        $addedProfile = $relativeProfile->$addFunction($this->aPI_Profile);
        // ----------------------------------------

        $addedAlive  = $addedProfile->isAlive();
        $addedPublic = $addedProfile->isPublic();

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.

        if ($alive && $public)
        {
            // It is NOT possible to set both of these flags to TRUE.
            // The Server will automtically set 'public' to FALSE.

            $this->assertEquals($alive, $addedAlive);
            $this->assertEquals($addedPublic, false);
        }
        else
        {
            $this->assertEquals($alive, $addedAlive);
            $this->assertEquals($addedPublic, $addedPublic);
        }
    }

    private function printFail(int $count, bool $baseAlive, bool $basePublic, bool $partnerAlive, bool $partnerPublic): void
    {
        echo "Failure: " . str_pad((string) $count, 3, ' ', STR_PAD_LEFT) . '  '
             . boolToString($baseAlive) . boolToString($basePublic)
             . boolToString($partnerAlive) . boolToString($partnerPublic) . "\r\n";
    }

    /**
     * Tests API_Profile->addSibling ($addProfile)
     *
     * @dataProvider provideAddProfile
     * @param array <array <string, mixed>> $addProfileArray
     */
    public function testAddSibling(array $addProfileArray): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $siblingProfile = $profileFactory->makeRandomProfile(); // Set-up.

        $this->aPI_Profile = API_Profile::newFromArray($addProfileArray['values']);

        // ----------------------------------------
        $addedProfile = $siblingProfile->addSibling($this->aPI_Profile);
        // ----------------------------------------

        $this->assertNotNull($addedProfile);
        $this->assertNotEquals(0, $addedProfile->getID());

        $profileFactory->logCreate($addedProfile->getID()); // Clean-up.
    }

    /**
     * Tests API_Profile->get ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGet(string $id, string $guid, array $profileTestArray): void
    {
        //--------------------------------------------------
        $this->aPI_Profile = API_Profile::get($id);
        //--------------------------------------------------

        $same = $this->aPI_Profile->compareToArray($profileTestArray);
        $this->assertTrue($same, 'Profiles are not the same.');
    }

    /**
     * Tests API_Profile->get ()
     *
     * This provider used to generate multiple calls. It's data can be ignored.
     *
     * @dataProvider provideRunCount
     */
    public function testGet_NewProfile(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $base = $profileFactory->makeRandomProfile();

        // The following API_Profile object is a shell, i.e. it has values set,
        // but has NOT been added to the Server. That will be done by the ADD
        // function.
        // Ignore the name return value.

        list($new, ) = $profileFactory->makeRandomShell();

        $added   = $base->addPartner($new);
        $addedID = $added->getID();

        // Need to log the profile created by the addPartner method.
        $profileFactory->logCreate($addedID);

        // --------------------------------------------------
        $this->aPI_Profile = API_Profile::get($addedID);
        // --------------------------------------------------

        $this->assertGetEquals($added, $this->aPI_Profile);
    }

    /**
     * Tests API_Profile->get ()
     *
     * @dataProvider provideRunCount
     */
    public function testGet_GUID_NewProfile(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $source     = $profileFactory->makeRandomProfile();
        $sourceGUID = $source->getGUID();

        // --------------------------------------------------
        $this->aPI_Profile = API_Profile::get($sourceGUID);
        // --------------------------------------------------

        $this->assertTrue($this->aPI_Profile->comparePartial($source), 'Profiles are not the same.');
    }

    /**
     * Tests API_Profile->getBrief ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetBrief(string $id, string $guid, array $profileTestArray): void
    {
        // --------------------------------------------------
        $this->aPI_Profile = API_Profile::getBrief($id);
        // --------------------------------------------------

        $profileTestArrayBrief = $this->makeProfileBrief($profileTestArray);

        $this->assertTrue($this->aPI_Profile->compareToArray($profileTestArrayBrief, isBrief: true), 'Briefs are not the same.');
    }

    /**
     * Tests API_Profile->getProfileSelf ()
     */
    public function testGetProfileSelf(): void
    {
        $this->aPI_Profile = API_Profile::getProfileSelf(brief: false);

        $this->assertTrue($this->aPI_Profile->compareToArray(TestProfiles::$Myself), "Test for 'getProfileSelf' failed.");
    }

    // =====================================================

    /**
     * Tests API_Profile->addDocument ()
     *
     * @dataProvider provideTestDocument
     */
    public function testAddDocument(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);
        /** @var FactoryDocuments $documentFactory */
        $documentFactory = FactoryBase::getFactory(API_Document::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        $docDate    = $dateFactory->makeRandomDate();
        $createDate = time();

        // --------------------------------------------------
        $addedDoc = $this->aPI_Profile->addDocument($docDate, $description, $file_name, $labels, null, $text, $title);
        // --------------------------------------------------

        $addedID = $addedDoc->getID();
        $documentFactory->logCreate($addedID);

        $checkerDoc = API_Document::get($addedID);

        $labelsArray   = API_Base::parseArrayOrCommaList($labels);
        $checkerLabels = $checkerDoc->getLabels();
        sort($labelsArray);
        sort($checkerLabels);

        $this->assertEquals($title, $checkerDoc->getTitle(), 'Title');
        $this->assertEquals($description, $checkerDoc->getDescription(), 'Description');
        $this->assertEquals($docDate, $checkerDoc->getDate(), 'Date');
        $this->assertLabels($labelsArray, $checkerLabels);

        $this->assertEqualsWithDelta($createDate, (int) $checkerDoc->getCreatedAt(), 20, 'CreatedAt');
    }

    /**
     * Tests API_Profile->addMugshot ()
     *
     * @dataProvider provideMugshotFiles
     */
    public function testAddMugshot(string $filename, string $fileType, string $title, string $description): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();
        $profileGUID       = $this->aPI_Profile->getGUID();

        $SDK_Album    = $photoFactory->getAlbumID();
        $mugDate      = $dateFactory->makeRandomDate();
        $fullFileName = $photoFactory->fullFileName($filename);

        // --------------------------------------------------
        $mugPhoto = $this->aPI_Profile->addMugshot($SDK_Album, $mugDate, $description, $fullFileName, null, $title, $fileType);
        // --------------------------------------------------

        $mugPhotoID = $mugPhoto->getID();

        $photoFactory->logCreate($mugPhotoID);

        // Verify that mugshot is tagged in profile.

        $checkerProfile = API_Profile::get($profileID);

        $profilePhotosList = $checkerProfile->getPhotos(IDsOnly: false);

        /** @var array <API_Photo> $profilePhotosArray */
        $profilePhotosArray = $profilePhotosList->getList();

        $firstKey = array_key_first($profilePhotosArray);
        $this->assertEquals(1, count($profilePhotosArray));
        $this->assertEquals($mugPhotoID, $profilePhotosArray [$firstKey]->getID());

        // --------------------------------------------------
        // Verify that profile is tagged in mugshot.

        $checkerMug = API_Photo::get($mugPhotoID);

        $mugTagsList  = $checkerMug->getTags();
        $mugTagsArray = $mugTagsList->getList();

        // Server seems to automatically ALSO tag the person making the call.
        unset($mugTagsArray[API_BaseTest::getRunningProfileID()]);

        $this->assertEquals(1, count($mugTagsArray));
        $this->assertEquals($profileGUID, $mugTagsArray[array_key_first($mugTagsArray)]);
    }

    /**
     * Tests API_Profile->addMugshot ()
     *
     * @dataProvider provideMugshotFiles
     */
    public function testAddMugshotWithID(string $ignore, string $ignore2, string $title, string $description): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $SDK_Album         = $photoFactory->getAlbumID();
        $photo             = $photoFactory->makeRandomPhoto($SDK_Album);
        $mugDate           = $dateFactory->makeRandomDate();

        $profileID   = $this->aPI_Profile->getID();
        $profileGUID = $this->aPI_Profile->getGUID();
        $photoID     = $photo->getID();

        // --------------------------------------------------
        $mugPhoto = $this->aPI_Profile->addMugshot(
            $SDK_Album,
            $mugDate,
            $description,
            filePathname: null,
            photo_id: $photoID,
            title: $title,
            fileType: null,
        );
        // --------------------------------------------------

        $mugPhotoID = $mugPhoto->getID();

        $photoFactory->logCreate($mugPhotoID);

        // Verify that mugshot is tagged in profile.

        $checkerProfile = API_Profile::get($profileID);

        $profilePhotosList = $checkerProfile->getPhotos(IDsOnly: false);

        /** @var array <API_Photo> $profilePhotosArray */
        $profilePhotosArray = $profilePhotosList->getList();
        $firstPhoto         = array_key_first($profilePhotosArray);

        $this->assertEquals(1, count($profilePhotosArray), 'Mugshot not tagged in profile');
        $this->assertEquals($mugPhotoID, $profilePhotosArray[$firstPhoto]->getID(), 'Mugshot not tagged in profile');

        // --------------------------------------------------
        // Verify that profile is tagged in mugshot.

        $checkerMug = API_Photo::get($mugPhotoID);

        $mugTagsList  = $checkerMug->getTags();
        $mugTagsArray = $mugTagsList->getList();

        // Server seems to automatically ALSO tag the person making the call.
        unset($mugTagsArray[API_BaseTest::getRunningProfileID()]);

        $this->assertEquals(1, count($mugTagsArray), 'Wrong number of tagged people.');
        $this->assertEquals($profileGUID, reset($mugTagsArray), 'Profile not tagged/');
    }

    /**
     * Tests API_Profile->addPhoto ()
     *
     * @dataProvider provideTestPhoto
     */
    public function testAddPhoto(string $title, ?string $dateJSON, ?string $description, ?string $fileName, ?string $fileType): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        $docDate    = $dateFactory->makeRandomDate();
        $createDate = time();

        $fullFileName = $photoFactory->fullFileName($fileName);

        // --------------------------------------------------
        $addedPhoto = $this->aPI_Profile->addPhoto($docDate, $description, $fullFileName, $title, $fileType);
        // --------------------------------------------------

        $addedID = $addedPhoto->getID();
        $photoFactory->logCreate($addedID);

        $checkerPhoto     = API_Photo::get($addedID);
        $checkerCreatedAt = (int) $checkerPhoto->getCreatedAt();

        $this->assertEquals($title, $checkerPhoto->getTitle(), "Title doesn't match");
        $this->assertEquals($description, $checkerPhoto->getDescription(), "Description doesn't match");
        $this->assertEquals($docDate, $checkerPhoto->getDate(), "Date doesn't match");
        $this->assertEqualsWithDelta($createDate, $checkerCreatedAt, 20, "Creation date doesn't match");
    }

    /**
     * Tests API_Profile->addVideo ()
     *
     * @dataProvider provideTestVideo
     */
    public function testAddVideo(string $title, ?string $dateJSON, ?string $description, ?string $fileName, ?string $fileType): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);
        /** @var FactoryVideos $videoFactory */
        $videoFactory = FactoryBase::getFactory(API_Video::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        $docDate    = $dateFactory->makeRandomDate();
        $createDate = time();

        $fullFileName = $videoFactory->fullFileName($fileName);

        // --------------------------------------------------
        $addedVideo = $this->aPI_Profile->addVideo($docDate, $description, $fullFileName, $title, $fileType);
        // --------------------------------------------------

        $addedID = $addedVideo->getID();
        $videoFactory->logCreate($addedID);

        $checkerVideo = API_Video::get($addedID);

        $this->assertEquals($title, $checkerVideo->getTitle());
        $this->assertEquals($description, $checkerVideo->getDescription());
        $this->assertEquals($docDate, $checkerVideo->getDate());
        $this->assertEqualsWithDelta($createDate, (int) $checkerVideo->getCreatedAt(), 20);
    }

    /**
     * Tests API_Profile->updateAboutMe ()
     *
     * @dataProvider provideTesterProfiles
     */
    public function testUpdateAboutMe_Empty(string $firstName, string $lastName, bool $alive, bool $public, API_Gender $gender): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $alive, $public, $gender);
        $this->aPI_Profile->setAboutMe('Text to erase.');
        $this->aPI_Profile->update();
        $profileID = $this->aPI_Profile->getID();

        // --------------------------------------------------
        $updated = $this->aPI_Profile->updateAboutMe('');
        // --------------------------------------------------

        $this->assertTrue($updated, 'Update AboutMe failed.');

        $checkerProfile = API_Profile::get($profileID);

        $aboutMe = $checkerProfile->getAboutMe(force: true);

        $this->assertEquals($aboutMe, '', 'AboutMe not empty');
    }

    /**
     * Tests API_Profile->updateAboutMe ()
     *
     * @dataProvider provideAboutMe
     */
    public function testUpdateAboutMe(string $testAboutMe): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('LetMe', 'TellYou', alive: false, public: false, gender: API_Gender::Female); // Set-up.
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $updated = $this->aPI_Profile->updateAboutMe($testAboutMe);
        // ----------------------------------------

        $this->assertTrue($updated, 'Update AboutMe failed.');

        $checkerProfile = API_Profile::get($profileID);

        $aboutMe = $checkerProfile->getAboutMe(force: true);

        $this->assertEquals($testAboutMe, $aboutMe, 'Updated AboutMe not the same.');
    }

    /**
     * Tests API_Profile->updateAboutMe ()
     *
     * @dataProvider provideAboutMe
     */
    public function testUpdateAboutMePublic(string $testAboutMe): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $updated = $this->aPI_Profile->updateAboutMe($testAboutMe);
        // ----------------------------------------

        $this->assertTrue($updated, 'Update AboutMe failed.');

        $checkerProfile = API_Profile::get($profileID);

        $aboutMe = $checkerProfile->getAboutMe(force: true);

        $this->assertEquals($testAboutMe, $aboutMe, 'Updated AboutMe not the same.');
    }

    // /**
    // * Tests API_Profile->updateDetailStrings ()
    // *
    // * @dataProvider provideUpdateDetailStrings
    // */
    // public function testUpdateDetailStrings (string $locale, string $text): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);

    // $this->aPI_Profile = $profileFactory->makeRandomProfile (dead: TRUE); // Set-up.

    // //----------------------------------------
    // $updated = $this->aPI_Profile->updateDetailStrings ($locale, $text);
    // //----------------------------------------

    // $details = $this->aPI_Profile->getDetailStrings (force: TRUE);

    // $this->assertTrue ($updated, 'Update DetailStrings failed.');
    // $this->assertNotNull ($details, 'No results returned');
    // $this->assertArrayHasKey ($locale, $details, 'Locale not found.');
    // $this->assertArrayHasKey ('about_me', $details [$locale], 'about_me not found.');

    // $this->assertEquals ($text, $details[$locale]['about_me'], 'Updated DetailsString not the same.');
    // }

    // /**
    // * Tests API_Profile->updateDetailStrings ()
    // *
    // * @dataProvider provideUpdateTwoDetailStrings
    // */
    // public function testUpdateDetailStringsTwo (string $locale1, string $text1, string $locale2, string $text2): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);

    // $this->aPI_Profile = $profileFactory->makeRandomProfile (dead: TRUE); // Set-up.

    // //----------------------------------------
    // $updated1 = $this->aPI_Profile->updateDetailStrings ($locale1, $text1);
    // $updated2 = $this->aPI_Profile->updateDetailStrings ($locale2, $text2);
    // //----------------------------------------

    // $this->assertTrue ($updated1, "Update DetailStrings ($locale1) failed.");
    // $this->assertTrue ($updated2, "Update DetailStrings ($locale2) failed.");

    // $details = $this->aPI_Profile->getDetailStrings (force: TRUE);

    // $this->assertNotNull ($details, 'No results returned');

    // $this->assertArrayHasKey ($locale1, $details, "Locale ($locale1) not found.");
    // $this->assertArrayHasKey ($locale2, $details, "Locale ($locale2) not found.");

    // $this->assertArrayHasKey ('about_me', $details [$locale1], "about_me ($locale1) not found.");
    // $this->assertArrayHasKey ('about_me', $details [$locale2], "about_me ($locale2) not found.");

    // $this->assertEquals ($text1, $details[$locale1]['about_me'], 'Updated DetailsString not the same.');
    // $this->assertEquals ($text2, $details[$locale2]['about_me'], 'Updated DetailsString not the same.');
    // }

    // /**
    // * Tests API_Profile->updateDetailStrings ()
    // *
    // * @dataProvider provideUpdateDetailStrings
    // */
    // public function testUpdateDetailStringsMultiple (string $locale, string $text): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);

    // $this->aPI_Profile = $profileFactory->makeRandomProfile (dead: TRUE); // Set-up.

    // //----------------------------------------
    // $updatedEN = $this->aPI_Profile->updateDetailStrings ('en-US', 'Good Morning');
    // $updatedHE = $this->aPI_Profile->updateDetailStrings ('he', 'בוקר טוב!');
    // $updated = $this->aPI_Profile->updateDetailStrings ($locale, $text);
    // //----------------------------------------

    // $details = $this->aPI_Profile->getDetailStrings (force: TRUE);

    // $this->assertTrue ($updatedEN, 'Update DetailStrings (en) failed.');
    // $this->assertTrue ($updatedHE, 'Update DetailStrings (he) failed.');
    // $this->assertTrue ($updated, "Update DetailStrings ($locale) failed.");

    // $this->assertNotNull ($details, 'No results returned');

    // $this->assertArrayHasKey ('en-US', $details, 'Locale (en) not found.');
    // $this->assertArrayHasKey ('he', $details, 'Locale (he) not found.');
    // $this->assertArrayHasKey ($locale, $details, "Locale ($locale) not found.");

    // $this->assertArrayHasKey ('about_me', $details ['en-US'], 'about_me (en) not found.');
    // $this->assertArrayHasKey ('about_me', $details ['he'], 'about_me (he) not found.');
    // $this->assertArrayHasKey ('about_me', $details [$locale], "about_me ($locale) not found.");

    // $this->assertEquals ('Good Morning', $details['en-US']['about_me'], 'Updated DetailsString (en) not the same.');
    // $this->assertEquals ('בוקר טוב!', $details['he']['about_me'], 'Updated DetailsString (he) not the same.');
    // $this->assertEquals ($text, $details[$locale]['about_me'], "Updated DetailsString ($locale) not the same.");
    // }

    /**
     * Tests API_Profile->compare ()
     *
     * @dataProvider provideCompareProfiles
     */
    public function testCompare(string $firstGUID, string $secondGUID): void
    {
        $this->aPI_Profile = API_Profile::getBrief($firstGUID); // Set-up.

        // ----------------------------------------
        $compareResult = $this->aPI_Profile->compare($secondGUID);
        // ----------------------------------------

        $this->assertCompareProfiles($compareResult, $firstGUID, $secondGUID);
    }

    /**
     * Tests API_Profile::compare2 ()
     *
     * @dataProvider provideCompareProfiles
     */
    public function testCompare2(string $firstGUID, string $secondGUID): void
    {
        // ----------------------------------------
        $compareResult = API_Profile::compare2($firstGUID, $secondGUID);
        // ----------------------------------------

        $this->assertCompareProfiles($compareResult, $firstGUID, $secondGUID);
    }

    /**
     * Tests API_Profile->merge ()
     *
     * @dataProvider provideMergeSize
     */
    public function testMerge(int $mergeSize): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $clones = $profileFactory->makeClones($mergeSize);
        $first  = $clones[0];

        $merged = false;
        for ($ind = 1; $ind < count($clones); $ind++)
        {
            $cloneID = $clones[$ind]->getID();

            // ----------------------------------------
            $merged = $first->merge($cloneID);
            // ----------------------------------------

            if (!$merged)
            {
                break;
            }
        }

        $this->assertTrue($merged, 'Failed to merge profiles.');
    }

    /**
     * Tests API_Profile::merge2 ()
     *
     * @dataProvider provideMergeSize
     */
    public function testMerge2(int $mergeSize): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $clones  = $profileFactory->makeClones($mergeSize);
        $first   = $clones[0];
        $firstID = $first->getID();

        $merged = false;
        for ($ind = 1; $ind < count($clones); $ind++)
        {
            $cloneID = $clones[$ind]->getID();

            // ----------------------------------------
            $merged = API_Profile::merge2($firstID, $cloneID);
            // ----------------------------------------

            if (!$merged)
            {
                break;
            }
        }

        $this->assertTrue($merged, 'Failed to merge2 profiles.');
    }

    /**
     * Tests API_Profile->delete ()
     *
     * @dataProvider provideMergeSize
     */
    public function testDelete(int $count): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $deletes = $profileFactory->makeProfiles($count);

        $failCount = 0;
        foreach ($deletes as $del)
        {
            $delID = $del->getID();

            // ----------------------------------------
            $deleted = $del->delete();
            // ----------------------------------------

            if (!$deleted)
            {
                $failCount++;
            }

            $profileFactory->logDelete($delID);
        }

        $this->assertEquals(0, $failCount, 'Failed to delete profiles.');
    }

    /**
     * Tests API_Profile->follow ()
     *
     * @dataProvider provideRunCount
     */
    public function testFollow(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        // ----------------------------------------
        $following = $this->aPI_Profile->follow();
        // ----------------------------------------

        $this->assertTrue($following, 'Failed to follow profile.');
    }

    /**
     * Tests API_Profile->unfollow ()
     *
     * @dataProvider provideRunCount
     */
    public function testUnfollow(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $following         = $this->aPI_Profile->follow();

        $this->assertTrue($following, 'Ooops. Test set-up failed. Not following.');

        // ----------------------------------------
        $unfollowing = $this->aPI_Profile->unfollow();
        // ----------------------------------------

        $this->assertTrue($unfollowing, 'Failed to unfollow profile.');
    }

    /**
     * Tests API_Profile->getPathTo ()
     *
     * @dataProvider providePathTo
     * @param string $startPathGUID
     * @param string $endPathGUID
     * @param API_Path $expectedPath
     */
    public function testGetPathTo(string $startPathGUID, string $endPathGUID, API_Path $expectedPath): void
    {
        $this->aPI_Profile = API_Profile::getBrief($startPathGUID); // Set-up.

        // --------------------------------------------------
        $actualPath = $this->aPI_Profile->getPathTo($endPathGUID, refresh: true);
        // --------------------------------------------------

        $this->assertEquals($expectedPath, $actualPath);
    }

    /**
     * Tests API_Profile->getDocuments ()
     *
     * @dataProvider provideMediaCount
     */
    public function testGetDocuments(int $count): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryDocuments $documentFactory */
        $documentFactory = FactoryBase::getFactory(API_Document::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();

        // Create documents and tag them with the profile.
        // This should cause the profile to have the documents listed.

        $testDocIDs = [];
        $testDocs   = [];

        for ($i = 0; $i < $count; $i++)
        {
            $doc = $documentFactory->makeRandomDocument();
            $doc->tag($profileID);

            $testDocIDs[] = $doc->getID();
            $testDocs[]   = $doc;
        }

        // This should return the list of documents created above.

        // --------------------------------------------------
        $docList = $this->aPI_Profile->getDocuments(getAll: false, IDsOnly: false);
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\API_Document> $actualDocs */
        $actualDocs   = $docList->getList();
        $actualDocIDs = [];

        foreach ($actualDocs as $doc)
        {
            $doc->getTags();

            $actualDocIDs[] = $doc->getID();
        }

        $this->assertTagList($testDocs, $testDocIDs, $actualDocs, $actualDocIDs);
    }

    /**
     * Tests API_Profile->getDocuments ()
     *
     * @dataProvider provideMediaLargeCount
     */
    public function testGetDocuments_GetAll(int $count): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryDocuments $documentFactory */
        $documentFactory = FactoryBase::getFactory(API_Document::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();

        // Create documents and tag them.
        $testDocIDs = [];
        $testDocs   = [];

        for ($i = 0; $i < $count; $i++)
        {
            $doc = $documentFactory->makeRandomDocument();
            $doc->tag($profileID);

            $testDocIDs[] = $doc->getID();
            $testDocs[]   = $doc;
        }

        // --------------------------------------------------
        $docList = $this->aPI_Profile->getDocuments(getAll: true, IDsOnly: false);
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\API_Document> $actualDocs */
        $actualDocs   = $docList->getList();
        $actualDocIDs = [];

        foreach ($actualDocs as $doc)
        {
            $doc->getTags();

            $actualDocIDs[] = $doc->getID();
        }

        $this->assertTagList($testDocs, $testDocIDs, $actualDocs, $actualDocIDs);
    }

    /**
     * Tests API_Profile->getPhotos ()
     *
     * @dataProvider provideMediaCount
     */
    public function testGetPhotos(int $count): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();

        // Create photos and tag them.
        $testPhotoIDs = [];
        $testPhotos   = [];

        for ($i = 0; $i < $count; $i++)
        {
            $photo = $photoFactory->makeRandomPhoto();
            $photo->tag($profileID);

            $testPhotoIDs[] = $photo->getID();
            $testPhotos[]   = $photo;
        }

        // --------------------------------------------------
        $photoList = $this->aPI_Profile->getPhotos(getAll: false, IDsOnly: false);
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\API_Photo> $actualPhotos */
        $actualPhotos   = $photoList->getList();
        $actualPhotoIDs = [];

        foreach ($actualPhotos as $photo)
        {
            $photo->getTags();

            $actualPhotoIDs[] = $photo->getID();
        }

        $this->assertTagList($testPhotos, $testPhotoIDs, $actualPhotos, $actualPhotoIDs);
    }

    /**
     * Tests API_Profile->getPhotos ()
     *
     * @dataProvider provideMediaLargeCount
     */
    public function testGetPhotos_GetAll(int $count): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();

        // Create photos and tag them.
        $testPhotoIDs = [];
        $testPhotos   = [];

        for ($i = 0; $i < $count; $i++)
        {
            $photo = $photoFactory->makeRandomPhoto();

            if ($photo != null)
            {
                $photo->tag($profileID);

                $testPhotoIDs[] = $photo->getID();
                $testPhotos[]   = $photo;
            }
            else
            {
                $i--;
            }
        }

        // --------------------------------------------------
        $photoList = $this->aPI_Profile->getPhotos(getAll: true, IDsOnly: false);
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\API_Photo> $actualPhotos */
        $actualPhotos   = $photoList->getList();
        $actualPhotoIDs = [];

        foreach ($actualPhotos as $photo)
        {
            $photo->getTags();

            $actualPhotoIDs[] = $photo->getID();
        }

        $this->assertTagList($testPhotos, $testPhotoIDs, $actualPhotos, $actualPhotoIDs);
    }

    /**
     * Tests API_Profile->getVideos ()
     *
     * @dataProvider provideMediaCount
     */
    public function testGetVideos(int $count): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryVideos $videoFactory */
        $videoFactory = FactoryBase::getFactory(API_Video::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();

        // Create videos and tag them.
        $testVideoIDs = [];
        $testVideos   = [];

        for ($i = 0; $i < $count; $i++)
        {
            $video = $videoFactory->makeRandomVideo();
            $video->tag($profileID);

            $testVideoIDs[] = $video->getID();
            $testVideos[]   = $video;
        }

        // --------------------------------------------------
        $videoList = $this->aPI_Profile->getVideos(getAll: false, IDsOnly: false);
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\API_Video> $actualVideos */
        $actualVideos   = $videoList->getList();
        $actualVideoIDs = [];

        foreach ($actualVideos as $video)
        {
            $video->getTags();

            $actualVideoIDs[] = $video->getID();
        }

        $this->assertTagList($testVideos, $testVideoIDs, $actualVideos, $actualVideoIDs);
    }

    /**
     * Tests API_Profile->getVideos ()
     *
     * @dataProvider provideMediaLargeCount
     */
    public function testGetVideos_GetAll(int $count): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryVideos $videoFactory */
        $videoFactory = FactoryBase::getFactory(API_Video::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();

        // Create videos and tag them.
        $testVideoIDs = [];
        $testVideos   = [];

        for ($i = 0; $i < $count; $i++)
        {
            $video = $videoFactory->makeRandomVideo();
            $video->tag($profileID);

            $testVideoIDs[] = $video->getID();
            $testVideos[]   = $video;
        }

        // --------------------------------------------------
        $videoList = $this->aPI_Profile->getVideos(getAll: true, IDsOnly: false);
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\API_Video> $actualVideos */
        $actualVideos   = $videoList->getList();
        $actualVideoIDs = [];

        foreach ($actualVideos as $video)
        {
            $video->getTags();

            $actualVideoIDs[] = $video->getID();
        }

        $this->assertTagList($testVideos, $testVideoIDs, $actualVideos, $actualVideoIDs);
    }

    //     /**
    //      * Tests API_Profile::search ()
    //      *
    //      * @dataProvider provideSearchNames
    //      */
    //     public function testSearch (string $name, bool $exists, int $estimatedCount, bool $getAll, bool $IDsOnly): void
    //     {
    //         // ----------------------------------------
    //         $searchResults = API_Profile::search($name, $getAll, $IDsOnly);
    //         // ----------------------------------------

    //         $searchCount = count($searchResults);
    //         $totalCount  = $searchResults->getTotalCount();

    //         if ($exists)
    //         {
    //             // We expect to find results.
    //             $this->assertTrue ($searchCount > 0, "No expected search results found.");
    //             $this->assertEqualsWithDelta ($estimatedCount, $totalCount, 10, 'Number of results found not near expected');
    //         }
    //         else
    //         {
    //             $this->assertTrue($totalCount == 0, "Expected search to NOT find results.");
    //         }

    //         $this->assertTrue ($getAll  == $searchResults->isGetAll(),  'getAll mismatch');
    //         $this->assertTrue ($IDsOnly == $searchResults->isIDsOnly(), 'IDs-only mismatch');

    //         if ($getAll)
    //         {
    //             $expectedPages = ceil ($estimatedCount / API_ListBase::SIZE_OF_BATCH);
    //             $actualPages   = ceil ($searchCount    / API_ListBase::SIZE_OF_BATCH);

    //             $this->assertEqualsWithDelta ($expectedPages, $actualPages, 2, 'Wrong number of results pages');
    //         }
    //     }

    /**
     * =====================================================
         */

    /**
     * Tests API_Profile::convertID ()
     *
     * @dataProvider provideRunCount
     */
    public function testConvertID_ToGUID(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();
        $profileGUID       = $this->aPI_Profile->getGUID();

        // ----------------------------------------
        $resultsGUID = API_Profile::convertID($profileID, isGUID: false);
        // ----------------------------------------

        $this->assertEquals($profileGUID, $resultsGUID, 'Converted ID not the same.');
    }

    /**
     * Tests API_Profile::convertID ()
     *
     * @dataProvider provideRunCount
     */
    public function testConvertGUID_ToID(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();
        $profileGUID       = $this->aPI_Profile->getGUID();

        // ----------------------------------------
        $resultsID = API_Profile::convertID($profileGUID, isGUID: true);
        // ----------------------------------------

        $this->assertEquals($profileID, $resultsID, 'Converted ID not the same.');
    }

    /**
     * Tests API_Profile::convertListIDs ()
     */
    public function testConvertListIDs_Empty(): void
    {
        // ----------------------------------------
        $list = API_Profile::convertListIDs([]);
        // ----------------------------------------

        $this->assertEmpty($list, 'Empty list');
    }

    /**
     * Tests API_Profile::convertListIDs ()
     *
     * @dataProvider provideSetSize
     */
    public function testConvertListIDs_ToGUIDs(int $setSize): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $convertProfiles = $profileFactory->makeProfiles($setSize);
        $convertIDs      = $profileFactory->makeArrayIDs($convertProfiles, getID: true);
        $expectedGUIDs   = $profileFactory->makeArrayIDs($convertProfiles, getID: false);

        // ----------------------------------------
        $resultsGUID = API_Profile::convertListIDs($convertIDs, isGUID: false);
        // ----------------------------------------

        $this->assertEquals($expectedGUIDs, $resultsGUID, 'Converted lists not the same.');
    }

    /**
     * Tests API_Profile::convertListIDs ()
     *
     * @dataProvider provideSetSize
     */
    public function testConvertListIDs_ToIDs(int $setSize): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        // Create array of profiles. Get from them two arrays of ID and GUID values.
        $convertProfiles = $profileFactory->makeProfiles($setSize);
        $convertGUIDs    = $profileFactory->makeArrayIDs($convertProfiles, getID: false);
        $expectedIDs     = $profileFactory->makeArrayIDs($convertProfiles, getID: true);

        // ----------------------------------------
        $resultIDs = API_Profile::convertListIDs($convertGUIDs, isGUID: true);
        // ----------------------------------------

        $this->assertEquals($expectedIDs, $resultIDs, 'Lists not the same.');
    }

    /**
     * Tests API_Profile->getProjects ()
     *
     * @dataProvider provideProfilesHaveProject
     */
    public function testGetProjects_HaveProj(string $ID): void
    {
        $this->aPI_Profile = API_Profile::get($ID); // Set-up.

        // ----------------------------------------
        $Projects = $this->aPI_Profile->getProjects();
        // ----------------------------------------

        $numProjects = count($Projects);

        $this->assertGreaterThan(0, $numProjects, 'There are NO projects.');
    }

    /**
     * Tests API_Profile->getProjects ()
     *
     * @dataProvider provideRunCount
     */
    public function testGetProjects_NoProj(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        // ----------------------------------------
        $Projects = $this->aPI_Profile->getProjects();
        // ----------------------------------------

        $numProjects = count($Projects);

        $this->assertEquals(0, $numProjects, 'There are projects.');
    }

    // ======================================================

    /**
     * Tests API_Profile->isBrief ()
     *
     * @dataProvider provideRunCount
     */
    public function testIsBriefTrue(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileID         = $this->aPI_Profile->getID();

        $checker = API_Profile::getBrief($profileID); // Set-up.

        // ----------------------------------------
        $flag = $checker->isBrief();
        // ----------------------------------------

        $this->assertEquals(true, $flag);
    }

    /**
     *
     * @dataProvider provideRunCount
     */
    public function testIsBriefFalse(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();
        $profileGUID       = $this->aPI_Profile->getGUID();

        $checker = API_Profile::get($profileGUID); // Set-up.

        // ----------------------------------------
        $flag = $checker->isBrief();
        // ----------------------------------------

        $this->assertEquals(false, $flag);
    }

    // ======================================================
    // Test getter functions.

    /**
     * Tests API_Profile->getAboutMe ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetAboutMe(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $getAboutMe = $this->aPI_Profile->getAboutMe();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'about_me', $getAboutMe);
    }

    /**
     * Use brief object to test separate API call.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetAboutMe_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $getAboutMe = $this->aPI_Profile->getAboutMe();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'about_me', $getAboutMe);
    }

    /**
     * Test 'force' parameter.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetAboutMe_Force(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // This clears the field, but does NOT change the server. This enables
        // us to verify that the 'forced' get worked.

        $this->aPI_Profile->setAboutMe('');

        // ------------------------------
        $getAboutMe = $this->aPI_Profile->getAboutMe(force: true);
        // ------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'about_me', $getAboutMe);
    }

    /**
     * Tests API_Profile->getCauseOfDeath ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCauseOfDeath(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCauseOfDeath();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'cause_of_death', $value);
    }

    /**
     * Tests API_Profile->getCauseOfDeath ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCauseOfDeath_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCauseOfDeath();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'cause_of_death', $value);
    }

    /**
     * Tests API_Profile->getCreatedAt ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCreatedAt(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCreatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'created_at', $value);
    }

    /**
     * Tests API_Profile->getCreatedAt ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCreatedAt_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCreatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'created_at', $value);
    }

    /**
     * Tests API_Profile->getCreatedBy ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCreatedBy(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCreatedBy();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'created_by', $value);
    }

    /**
     * Tests API_Profile->getCreatedBy ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCreatedBy_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCreatedBy();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'created_by', $value);
    }

    /**
     * Tests API_Profile->getCreatedBy ()
     *
     * @dataProvider provideRunCount
     */
    public function testGetCreatedBy_Self(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        // Get ID of whoever is running the script.

        $creator   = API_Profile::getProfileSelf(brief: true); // Set-up.
        $creatorID = $creator->getID();

        // Create a profile.

        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        // ----------------------------------------
        $createdBy = $this->aPI_Profile->getCreatedBy();
        // ----------------------------------------

        $this->assertEquals($creatorID, $createdBy);
    }

    // /**
    // * Tests API_Profile->getCreator ()
    // *
    // * @dataProvider provideFullProfile
    // * @param string $id
    // * @param string $guid
    // * @param array <string, mixed> $profileTestArray
    // */
    // public function testGetCreator (string $id, string $guid, array $profileTestArray): void
    // {
    // $this->aPI_Profile = API_Profile::get ($guid); // Set-up.

    // //----------------------------------------
    // $value = $this->aPI_Profile->getCreator ();
    // //----------------------------------------

    // $this->assertTestFieldEquals ($profileTestArray, 'creator', $value);
    // }

    // /**
    // * Tests API_Profile->getCreator ()
    // * Use getBrief, to ensure that API is called.
    // *
    // * @dataProvider provideFullProfile
    // * @param string $id
    // * @param string $guid
    // * @param array <string, mixed> $profileTestArray
    // */
    // public function testGetCreator_Call (string $id, string $guid, array $profileTestArray): void
    // {
    // $this->aPI_Profile = API_Profile::getBrief ($guid); // Set-up.

    // //----------------------------------------
    // $value = $this->aPI_Profile->getCreator ();
    // //----------------------------------------

    // $this->assertTestFieldEquals ($profileTestArray, 'creator', $value);
    // }

    // /**
    // * Tests API_Profile->getCreator ()
    // *
    // * @dataProvider provideRunCount
    // */
    // public function testGetCreator_Self (int $ignore): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);

    // $this->aPI_Profile = $profileFactory->makeRandomProfile();
    // $creator = API_Profile::getProfileSelf (brief: TRUE); // Set-up.
    // $creatorID = $creator->getID();

    // //----------------------------------------
    // $value = $this->aPI_Profile->getCreator ();
    // //----------------------------------------

    // $this->assertEquals ($creatorID, $value);
    // }

    /**
     * Tests API_Profile->getCurator ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCurator(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCurator();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'curator', $value);
    }

    /**
     * Tests API_Profile->getCurator ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCurator_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCurator();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'curator', $value);
    }

    /**
     * Tests API_Profile->getDocumentsUpdatedAt ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetDocumentsUpdatedAt(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getDocumentsUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'documents_updated_at', $value);
    }

    /**
     * Tests API_Profile->getDocumentsUpdatedAt ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetDocumentsUpdatedAt_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getDocumentsUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'documents_updated_at', $value);
    }

    /**
     * Tests API_Profile->getEmail ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetEmail(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getEmail();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'email', $value);
    }

    /**
     * Tests API_Profile->getEmail ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetEmail_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getEmail();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'email', $value);
    }

    /**
     * Tests API_Profile->getGender ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetGender(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $gender = $this->aPI_Profile->getGender();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'gender', $gender->value);
    }

    /**
     * Tests API_Profile->getGender ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetGender_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $gender = $this->aPI_Profile->getGender();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'gender', $gender->value);
    }

    /**
     * Tests API_Profile->getGUID ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetGUID(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getGUID();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'guid', $value);
    }

    /**
     * Tests API_Profile->getID ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetID(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getID();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'id', $value);
    }

    /**
     * Tests API_Profile->getLanguage ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetLanguage(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getLanguage();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'language', $value);
    }

    /**
     * Tests API_Profile->getLanguage ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetLanguage_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getLanguage();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'language', $value);
    }

    /**
     * Tests API_Profile->getMergedInto ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetMergedInto(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getMergedInto();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'merged_into', $value);
    }

    /**
     * Tests API_Profile->getMergedInto ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetMergedInto_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getMergedInto();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'merged_into', $value);
    }

    /**
     * Tests API_Profile->getOccupation ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetOccupation(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getOccupation();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'occupation', $value);
    }

    /**
     * Tests API_Profile->getOccupation ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetOccupation_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getOccupation();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'occupation', $value);
    }

    /**
     * Tests API_Profile->getPhotosUpdatedAt ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetPhotosUpdatedAt(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPhotosUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'photos_updated_at', $value);
    }

    /**
     * Tests API_Profile->getPhotosUpdatedAt ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetPhotosUpdatedAt_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPhotosUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'photos_updated_at', $value);
    }

    /**
     * Tests API_Profile->getPremiumStartDate ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetPremiumStartDate(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPremiumStartDate();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'premium_start_date', $value);
    }

    /**
     * Tests API_Profile->getPremiumStartDate ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetPremiumStartDate_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPremiumStartDate();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'premium_start_date', $value);
    }

    /**
     * Tests API_Profile->getPremiumStartDate ()
     */
    public function testGetPremiumStartDate_User(): void
    {
        $this->aPI_Profile = API_Profile::getProfileSelf(brief: false); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPremiumStartDate();
        // ----------------------------------------

        $this->assertTestFieldEquals(TestProfiles::$Myself, 'premium_start_date', $value);
    }

    /**
     * Tests API_Profile->getPremiumStartDate ()
     * Use getBrief, to ensure that API is called.
     */
    public function testGetPremiumStartDate_UserCall(): void
    {
        $this->aPI_Profile = API_Profile::getProfileSelf(brief: true); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPremiumStartDate();
        // ----------------------------------------

        $this->assertTestFieldEquals(TestProfiles::$Myself, 'premium_start_date', $value);
    }

    /**
     * Tests API_Profile->getProfileURL ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetProfileURL(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getProfileURL();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'profile_url', $value);
    }

    /**
     * Tests API_Profile->getProfileURL ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetProfileURL_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getProfileURL();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'profile_url', $value);
    }

    /**
     * Tests API_Profile->getRelationship ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetRelationship(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getRelationship();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'relationship', $value);
    }

    /**
     * Tests API_Profile->getRelationship ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetRelationship_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getRelationship();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'relationship', $value);
    }

    /**
     * Tests API_Profile->getUpdatedAt ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetUpdatedAt(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'updated_at', $value);
    }

    /**
     * Tests API_Profile->getUpdatedAt ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetUpdatedAt_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'updated_at', $value);
    }

    /**
     * Tests API_Profile->getURL ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetURL(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getURL();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'url', $value);
    }

    /**
     * Tests API_Profile->getURL ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetURL_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getURL();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'url', $value);
    }

    /**
     * Tests API_Profile->getVideosUpdatedAt ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetVideosUpdatedAt(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getVideosUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'videos_updated_at', $value);
    }

    /**
     * Tests API_Profile->getVideosUpdatedAt ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetVideosUpdatedAt_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getVideosUpdatedAt();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'videos_updated_at', $value);
    }

    /**
     * Tests API_Profile->getDetailStrings ()
     *
     * @dataProvider provideDetailStrings
     * @param string $name
     * @param string $guid
     * @param array <string, array <string>> $detail_strings
     */
    public function testGetDetailStrings(string $name, string $guid, array $detail_strings): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getDetailStrings();
        // ----------------------------------------

        $this->assertDetailStringsEquals($detail_strings, $value);
    }

    /**
     * Tests API_Profile->getDetailStrings ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideDetailStrings
     * @param string $name
     * @param string $guid
     * @param array <string, array <string>> $detail_strings
     */
    public function testGetDetailStrings_Call(string $name, string $guid, array $detail_strings): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getDetailStrings();
        // ----------------------------------------

        $this->assertDetailStringsEquals($detail_strings, $value);
    }

    // TODO figure out why this is in a comment and finish it.
    // /**
    //  * Tests API_Profile->getEvents ()
    //  *
    //  * @dataProvider provideLifeEvents
    //  * param array <string> $eventTypes
    //  */
    // public function testGetEvents (array $eventTypes): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);

    // // Set-up.
    // $this->aPI_Profile = $profileFactory->makeRandomProfile();
    // $profileID = $this->aPI_Profile->getID();
    // $profileGUID = $this->aPI_Profile->getGUID();

    // $tempEventList = $eventTypes; //XXX
    // sort ($tempEventList);
    // $testEventsList = implode(', ', $tempEventList); //XXX
    // echo "\r\nProfile ID: $profileID ($profileGUID).\r\n\r\n"; //XXX
    // echo "Test set Events: $testEventsList.\r\n"; //XXX

    // $personName = $this->aPI_Profile->getName();
    // $partnerName = implode (' ', $profileFactory->makeRandomName());

    // $testEvents = $profileFactory->makeSetBMD_Events ($personName, $partnerName, $eventTypes);

    // foreach ($testEvents as $eventName => $event)
    // {
    // switch ($eventName)
    // {
    // case 'Birth': $this->aPI_Profile->setBirth ($event);
    // break;

    // case 'Baptism': $this->aPI_Profile->setBaptism ($event);
    // break;

    // case 'Burial': $this->aPI_Profile->setBurial ($event);
    // break;

    // case 'Death': $this->aPI_Profile->setDeath ($event);
    // break;

    // default:
    // break;
    // }
    // }

    // $this->aPI_Profile->update();

    // $checkerProfile = API_Profile::get ($profileID);

    // //----------------------------------------
    // $allEvents = $checkerProfile->getEvents ();
    // //----------------------------------------

    // $allNamed = $this->assignEventNames ($allEvents);

    // $returnedEvents = array_keys($allNamed); //XXX
    // sort ($returnedEvents);
    // $testEventsList = implode(', ', $returnedEvents); //XXX
    // echo "Returned Events: $testEventsList\r\n\r\n"; //XXX
    // echo "Count: Created - " . count ($testEvents) . " Returned - " . count ($allEvents) . ".\r\n";
    // echo "=========================================\r\n\r\n";

    // $this->assertEquals (count ($testEvents), count ($allEvents));

    // foreach ($testEvents as $name => $expected)
    // {
    // $this->assertTrue (FactoryEvents::equalEvents ($expected, $allNamed [$name]));
    // }
    // }

    /**
     * Tests API_Profile->getManagers ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetManagers(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getManagers();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'managers', $value);
    }

    /**
     * Tests API_Profile->getManagers ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetManagers_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getManagers();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'managers', $value);
    }

    /**
     * Tests API_Profile->getMergeNote ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetMergeNote(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getMergeNote();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'merge_note', $value);
    }

    /**
     * Tests API_Profile->getMergeNote ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetMergeNote_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getMergeNote();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'merge_note', $value);
    }

    /**
     * Tests API_Profile->getPhoneNumbers ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetPhoneNumbers(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPhoneNumbers();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'phone_numbers', $value);
    }

    /**
     * Tests API_Profile->getPhoneNumbers ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetPhoneNumbers_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getPhoneNumbers();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'phone_numbers', $value);
    }

    /**
     * Tests API_Profile->getRequestedMerges ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetRequestedMerges(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getRequestedMerges();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'requested_merges', $value);
    }

    /**
     * Tests API_Profile->getRequestedMerges ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetRequestedMerges_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getRequestedMerges();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'requested_merges', $value);
    }

    /**
     * Tests API_Profile->getUnions ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetUnions(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getUnions();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'unions', $value);
    }

    /**
     * Tests API_Profile->getUnions ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetUnions_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getUnions();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'unions', $value);
    }

    /**
     * Tests API_Profile->getBaptism ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetBaptism(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getBaptism();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'baptism');
    }

    /**
     * Tests API_Profile->getBaptism ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetBaptism_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getBaptism();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'baptism');
    }

    /**
     * Tests API_Profile->getBirth ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetBirth(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getBirth();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'birth');
    }

    /**
     * Tests API_Profile->getBirth ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetBirth_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getBirth();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'birth');
    }

    /**
     * Tests API_Profile->getBurial ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetBurial(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getBurial();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'burial');
    }

    /**
     * Tests API_Profile->getBurial ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetBurial_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getBurial();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'burial');
    }

    /**
     * Tests API_Profile->getDeath ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetDeath(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getDeath();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'death');
    }

    /**
     * Tests API_Profile->getDeath ()
     * Use getBrief to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetDeath_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $event = $this->aPI_Profile->getDeath();
        // ----------------------------------------

        $this->assertEventTestdata($profileTestArray, $event, 'death');
    }

    /**
     * Tests API_Profile->getCurrentResidence ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCurrentResidence(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCurrentResidence();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'current_residence', $value);
    }

    /**
     * Tests API_Profile->getCurrentResidence ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetCurrentResidence_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getCurrentResidence();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'current_residence', $value);
    }

    /**
     * Tests API_Profile->getMugshotURLs ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetMugshotURLs(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $mugshotsMap = $this->aPI_Profile->getMugshotURLs();
        // ----------------------------------------

        $mugshots = $mugshotsMap->makeParamArray();

        $testMugsMap = $profileTestArray['values']['mugshot_urls'];
        $testMugs    = $testMugsMap->makeParamArray();

        $cleanTestMugs = $this->removeHashValues($testMugs);
        $cleanMugs     = $this->removeHashValues($mugshots);

        $this->assertEquals($cleanTestMugs, $cleanMugs);
    }

    /**
     * Tests API_Profile->getMugshotURLs ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetMugshotURLs_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($id); // Set-up.

        // ----------------------------------------
        $mugshotsMap = $this->aPI_Profile->getMugshotURLs();
        // ----------------------------------------

        $mugshots = $mugshotsMap->makeParamArray();

        $testMugsMap = $profileTestArray['values']['mugshot_urls'];
        $testMugs    = $testMugsMap->makeParamArray();

        $cleanTestMugs = $this->removeHashValues($testMugs);
        $cleanMugs     = $this->removeHashValues($mugshots);

        $this->assertEquals($cleanTestMugs, $cleanMugs);
    }

    // ======================================================

    /**
     * Tests API_Profile->getFullName ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetFullName(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $names = $this->aPI_Profile->getFullName();
        // ----------------------------------------

        $this->assertFullName($profileTestArray['values'], $names);
    }

    /**
     * Tests API_Profile->getFullName ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetFullName_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $names = $this->aPI_Profile->getFullName();
        // ----------------------------------------

        $this->assertFullName($profileTestArray['values'], $names);
    }

    /**
     * Tests API_Profile->getName ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetName(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getName();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'name', $value);
    }

    /**
     * Tests API_Profile->getName ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetName_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getName();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'name', $value);
    }

    /**
     * Tests API_Profile->getNames ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetNames(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        $allNames = $profileTestArray['values']['names'];

        foreach ($allNames as $languageID => $names)
        {
            // ----------------------------------------
            $langNames = $this->aPI_Profile->getNames($languageID);
            // ----------------------------------------

            $this->assertEquals($names, $langNames);
        }
    }

    /**
     * Tests API_Profile->getNames ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetNames_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($id); // Set-up.

        foreach ($profileTestArray['values']['names'] as $languageID => $names)
        {
            // ----------------------------------------
            $langNames = $this->aPI_Profile->getNames($languageID);
            // ----------------------------------------

            $this->assertEquals($names, $langNames);
        }
    }

    /**
     * Tests API_Profile->getNames ()
     * Test for language that is NOT in profile.
     */
    public function testGetNamesMissing(): void
    {
        // Get Adam.
        $this->aPI_Profile = API_Profile::get('6000000003538706117'); // Set-up.

        // ----------------------------------------
        $langNames = $this->aPI_Profile->getNames('zz');
        // ----------------------------------------

        $this->assertNull($langNames);
    }

    /**
     * Tests API_Profile->getNamesAll ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetNamesAll(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $names = $this->aPI_Profile->getNamesAll();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'names', $names);
    }

    /**
     * Tests API_Profile->getNamesAll ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetNamesAll_ForceImplied(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $names = $this->aPI_Profile->getNamesAll();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'names', $names);
    }

    /**
     * Tests API_Profile->getNamesAll ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetNamesAll_Force(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $names = $this->aPI_Profile->getNamesAll(force: true);
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'names', $names);
    }

    /**
     * Tests API_Profile->getNicknames ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetNicknames(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getNicknames();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'nicknames', $value);
    }

    /**
     * Tests API_Profile->getNicknames ()
     * Use getBrief, to ensure that API is called.
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testGetNicknames_Call(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::getBrief($guid); // Set-up.

        // ----------------------------------------
        $value = $this->aPI_Profile->getNicknames();
        // ----------------------------------------

        $this->assertTestFieldEquals($profileTestArray, 'nicknames', $value);
    }

    // ======================================================
    // Test get flag functions.

    /**
     * Tests API_Profile->isBig_tree ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testIsBig_tree(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isBig_tree();
        // ----------------------------------------

        $this->assertTestBoolSame($profileTestArray, 'big_tree', $flag);
    }

    /**
     * Tests API_Profile->isBig_tree ()
     *
     * @dataProvider provideSetProfile
     * @param string $targetID
     * @param string $targetGUID
     * @param string $targetName
     * @param array <string, mixed> $source
     */
    public function testIsBig_tree_False(string $targetID, string $targetGUID, string $targetName, array $source): void
    {
        $this->aPI_Profile = API_Profile::get($targetGUID); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isBig_tree();
        // ----------------------------------------

        $this->assertEquals(false, $flag);
    }

    /**
     * Tests API_Profile->isBlockExists ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testIsBlockExists(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isBlockExists();
        // ----------------------------------------

        $this->assertTestBoolSame($profileTestArray, 'block_exists', $flag);
    }

    /**
     * Tests API_Profile->isClaimed ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testIsClaimed(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isClaimed();
        // ----------------------------------------

        $this->assertTestBoolSame($profileTestArray, 'claimed', $flag);
    }

    /**
     * Tests API_Profile->isClaimed ()
     */
    public function testIsClaimed_True(): void
    {
        $this->aPI_Profile = API_Profile::getProfileSelf(brief: false); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isClaimed();
        // ----------------------------------------

        $this->assertEquals(true, $flag);
    }

    /**
    * Tests API_Profile->isDeleted ()
    *
    * @dataProvider provideRunCount
    */
    public function testIsDeleted(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('Ben', 'Iced', alive: true, public: true, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $flag = $this->aPI_Profile->isDeleted();
        // ----------------------------------------

        $this->assertEquals(false, $flag);

        $this->aPI_Profile->delete();

        $checkerProfile = API_Profile::get($profileID);

        // ----------------------------------------
        $flag = $checkerProfile->isDeleted();
        // ----------------------------------------

        $profileFactory->logDelete($profileID);

        $this->assertEquals(true, $flag);
    }

    /**
     * Tests API_Profile->isGet_email ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testIsGet_email(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isGet_email();
        // ----------------------------------------

        $this->assertTestBoolSame($profileTestArray, 'get_email', $flag);
    }

    /**
     * Tests API_Profile->isAlive ()
     *
     * @dataProvider provideRunCount
     */
    public function testIsAlive(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $alive             = true;
        $this->aPI_Profile = $profileFactory->makeTestProfile('Ben', 'Iced', alive: $alive, public: true, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        $checker = API_Profile::get($profileID);

        // ----------------------------------------
        $flag = $checker->isAlive();
        // ----------------------------------------

        $this->assertEquals($alive, $flag);
    }

    /**
     * Tests API_Profile->isAlive ()
     *
     * @dataProvider provideRunCount
     */
    public function testIsAlive_False(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $alive             = false;
        $this->aPI_Profile = $profileFactory->makeTestProfile('Ben', 'Iced', alive: $alive, public: true, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        $checker = API_Profile::get($profileID);

        // ----------------------------------------
        $flag = $checker->isAlive();
        // ----------------------------------------

        $this->assertEquals($alive, $flag);
    }

    /**
     * Tests API_Profile->isLocked ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testIsLocked(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isLocked();
        // ----------------------------------------

        $this->assertTestBoolSame($profileTestArray, 'locked', $flag);
    }

    /**
     * Tests API_Profile->isLocked ()
     *
     * @dataProvider provideRunCount
     */
    public function testIsLocked_False(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isLocked();
        // ----------------------------------------

        $this->assertEquals(false, $flag);
    }

    /**
     * Tests API_Profile->isMasterProfile ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testIsMasterProfile(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isMasterProfile();
        // ----------------------------------------

        $this->assertTestBoolSame($profileTestArray, 'master_profile', $flag);
    }

    /**
     * Tests API_Profile->isMasterProfile ()
     *
     * @dataProvider provideRunCount
     */
    public function testIsMasterProfile_False(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isMasterProfile();
        // ----------------------------------------

        $this->assertEquals(false, $flag);
    }

    /**
     * Tests API_Profile->isMergePending ()
     *
     * @dataProvider provideFullProfile
     * @param string $id
     * @param string $guid
     * @param array <string, mixed> $profileTestArray
     */
    public function testIsMergePending(string $id, string $guid, array $profileTestArray): void
    {
        $this->aPI_Profile = API_Profile::get($guid); // Set-up.

        // ----------------------------------------
        $flag = $this->aPI_Profile->isMergePending();
        // ----------------------------------------

        $this->assertTestBoolSame($profileTestArray, 'merge_pending', $flag);
    }

    /**
     * Tests API_Profile->isPublic ()
     *
     * @dataProvider provideRunCount
     */
    public function testIsPublic(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('Ben', 'Iced', alive: false, public: true, gender: API_Gender::Male);

        $profileID = $this->aPI_Profile->getID();

        $checkerProfile = API_Profile::get($profileID);

        // ----------------------------------------
        $public = $checkerProfile->isPublic();
        // ----------------------------------------

        $this->assertEquals(true, $public);
    }

    /**
     * Tests API_Profile->isPublic ()
     *
     * @dataProvider provideRunCount
     */
    public function testIsPublic_False(int $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('Ben', 'Iced', alive: false, public: false, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        $checkerProfile = API_Profile::get($profileID);

        // ----------------------------------------
        $public = $checkerProfile->isPublic();
        // ----------------------------------------

        $this->assertEquals(false, $public);
    }

    // ======================================================
    // Test setter functions.
    // These tests do NOT change the server values. That would require
    // calling the API_Profile::update () function. Instead they check
    // the API_Profile object using Reflection (see Reflectrovitch class).
    // See the API_ProfileTestUpdate class for tests that change the
    // server objects.

    /**
     * Tests API_Profile->setAboutMe ()
     *
     * @dataProvider provideSetProfile
     * @param string $targetID
     * @param string $targetGUID
     * @param string $targetName
     * @param array <string, mixed> $source
     */
    public function testSetAboutMe(string $targetID, string $targetGUID, string $targetName, array $source): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        $inputValue = $source['values']['about_me'];

        // ----------------------------------------
        $this->aPI_Profile->setAboutMe($inputValue);
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'about_me', $inputValue, nullAllowed: false);
    }

    /**
     * Tests API_Profile->setAboutMe ()
     *
     * @dataProvider provideSetProfile
     * @param string $targetID
     * @param string $targetGUID
     * @param string $targetName
     * @param array <string, mixed> $source
     */
    public function testSetAboutMeBlank(string $targetID, string $targetGUID, string $targetName, array $source): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        // Make copy in order to not change the GLOBAL variable.
        $this->aPI_Profile = $profileFactory->makeCloneFromID($targetID, true); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setAboutMe('');
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'about_me', '', nullAllowed: false);
    }

    /**
     * Tests API_Profile->setCauseOfDeath ()
     *
     * @dataProvider provideSetData
     * @param array <string, string> $values
     */
    public function testSetCauseOfDeath(array $values): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setCauseOfDeath($values['cause_of_death']);
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'cause_of_death', $values['cause_of_death'], nullAllowed: false);
    }

    /**
     * Tests API_Profile->setCauseOfDeath ()
     *
     * @dataProvider provideSetData
     * @param array <string, string> $values
     */
    public function testSetCauseOfDeathBlank($values): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setCauseOfDeath('');
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'cause_of_death', '', nullAllowed: false);
    }

    /**
     * Tests API_Profile->setEmail ()
     *
     * @dataProvider provideSetData
     * @param array <string, string> $values
     */
    public function testSetEmail($values): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setEmail($values['email']);
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'email', $values['email'], nullAllowed: false);
    }

    /**
     * Tests API_Profile->setGender ()
     *
     * @dataProvider provideSetGender
     * @param API_Gender $fromGender
     * @param API_Gender $toGender
     */
    public function testSetGender(API_Gender $fromGender, API_Gender $toGender): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        list($firstName, $lastName) = $profileFactory->makeRandomName($fromGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, false, true, $fromGender);

        // ----------------------------------------
        $this->aPI_Profile->setGender($toGender);
        // ----------------------------------------

        $setGender = $this->aPI_Profile->getGender();

        $this->assertEquals($toGender->value, $setGender->value);
    }

    /**
     * Tests API_Profile->setIsAlive ()
     *
     * @dataProvider provideSetFlag
     */
    public function testSetIsAlive(bool $isPublic, bool $fromFlag, bool $toFlag, bool $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $randomGender               = $profileFactory->makeRandomGender();
        list($firstName, $lastName) = $profileFactory->makeRandomName($randomGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $fromFlag, $isPublic, $randomGender); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setIsAlive($toFlag);
        // ----------------------------------------

        $isAlive = $this->aPI_Profile->isAlive();

        $this->assertEquals($toFlag, $isAlive);

        $this->assertValuesBoolEqual($this->aPI_Profile, 'is_alive', $toFlag);
    }

    /**
     * Tests API_Profile->setLocked ()
     *
     * @dataProvider provideSetFlag
     */
    public function testSetLocked(bool $isPublic, bool $fromFlag, bool $toFlag, bool $ignore): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $randomGender               = $profileFactory->makeRandomGender();
        list($firstName, $lastName) = $profileFactory->makeRandomName($randomGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $fromFlag, $isPublic, $randomGender); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setLocked($toFlag);
        // ----------------------------------------

        $isLocked = $this->aPI_Profile->isLocked();

        $this->assertEquals($toFlag, $isLocked);

        $this->assertValuesBoolEqual($this->aPI_Profile, 'locked', $toFlag);
    }

    // /**
    // * Tests API_Profile->setMergeNote ()
    // *
    // * @dataProvider provideSetData
    // * @param array <string, string> $values
    // */
    // public function testSetMergeNote ($values): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);

    // $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

    // //----------------------------------------
    // $this->aPI_Profile->setMergeNote ('');
    // //----------------------------------------

    // $this->assertValuesFieldEqual ($this->aPI_Profile, 'merge_note', '', nullAllowed: TRUE);
    // }

    // /**
    // * Tests API_Profile->setMergeNote ()
    // *
    // * @dataProvider provideSetData
    // * @param array <string, string> $values
    // */
    // public function testSetMergeNoteBlank ($values): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);

    // $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

    // //----------------------------------------
    // $this->aPI_Profile->setMergeNote ('');
    // //----------------------------------------

    // $this->assertValuesFieldEqual ($this->aPI_Profile, 'merge_note', '', nullAllowed: TRUE);
    // }

    /**
     * Tests API_Profile->setOccupation ()
     *
     * @dataProvider provideSetData
     * @param array <string, string> $values
     */
    public function testSetOccupation($values): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setOccupation($values['occupation']);
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'occupation', $values['occupation'], nullAllowed: true);
    }

    /**
     * Tests API_Profile->setOccupation ()
     *
     * @dataProvider provideSetData
     * @param array <string, string> $values
     */
    public function testSetOccupationBlank($values): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setOccupation('');
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'occupation', '', nullAllowed: true);
    }

    /**
     * Tests API_Profile->setPublic ()
     *
     * @dataProvider provideSetFlag
     */
    public function testSetPublic(bool $isAlive, bool $fromFlag, bool $toFlag, bool $expected): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $randomGender               = $profileFactory->makeRandomGender();
        list($firstName, $lastName) = $profileFactory->makeRandomName($randomGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $isAlive, $fromFlag, $randomGender); // Set-up.

        // ----------------------------------------
        $this->aPI_Profile->setPublic($toFlag);
        // ----------------------------------------

        $isPublic = $this->aPI_Profile->isPublic();

        $this->assertEquals($toFlag, $isPublic);

        $this->assertValuesBoolEqual($this->aPI_Profile, 'public', $toFlag);
    }

    /**
     * Tests API_Profile->setBaptism ()
     *
     * @dataProvider provideSetFlag
     */
    public function testSetBaptism(bool $ignore, bool $alive, bool $public, bool $ignore2): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $randomGender               = $profileFactory->makeRandomGender();
        list($firstName, $lastName) = $profileFactory->makeRandomName($randomGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $alive, $public, $randomGender);

        $baptismEvent = $eventFactory->makeBaptismEvent("$firstName $lastName", 'Dunked.');

        // ----------------------------------------
        $this->aPI_Profile->setBaptism($baptismEvent);
        // ----------------------------------------

        $setEvent = $this->aPI_Profile->getBaptism();

        $this->assertEvents_Other($baptismEvent, $setEvent, 'Baptism');
    }

    /**
     * Tests API_Profile->setBirth ()
     *
     * @dataProvider provideSetFlag
     */
    public function testSetBirth(bool $ignore, bool $alive, bool $public, bool $ignore2): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $randomGender               = $profileFactory->makeRandomGender();
        list($firstName, $lastName) = $profileFactory->makeRandomName($randomGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $alive, $public, $randomGender);

        $birthEvent = $eventFactory->makeBirthEvent("$firstName $lastName", 'Yikes.');

        // ----------------------------------------
        $this->aPI_Profile->setBirth($birthEvent);
        // ----------------------------------------

        $setEvent = $this->aPI_Profile->getBirth();

        $this->assertEvents_Other($birthEvent, $setEvent, 'Birth');
    }

    /**
     * Tests API_Profile->setBurial ()
     *
     * @dataProvider provideSetFlag
     */
    public function testSetBurial(bool $ignore, bool $alive, bool $public, bool $ignore2): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $randomGender               = $profileFactory->makeRandomGender();
        list($firstName, $lastName) = $profileFactory->makeRandomName($randomGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $alive, $public, $randomGender);

        $burialEvent = $eventFactory->makeBurialEvent("$firstName $lastName", 'Yikes.');

        // ----------------------------------------
        $this->aPI_Profile->setBurial($burialEvent);
        // ----------------------------------------

        $setEvent = $this->aPI_Profile->getBurial();

        $this->assertEvents_Other($burialEvent, $setEvent, 'Burial');
    }

    /**
     * Tests API_Profile->setDeath ()
     *
     * @dataProvider provideSetFlag
     */
    public function testSetDeath(bool $ignore, bool $alive, bool $public, bool $ignore2): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $randomGender               = $profileFactory->makeRandomGender();
        list($firstName, $lastName) = $profileFactory->makeRandomName($randomGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, $alive, $public, $randomGender);

        $deathEvent = $eventFactory->makeDeathEvent("$firstName $lastName", 'Yikes.');

        // ----------------------------------------
        $this->aPI_Profile->setDeath($deathEvent);
        // ----------------------------------------

        $setEvent = $this->aPI_Profile->getDeath();

        $this->assertEvents_Other($deathEvent, $setEvent, 'Death');
    }

    /**
     * Tests API_Profile->setCurrentResidence ()
     *
     * @dataProvider provideCurrentResidence
     * @param API_Location $location
     */
    public function testSetCurrentResidence(API_Location $location): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('Who', 'Dunnit', alive: true, public: true, gender: API_Gender::Male);

        // ----------------------------------------
        $this->aPI_Profile->setCurrentResidence($location);
        // ----------------------------------------

        $setLocation = Reflectrovitch::getValuesFieldObj($this->aPI_Profile, 'current_residence', API_Location::class);

        $this->assertEquals($location, $setLocation);
    }

    /**
     * Tests API_Profile->setFullName ()
     *
     * @dataProvider provideSetNames
     * @param array <string, mixed> $names
     */
    public function testSetFullName(array $names): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        // ----------------------------------------
        $this->aPI_Profile->setFullName(
            $names['first_name'],
            $names['middle_name'],
            $names['last_name'],
            $names['maiden_name'],
            $names['suffix'],
            $names['title'],
            $names['display_name'],
        );
        // ----------------------------------------

        $this->assertValuesFieldEqual($this->aPI_Profile, 'first_name', $names['first_name'], nullAllowed: true);
        $this->assertValuesFieldEqual($this->aPI_Profile, 'middle_name', $names['middle_name'], nullAllowed: true);
        $this->assertValuesFieldEqual($this->aPI_Profile, 'last_name', $names['last_name'], nullAllowed: true);
        $this->assertValuesFieldEqual($this->aPI_Profile, 'maiden_name', $names['maiden_name'], nullAllowed: true);
        $this->assertValuesFieldEqual($this->aPI_Profile, 'suffix', $names['suffix'], nullAllowed: true);
        $this->assertValuesFieldEqual($this->aPI_Profile, 'title', $names['title'], nullAllowed: true);
        $this->assertValuesFieldEqual($this->aPI_Profile, 'display_name', $names['display_name'], nullAllowed: true);
    }

    /**
     * Tests API_Profile->setNames ()
     *
     * @dataProvider provideSetProfile
     * @param string $ignore1
     * @param string $ignore2
     * @param string $ignore3
     * @param array <string, mixed> $source
     */
    public function testSetNames(string $ignore1, string $ignore2, string $ignore3, array $source): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(); // Set-up.

        foreach ($source['values']['names'] as $languageID => $names)
        {
            // ----------------------------------------
            $this->aPI_Profile->setNames($languageID, $names); // Test.
            // ----------------------------------------

            $langNames = $this->aPI_Profile->getNames($languageID); // Get results.

            $this->assertEquals($langNames, $names);
        }
    }

    /**
     * Tests API_Profile->setNicknames ()
     *
     * @dataProvider provideSetNames
     * @param array <string, mixed> $names
     */
    public function testSetNicknames(array $names): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile();

        // ----------------------------------------
        $this->aPI_Profile->setNicknames($names['nicknames']);
        // ----------------------------------------

        $this->assertValuesArrayEqual($this->aPI_Profile, 'nicknames', $names['nicknames']);
    }
}
