<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Gender;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_Name;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryEvents;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryLocations;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProfiles;

/**
 * API_Profile test case for UPDATE methods only.
 *
 * @see API_ProfileTest for other tests.
 */
class API_ProfileTestUpdate extends API_BaseTest
{
    /**
     * @var API_Profile
     */
    private API_Profile $aPI_Profile;

    public static function setUpBeforeClass(): void {}

    public static function tearDownAfterClass(): void {}

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
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
     * Provider for test profiles.
     * The values for each are: ID, GUID, Name, AboutMe, and the NEW AboutMe.
     *
     * @return array <array <string, string>>
     */
    public static function provideAboutMe(): array
    {
        $aboutMe = [
            'Adam' => [
                "* [http://www.mechon-mamre.org/p/pt/pt0101.htm#26 Genesis 1:26-31]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0102.htm#7 Genesis 2:7-25]\r\n" . "* [http://quran.",
                'Yankee Dooble'
            ],
            // com/2#2:29 Quran 2:29-38]. * [http://quran.com/3#3:59 Quran 3:59]. Wikipedia: * [http://en.wikipedia.org/wiki/Adam Adam] & [http://he.wikipedia.org/wiki/%D7%90%D7%93%D7%9D_%D7%95%D7%97%D7%95%D7%94 אדם וחוה] * [http://en.wikipedia.org/wiki/Generations_of_Adam Generations of Adam] * [http://en.wikipedia.org/wiki/Adam_in_Islam Adam in Islam] The name 'Adam' originated from the Hebrew word, pronounced aw-dawm, which means man, as a species, male and female. The Holy Scriptures use this literal meaning for Adam, meaning simply humans. '

            'Seth' => [
                "* [http://www.mechon-mamre.org/p/pt/pt0104.htm#25 Genesis 4:25-26]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0105.htm#3 Genesis 5:3-8] lists his place in",
                'Went to Town'
            ],
            // the ten generations between Adam & Noah. Normally genealogies are traced by the first-born sons, but Cain killed Abel and then was banished, so Seth became almost like a new “first-born”. * [http://www.biblestudytools.com/history/flavius-josephus/antiquities-jews/book-1/chapter-2.html Antiquities of the Jews, Book 1 Chapter 2:3.] Wikipedia: * [http://en.wikipedia.org/wiki/Seth Seth] and [http://he.wikipedia.org/wiki/%D7%A9%D7%AA שת]. * [https://en.wikipedia.org/wiki/Seth#Islam Seth in Islam.] Seth, 3rd son of Adam & Eve, was born after Cain murdered Abel. So the family line is continued through him. His name denoted this being derived from the root meaning 'appointed-given' instead of his brother(s), as Eve herself explains. ---- See also: [http://www.geni.com/profile/index/6000000003645707049 Seth Sat-naal]

            'Enosh' => [
                "* [http://www.mechon-mamre.org/p/pt/pt0104.htm#26 Genesis 4:26.]\r\n" . "* [http://www.mechon-mamre.org/p/pt/pt0105.htm#6 Genesis 5:6-11.]\r\n" . "* [http://www.pse",
                'Riding on his Pony'
            ]
            // udoepigrapha.com/jubilees/4.htm Book of Jubilees: 4:11-13.] Wikipedia: [http://en.wikipedia.org/wiki/Enos_%28Bible%29 Enos] See also (don't merge): [http://www.geni.com/profile/index/6000000003645882249 Henokh] He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), Ethiopian Orthodox tradition considers him a 'faithful and righteous servant of God', and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, 'as an instrument for codifying the laws'. He is commemorated as one of the Holy Forefathers in the Calendar of Saints of the Armenian Apostolic Church on July 30. According to the Book of Jubilees (4:11-13) in the Ethiopian Orthodox Bible, Enos was born in the Year of the World 235, and 'he began to call on the name of the Lord on the earth.' He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), and she bare him Kenan in the year 325. Ethiopian Orthodox tradition considers him a 'faithful and righteous servant of God', and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, 'as an instrument for codifying the laws'[1]. Enos in Hinduism is mentioned as Anoohan and is considered being the son of Seth (Shwetha-nama)[citation needed]. The text carried about Enoch was mentioned in the last Purana the Bhavishya Purana which was summarised text done in Sanskrit and attributed to Rishi Vyasa, the compiler of the Vedas. '
        ];

        return self::flagify($aboutMe);
    }

    /**
     * @return array <string, array <bool>>
     */
    public static function provideSetAliveFlag(): array
    {
        return self::provideTwoFlags();
    }

    /**
     * Provider for testUpdateSetPublic test.
     * The first 2 columns define the set-up of the test (isAlive and isPublic)
     * The 3rd coulmn defines the value to set, and the fourth coulmn defines the
     * expected result. E.g. If the profile is alive it can NOT be set public.
     * @return array <array <bool>>
     */
    public static function provideSetPublicFlags(): array
    {
        // This can NOT use the provideFourFlags function,
        // because the fourth column is different.

        return [
        //     Set-up        Test     Expected
        // Alive,  Public,   Public   Public
        [  true,   true,     true,    false  ],
        [  true,   true,     false,   false  ],
        [  true,   false,    true,    false  ],
        [  true,   false,    false,   false  ],

        [  false,  true,     true,    true   ],
        [  false,  true,     false,   false  ],
        [  false,  false,    true,    true   ],
        [  false,  false,    false,   false  ]
        ];
    }

    /**
     * @return array <array <string, bool>>
     */
    public static function provideSetStrings(): array
    {
        $setters = [
            // MIGHT not be settable. 'setEmail',
            'setOccupation',
            'setAboutMe'
        ];

        $testStrings = [
            'One Silly String',
            'After Another',
            'Nothing much to say',
            'except "enough already"',
            '',
            null
        ];

        $provideTestStrings = [];
        foreach ($setters as $setFlag)
        {
            foreach ($testStrings as $stringy)
            {
                $provideTestStrings[] = [
                    $setFlag,
                    $stringy
                ];
            }
        }

        return self::flagify($provideTestStrings);
    }

    /**
     * @return array <array <string, bool>>
     */
    public static function provideSetTextWithFlags(): array
    {
        $testStrings = [
        [ 'One Silly String'        ],
        [ 'After Another'           ],
        [ 'Nothing much to say'     ],
        [ 'except "enough already"' ],
        [ ''                        ],
        [ null                      ]
        ];

        return self::flagify($testStrings);
    }

    /**
     * @return array <string, array <\ShmuelAKam\Greenjeans\Api\Data\API_Gender|bool>>
     */
    public static function provideSetGender(): array
    {
        return self::flagify(FactoryProfiles::TEST_SetGender);
    }

    /**
     * @return array <array <API_Location|bool>>
     */
    public static function provideCurrentResidence(): array
    {
        return self::flagify(TestData::$currentResidences);
    }

    /**
     * @return array <array <mixed>>
     */
    public static function provideNickname(): array
    {
        return self::flagify(TestData::$nicknames);
    }

    /**
     * @return array <array <bool|\ShmuelAKam\Greenjeans\Api\Data\API_Name>>
     */
    public static function provideSetNames(): array
    {
        $setNames = [];
        foreach (TestProfiles::$Adam['values']['names'] as $langID => $name)
        {
            $setNames[] = [
                $langID,
                $name
            ];
        }

        return self::flagify($setNames);
    }

    /**
     * @return array <string, array <array <API_Name>>>
     */
    public static function provideSetNamesMultiple(): array
    {
        $setNames = [
            "Adam"  => [ TestProfiles::$Adam  ['values']['names'] ],
            "Seth"  => [ TestProfiles::$Seth  ['values']['names'] ],
            "Enosh" => [ TestProfiles::$Enosh ['values']['names'] ]
        ];

        return self::flagify($setNames);
    }

    /**
     * @return array <array <mixed>>
     */
    public static function provideFullnames(): array
    {
        return self::flagify(TestData::$fullNames);
    }

    /**
     * Provider for tests that do NOT need specific data, only a number of
     * @return array <array <int>>
     */
    public static function provideFlagRun(): array
    {
        $setSize = [
            [  1 ],
            [  2 ],
            [  5 ],
            [  7 ],
            [ 11 ]
        ];

        return self::flagify($setSize);
    }

    // =====================================================
    // Helper functions.

    /**
     * Assert that setter->update was a success.
     * This is done by creating a SECOND API_Profile brief object, and calling
     * the getter on that. This ensures that the object was not influenced by
     * the setter function.
     *
     * @param string $setter
     *            name of function.
     * @param string $profileID
     *            ID of profile that was used by the setter,
     * @param mixed $expected
     *            Expected value to compare with.
     */
    private function assertSetter(string $setter, string $profileID, $expected): void
    {
        $checker = API_Profile::getBrief($profileID);

        $getter = $this->getGetterName($setter);

        $result = $checker->$getter();

        if ($expected != null && $expected != '')
        {
            $this->assertNotNull($result);
        }

        $this->assertEquals($expected, $result, "Update of setter '$setter' failed.");
    }

    /**
     * Return name of relevant getter function.
     *
     * @param string $setter
     * @return string
     */
    private function getGetterName(string $setter): string
    {
        $getter = '';

        switch ($setter)
        {
            default:
                // replace "set" with "get", i.e. just the first letter.
                $getter = substr_replace($setter, "get", 0, 3);
                break;

                // case 'setLocked': $getter = 'isLocked';
                // break;
        }

        return $getter;
    }

    private function assertEventsEqual(?API_Event $expected, ?API_Event $actual, string $msg): void
    {
        $this->assertNotNull($actual, 'No event returned');

        $eventsSame = FactoryEvents::equalEvents($expected, $actual);

        $this->assertTrue($eventsSame, $msg);
    }

    // =====================================================
    // Test functions.

    /**
     * Test setIsAlive and setIsPublic TOGETHER.
     * @dataProvider provideFourFlags
     */
    public function testSetIsAlivePublic(bool $baseAlive, bool $basePublic, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $baseAlive, public: $basePublic);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setIsAlive($alive);
        $this->aPI_Profile->setPublic($public);

        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::get($profileID);

        $checkAlive  = $checker->isAlive();
        $checkPublic = $checker->isPublic();

        if ($alive && $public)
        {
            // It is NOT possible to set both of these flags to TRUE.
            // The Server will automtically set 'public' to FALSE.

            $this->assertEquals($alive, $checkAlive, "TT - Alive doesn't match");
            $this->assertEquals(false, $checkPublic, "TT - Public not FALSE");
        }
        else
        {
            $this->assertEquals($alive, $checkAlive, "Alive doesn't match");
            $this->assertEquals($public, $checkPublic, "Public doesn't match");
        }
    }

    /**
     * Tests API_Profile->update() for setIsAlive.
     *
     * @dataProvider provideSetAliveFlag
     */
    public function testUpdateSetIsAlive(bool $initialAlive, bool $setAlive): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $initialAlive, public: false);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setIsAlive($setAlive);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker    = API_Profile::getBrief($profileID);
        $checkAlive = $checker->isAlive();

        $this->assertEquals($setAlive, $checkAlive, "Update of setter 'setIsAlive' failed.");
    }

    /**
     * Tests API_Profile->update() for setPublic.
     *
     * @dataProvider provideSetPublicFlags
     */
    public function testUpdateSetPublic(bool $isAlive, bool $isPublic, bool $setPublic, bool $expected): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile($isAlive, $isPublic);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setPublic($setPublic);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker     = API_Profile::getBrief($profileID);
        $checkPublic = $checker->isPublic();

        $this->assertEquals($expected, $checkPublic, "Update of setter 'setPublic' failed.");
    }

    /**
     * Tests API_Profile->setCauseOfDeath().
     *
     * @dataProvider provideSetTextWithFlags
     */
    public function testUpdateSetCauseOfDeath(?string $expectedCause, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setCauseOfDeath($expectedCause);

        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker     = API_Profile::getBrief($profileID);
        $actualCause = $checker->getCauseOfDeath();

        if ($expectedCause === null)
        {
            $expectedCause = '';
        }

        // If set failed, then try a second time. There is some bug
        // that I couldn't find, that rarely causes a fail. Not
        // clear whether it's in the SDK or the Server.
        if ($expectedCause != '' && $actualCause != '')
        {
            if (strcmp($expectedCause, $actualCause) != 0)
            {
                $this->aPI_Profile->setCauseOfDeath($expectedCause);
                $updated = $this->aPI_Profile->update();

                $this->assertTrue($updated, 'Update Failed');

                $checker     = API_Profile::getBrief($profileID);
                $actualCause = $checker->getCauseOfDeath();
            }
        }
        else
        {
            if ($expectedCause != '' || $actualCause != '')
            {
                $this->fail("Only one 'cause opf death' is empty");
            }
        }

        $actualAlive = $checker->isAlive();

        $this->assertEquals($expectedCause, $actualCause, "Update of setCauseOfDeath failed.");
        $this->assertFalse($actualAlive);
    }

    /**
     * Tests API_Profile->update() for all setters that have a string parameter.
     *
     * @dataProvider provideSetStrings
     */
    public function testUpdateSetString(string $setter, ?string $value, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->$setter($value);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $this->assertSetter($setter, $profileID, $value);
    }

    // /**
    // * Tests API_Profile->update() for setMergeNote.
    // * @dataProvider provideSetTextWithFlags
    // */
    // public function testUpdateSetMergeNote (?string $value, bool $alive, bool $public): void
    // {
    //     /** @var FactoryProfiles $profileFactory */
    //     $profileFactory = FactoryBase::getFactory(API_Profile::class);
    //
    // $this->aPI_Profile = $profileFactory->makeRandomProfile (alive: $alive, public: $public);
    //
    // //----------------------------------------
    // $this->aPI_Profile->setMergeNote ($value);
    // $updated = $this->aPI_Profile->update();
    // //----------------------------------------
    //
    // $this->assertTrue ($updated, "Update for 'setMergeNote' failed");
    // $this->assertSetter ('setMergeNote', '34838408177', $value);
    // }

    /**
     * ============================================================
     */

    /**
     * Tests API_Profile->update() for setGender.
     *
     * @dataProvider provideSetGender
     */
    public function testUpdateSetGender(API_Gender $fromGender, API_Gender $toGender, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        list($firstName, $lastName) = $profileFactory->makeRandomName($fromGender);

        $this->aPI_Profile = $profileFactory->makeTestProfile($firstName, $lastName, alive: $alive, public: $public, gender: $fromGender);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setGender($toGender);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $result = $checker->getGender();

        $this->assertEquals($toGender, $result, "Update of setter 'setGender' failed.");
    }

    /**
     * ============================================================
     */

    /**
     * This provider used to generate multiple calls.
     * It's data can be ignored.
     *
     * @dataProvider provideFlagRun
     */
    public function testUpdateSetBirth(int $ignore, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);

        $profileID = $this->aPI_Profile->getID();
        $fullName  = $this->aPI_Profile->getFullName();
        $eventName = $fullName->getFirstName() . ' ' . $fullName->getLastName();

        $event = $eventFactory->makeBirthEvent($eventName);

        // ----------------------------------------
        $this->aPI_Profile->setBirth($event);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $setEvent = $checker->getBirth();

        $this->assertEventsEqual($event, $setEvent, "Update of setter 'setBirth' failed.");
    }

    /**
     *
     * @dataProvider provideFlagRun
     */
    public function testUpdateSetDeath(int $ignore, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);

        $profileID = $this->aPI_Profile->getID();
        $fullName  = $this->aPI_Profile->getFullName();
        $eventName = $fullName->getFirstName() . ' ' . $fullName->getLastName();

        $event = $eventFactory->makeDeathEvent($eventName);

        // ----------------------------------------
        $this->aPI_Profile->setDeath($event);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker  = API_Profile::getBrief($profileID);
        $setEvent = $checker->getDeath();

        $this->assertNotNull($setEvent);
        $this->assertEventsEqual($event, $setEvent, "Update of setter 'setDeath' failed.");
    }

    /**
     *
     * @dataProvider provideFlagRun
     */
    public function testUpdateSetBurial(int $ignore, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);

        $profileID = $this->aPI_Profile->getID();
        $fullName  = $this->aPI_Profile->getFullName();
        $eventName = $fullName->getFirstName() . ' ' . $fullName->getLastName();

        $event = $eventFactory->makeBurialEvent($eventName);

        // ----------------------------------------
        $this->aPI_Profile->setBurial($event);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $setEvent = $checker->getBurial();

        $this->assertEventsEqual($event, $setEvent, "Update of setter 'setBurial' failed.");
    }

    /**
     *
     * @dataProvider provideFlagRun
     */
    public function testUpdateSetBaptism(int $ignore, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);
        /** @var FactoryEvents $eventFactory */
        $eventFactory = FactoryBase::getFactory(API_Event::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);

        $profileID = $this->aPI_Profile->getID();
        $fullName  = $this->aPI_Profile->getFullName();
        $eventName = $fullName->getFirstName() . ' ' . $fullName->getLastName();

        $event = $eventFactory->makeBaptismEvent($eventName);

        // ----------------------------------------
        $this->aPI_Profile->setBaptism($event);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $setEvent = $checker->getBaptism();

        $this->assertEventsEqual($event, $setEvent, "Update of setter 'setBaptism' failed.");
    }

    /**
     * ============================================================
     */

    /**
     *
     * @dataProvider provideCurrentResidence
     */
    public function testUpdateCurrentResidence(API_Location $location, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('Who', 'Dunnit', alive: $alive, public: $public, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setCurrentResidence($location);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $setLocation = $checker->getCurrentResidence();

        $this->assertNotNull($setLocation, 'No location returned');
        $this->assertTrue(FactoryLocations::equalLocations($location, $setLocation));
    }

    /**
     * @dataProvider provideNickname
     * @param array <string> $nicknameArray
     * @param bool $alive
     * @param bool $public
     */
    public function testUpdateNickname($nicknameArray, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('Who', 'Dunnit', alive: $alive, public: $public, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setNicknames($nicknameArray);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $setNicknameArray = $checker->getNicknames();

        $this->assertEquals($nicknameArray, $setNicknameArray, "Update of setter 'setNicknames' failed.");
    }

    /**
     *
     * @dataProvider provideSetNames
     */
    public function testUpdateSetNames(string $languageID, API_Name $name, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('He', 'Diddit', alive: $alive, public: $public, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setNames($languageID, $name);
        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $setNames = $checker->getNames($languageID);

        $this->assertNotNull($setNames, 'Names not returned');
        $this->assertEquals($name, $setNames, "Update of setter 'setNames' failed.");
    }

    /**
     * @dataProvider provideSetNamesMultiple
     * @param array <API_Name> $testNames
     * @param bool  $alive
     * @param bool  $public
     */
    public function testUpdateSetNamesMultiple(array $testNames, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeTestProfile('He', 'Diddit', alive: $alive, public: $public, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        foreach ($testNames as $languageID => $name)
        {
            $this->aPI_Profile->setNames($languageID, $name);
        }

        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $setNamesArray = $checker->getNamesAll();

        $this->assertNotNull($setNamesArray, 'Names not returned');

        foreach ($setNamesArray as $languageID => $name)
        {
            if (array_key_exists($languageID, $testNames))
            {
                $this->assertEquals($name, $testNames[$languageID], "Name in language '$languageID' NOT equal.");

                unset($testNames[$languageID]);
                unset($setNamesArray[$languageID]);
            }
            else
            {
                $this->fail("Failed to setName ($languageID, {$name->getDisplayName()}).");
            }
        }

        $this->assertEmpty($testNames, "Not all names returned.");
        $this->assertEmpty($setNamesArray, "WTF. Returned names NOT SET.");
    }

    /**
     * ============================================================
     */

    /**
     * Tests API_Profile->update setFullname()
     *
     * @dataProvider provideFullnames
     */
    public function testUpdateSetFullName(API_Name $name, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        // Using BLANK name fields in the test object, so nothing gets added.

        $this->aPI_Profile = $profileFactory->makeTestProfile('', '', alive: $alive, public: $public, gender: API_Gender::Male);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $this->aPI_Profile->setFullName(
            first_name: $name->getFirstName(),
            middle_name: $name->getMiddleName(),
            last_name: $name->getLastName(),
            maiden_name: $name->getMaidenName(),
            suffix: $name->getSuffix(),
            title: $name->getTitle(),
            display_name: $name->getDisplayName(),
        );

        $updated = $this->aPI_Profile->update();
        // ----------------------------------------

        $this->assertTrue($updated, 'Update Failed');

        $checker = API_Profile::getBrief($profileID);

        $fullName = $checker->getFullname();

        $this->assertEquals($name, $fullName, "Update of setter 'setFullName' failed.");
    }

    /**
     *
     * @dataProvider provideAboutMe
     */
    public function testUpdateAboutMe(string $AboutMe, string $NewText, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $updated = $this->aPI_Profile->updateAboutMe($NewText);
        // ----------------------------------------

        $checker     = API_Profile::get($profileID);
        $updatedText = $checker->getAboutMe();

        $this->assertTrue($updated, 'AboutMe NOT updated');
        $this->assertEquals($NewText, $updatedText);
    }

    /**
     * Tests API_Profile->updateAboutMe()
     *
     * @dataProvider provideAboutMe
     */
    public function testUpdateAboutMe_Empty(string $AboutMe, string $NewText, bool $alive, bool $public): void
    {
        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $this->aPI_Profile = $profileFactory->makeRandomProfile(alive: $alive, public: $public);
        $profileID         = $this->aPI_Profile->getID();

        // ----------------------------------------
        $updated = $this->aPI_Profile->updateAboutMe('');
        // ----------------------------------------

        $checker     = API_Profile::get($profileID);
        $updatedText = $checker->getAboutMe();

        $this->assertTrue($updated, 'Empty AboutMe NOT updated');
        $this->assertEquals('', $updatedText, 'Empty AboutMe is NOT empty.');
    }
}
