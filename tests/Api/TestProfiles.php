<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_Name;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;

// TestProfiles::setup(); Is called in the bootstrap code, in GlobalSetup.php

class TestProfiles
{
    /**
     * This function does the final set-up operations on the big profile variables
     * defined below. The function is called above when the file is first loaded.
     */
    public static function setup(): void
    {
        self::setupSubObjects();

        self::setupNames();
    }

    /**
     * Returns a reference to the values array for a given profile property.
     * @param  string $name
     * @return array<string, mixed>
     */
    private static function &getProfileRef(string $name): array
    {
        switch ($name)
        {
            case 'Adam':   return self::$Adam   ['values'];
            case 'Seth':   return self::$Seth   ['values'];
            case 'Enosh':  return self::$Enosh  ['values'];
            case 'Myself': return self::$Myself ['values'];

            default:
                throw new \InvalidArgumentException("Unknown profile property: {$name}");
        }
    }

    /**
     * This function creates sub-objects for the four big variables.
     */
    private static function setupSubObjects(): void
    {
        foreach (self::$PROFILE_OBJECT_DATA as $name => $person)
        {
            /**
             * @var array <string, array <string, string|NULL|bool>> $personArray
             * refers to the big class variables.
             */
            $personArray = &self::getProfileRef($name);

            if (isset($person ['birth']))
            {
                $personArray ['birth'] = API_Event::newFromarray($person ['birth']);
            }

            if (isset($person ['burial']))
            {
                $personArray ['burial'] = API_Event::newFromarray($person ['burial']);
            }

            if (isset($person ['death']))
            {
                $personArray ['death'] = API_Event::newFromarray($person ['death']);
            }

            if (isset($person ['current_residence']))
            {
                $personArray ['current_residence'] = API_Location::newFromarray($person ['current_residence']);
            }

            if (isset($person ['location']))
            {
                $personArray ['location'] = API_Location::newFromarray($person ['location']);
            }

            if (isset($person ['mugshot_urls']))
            {
                $personArray ['mugshot_urls'] = API_PhotoImageSizeMap::newFromarray($person ['mugshot_urls']);
            }

            unset($personArray);
        }
    }

    private static function setupNames(): void
    {
        foreach (self::$PROFILE_OBJECT_NAMES as $name => $person)
        {
            $personArray = &self::$$name['values'];

            $personArray ['names'] = [];

            foreach ($person as $locale => $nameParams)
            {
                $personArray ['names'][$locale] = API_Name::newFromarray($nameParams);
            }
        }

    }

    // =====================================================================

    /**
     * Test variables for four complete profiles: Adam, Seth, Enosh and Myself.
     * These are variables and NOT const, because they include sub-objects that
     * must be created and added at run-time.
     */

    //=====================================================================
    //
    // .....####.....########.......####.....###......###..
    // ....##..##......##...##.....##..##....###......###..
    // ...##....##.....##....##...##....##...##.##..##.##..
    // ..##########....##....##..##########..##..####..##..
    // ..##......##....##....##..##......##..##........##..
    // ..##......##....##...##...##......##..##........##..
    // ..##......##..########....##......##..##........##..
    //
    //=====================================================================

    /**
     * Used by API_ProfileTest & API_ProfileTestUpdate classes.
     * @var array <string, mixed> $Adam
     */
    public static $Adam = [
        'projects' => [
            '0' => 'project-4504495',
            '1' => 'project-30758',
            '2' => 'project-12495',
            '3' => 'project-2380',
            '4' => 'project-2305',
            '5' => 'project-12283',
            '6' => 'project-10927',
            '7' => 'project-10799',
            '8' => 'project-8209',
            '9' => 'project-8193',
            '10' => 'project-8153',
            '11' => 'project-1055',
            '12' => 'project-2785',
            '13' => 'project-38'
        ],
        'documents' => [],
        'photos' => [],
        'videos' => [],

        'values' => [
            'guid' => '6000000003538706117',
            'name' => 'Adam of Eden',
            'id' => 'profile-53597431',
            'url' => 'https://www.geni.com/api/profile-53597431',
            'profile_url' => 'https://www.geni.com/people/Adam-of-Eden/6000000003538706117',
            'public' => '1',
            'first_name' => 'Adam',
            'maiden_name' => '.',
            'last_name' => '.',
            'display_name' => 'Adam of Eden',
            'is_alive' => '',
            'gender' => 'male',
            'occupation' => 'Gardener, First Man',
            'created_by' => 'profile-9288190',
            'claimed' => '',
            'curator' => 'profile-9288190',
            'unions' => [
                '0' => 'union-49008995',
                '1' => 'union-39702255',
                '2' => 'union-36290190'
            ],

            'marriage_orders' => [
                '6000000011013752649' => '0'
            ],

            'birth_order' => '99',
            'living' => '',
            'location' => null,

            'creator' => 'user-1444024',
            'account_type' => 'basic',
            'photo_urls' => [
                'medium' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_medium.jpg?hash=4cce8c1ad8c27284b6433d6c4586b16682b594af3069c61cf2af186df281e7ea.1746687599',
                'small' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_small.jpg?hash=1259133d44dcbe5e9e18da9551fcb30ccf16b3e3504584c7662c6eb8fc4d44c2.1746687599',
                'thumb' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_t.jpg?hash=77dc3069008e5a750d2a02542699b1666d000f401b94b10a82aa082c9b46ad2b.1746687599',
                'print' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_print.jpg?hash=74300e06ab8b18658e3494317313e006fd8b2e148a101a6f3da701f28639e380.1746687599',
                'thumb2' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_t2.jpg?hash=6a6f308057035d5fd53d06e692c3590cb2687845330a34af03c1fd617875b30f.1746687599',
                'url' => 'https://www.geni.com/api/photo_crop-6000000060177516821'
            ],

            'created_at' => '1169084604',
            'updated_at' => '1788990617',
            'documents_updated_at' => '1544469383',
            'photos_updated_at' => '1764369854',
            'managers' => [
                "profile-9288190"
            ],
            'master_profile' => true,
            'locked' => true,

            'deleted' => '',
            'is_curator' => '',
            'birth'  => null,
            'burial' => null,
            'death'  => null,
            'current_residence' => null,

            'names' => null,
            'nicknames' => [
                '0' => 'Nabi Adam'
            ],

            'about_me' => "* [http://mechon-mamre.org/p/pt/pt0101.htm#26 Genesis 1:26-31]\r\n" . "* [http://mechon-mamre.org/p/pt/pt0102.htm#7 Genesis 2:7-25]\r\n" . "* [https://mechon-mamre.org/p/pt/pt25a01.htm#1 1 Chronicles 1:1.]\r\n" . "* [http://quran.com/2#2:29 Quran 2:29-38].\r\n" . "* [http://quran.com/3#3:59 Quran 3:59].\r\n" . "\r\n" . "Wikipedia:\r\n" . "* [http://en.wikipedia.org/wiki/Adam Adam] & [http://he.wikipedia.org/wiki/%D7%90%D7%93%D7%9D_%D7%95%D7%97%D7%95%D7%94 אדם וחוה]\r\n" . "* [http://en.wikipedia.org/wiki/Generations_of_Adam Generations of Adam]\r\n" . "* [http://en.wikipedia.org/wiki/Adam_in_Islam Adam in Islam]\r\n" . "\r\n" . "The name \"Adam\" originated from the Hebrew word, pronounced aw-dawm, which means man, as a species, male and female. The Holy Scriptures use this literal meaning for Adam, meaning simply humans.\r\n" . "\r\n" . "Anyone with questions about this or other biblical profiles, or the project to merge all Biblical tree on Geni, is invited to read the attached Discussion, and the FAQ: [http://www.geni.com/projects/Biblical-Tree Biblical-Tree Project] page.\r\n" . "\r\n" . "--------------------\r\n" . "The Sumerian Mythology has a parallel for Adam named [http://www.geni.com/profile/index/6000000003645882266 Enki] and has an entire family tree of his ancestors.\r\n" . "--------------------\r\n" . "Adam is a figure in the Book of Genesis, the Quran and the Kitáb-i-Íqán. According to the creation myth of Abrahamic religions, he is the first human. Int he Genesis creation narratives, he was created by Yahweh-Elohim (\"Yahweh-God\", the god of Israel), though the term \"adam\" can refer to both the first individual person, as well as to the general creation of humankind. Christian churches differ on how they view Adam's subsequent behavior (often called the ''Fall of Man''), and to the consequences that those actions had on the rest of humanity. Christian and Jewish teachings sometimes hold Adam and Eve (the first woman) to a different level of responsibility for the Fall, though Islamic teaching holds both equally responsible. In addition, Islam holds that Adam was eventually forgiven, while Christianity holds that redemption occurred only later through the sacrifice of Jesus Christ. Bahá'í Faith, Islam and some Christian denominations consider Adam to be the first Prophet.\r\n" . "--------------------\r\n" . "\r\n" . "* [http://quran.com/2#2:29 Quran 2:29-38].\r\n" . "* [http://quran.com/3#3:59 Quran 3:59].\r\n" . "\r\n" . "The 1st man on earth, created by Allah SWT as a Caliph. Survived for 1000 years.\r\n" . "\r\n" . "The 3rd Stage of Prophet Muhammad SAW Family Tree. \r\n"
        ]

        // 'setFields' => []
    ];

    // =====================================================================
    //
    // ...######.##########.########.##....##..
    // ..##........##..........##....##....##..
    // ..##........##..........##....##....##..
    // ...#####....#####.......##....########..
    // .......##...##..........##....##....##..
    // .......##...##..........##....##....##..
    // ..######..#########.....##....##....##..
    //
    // =====================================================================

    /**
     * Used by API_ProfileTest & API_ProfileTestUpdate classes.
     * @var array <string, mixed> $Seth
     */
    public static $Seth = [
        'projects' => [
            '0' => 'project-4504495',
            '1' => 'project-12495',
            '2' => 'project-2380',
            '3' => 'project-2305',
            '4' => 'project-12283',
            '5' => 'project-10927',
            '6' => 'project-8193',
            '7' => 'project-1055',
            '8' => 'project-38'
        ],

        'documents' => [],
        'photos' => [],
        'videos' => [],

        'values' => [
            'guid' => '6000000006375916501',
            'id' => 'profile-4095031',
            'name' => 'Seth .',
            'url' => 'https://www.geni.com/api/profile-4095031',
            'profile_url' => 'https://www.geni.com/people/Seth/6000000006375916501',
            'public' => '1',
            'first_name' => 'Seth',
            'maiden_name' => '.',
            'last_name' => '.',
            'is_alive' => '',
            'gender' => 'male',
            'created_by' => 'profile-9288190',
            'claimed' => '',
            'curator' => 'profile-9288190',
            'unions' => [
                '0' => 'union-36290190',
                '1' => 'union-19124527'
            ],

            'marriage_orders' => [
                '6000000003828491354' => '0'
            ],

            'birth_order' => '1',
            'living' => '',
            'creator' => 'user-1444024',
            'account_type' => 'basic',
            'photo_urls' => [
                'large' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_large.jpg?hash=ebfa9b296164eeb3ed6a9d87b2611ebd9af5e8f6bac3d9e3fb381282870d4a30.1746687599',
                'medium' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_medium.jpg?hash=02205ad1dfc230de836db87f5201951ea018d93ad1146dc8b9d8a4badf146921.1746687599',
                'small' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_small.jpg?hash=766d6fae0b4ca29ac75e0dbee42beca303e7b716a83dc63874a0eb4785d25b09.1746687599',
                'thumb' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_t.jpg?hash=42d3852878c9553ebcd7fa53d910166d0f94dfe7a848c8df226bcd550c489b4d.1746687599',
                'print' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_print.jpg?hash=8771cd12391394fd35115adab3d22699009d955fe733c1af386d9943a4ed0ded.1746687599',
                'thumb2' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_t2.jpg?hash=ae8f3b842ab2971907852385e4c684903e00d6990b9329d14adb51c20d7498dc.1746687599',
                'original' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_original.jpg?hash=3fcc71a610a895ffef357e8477dc22741da030a832b21a136b771e1fe8b1a745.1746687599',
                'url' => 'https://www.geni.com/api/photo-30860468'
            ],

            'created_at' => '1169084804',
            'updated_at' => '1766017605',
            'documents_updated_at' => '1544469383',
            'photos_updated_at' => '1764894645',
            'managers' => [
                "profile-9288190"
            ],
            'master_profile' => true,
            'locked' => true,

            'deleted' => '',
            'is_curator' => '',

            'birth'  => null,
            'burial' => null,
            'death'  => null,

            'names' => null,
            'nicknames' => [
                '0' => 'Sis'
            ],
            'about_me' => "* [http://mechon-mamre.org/p/pt/pt0104.htm#25 Genesis 4:25-26]\r\n" . "* [http://mechon-mamre.org/p/pt/pt0105.htm#3 Genesis 5:3-8] lists his place in the ten generations between Adam & Noah. Normally genealogies are traced by the first-born sons, but Cain killed Abel and then was banished, so Seth became almost like a new “first-born”.\r\n" . "* [https://mechon-mamre.org/p/pt/pt25a01.htm#1 1 Chronicles 1:1.]\r\n" . "* [http://www.biblestudytools.com/history/flavius-josephus/antiquities-jews/book-1/chapter-2.html Antiquities of the Jews, Book 1 Chapter 2:3.]\r\n" . "\r\n" . "Wikipedia:\r\n" . "* [http://en.wikipedia.org/wiki/Seth Seth] and [http://he.wikipedia.org/wiki/%D7%A9%D7%AA שת].\r\n" . "* [https://en.wikipedia.org/wiki/Seth#Islam Seth in Islam.]\r\n" . "\r\n" . "Seth, 3rd son of Adam & Eve, was born after Cain murdered Abel. So the family line is continued through him. His name denoted this being derived from the root meaning \"appointed-given\" instead of his brother(s), as Eve herself explains.\r\n" . "----\r\n" . "See also: [http://www.geni.com/profile/index/6000000003645707049 Seth Sat-naal]\r\n"
        ]

        // 'setFields' => []
    ];

    // =====================================================================
    //
    // ..##########.###.....##...####....######.##....##..
    // ....##.......####....##..##..##..##......##....##..
    // ....##.......##.##...##.##....##.##......##....##..
    // ....#####....##..##..##.##....##..#####..########..
    // ....##.......##...##.##.##....##......##.##....##..
    // ....##.......##....####..##..##.......##.##....##..
    // ..##########.##.....###...####...######..##....##..
    //
    // =====================================================================

    /**
     * Used by API_ProfileTest & API_ProfileTestUpdate classes.
     * @var array <string, mixed> $Enosh
     */
    public static $Enosh = [
        'projects' => [
            '0' => 'project-4504495',
            '1' => 'project-12495',
            '2' => 'project-2380',
            '3' => 'project-2305',
            '4' => 'project-12283',
            '5' => 'project-10927',
            '6' => 'project-1055',
            '7' => 'project-38'
        ],
        'documents' => [],
        'photos' => [],
        'videos' => [],

        'values' => [
            'guid' => '6000000000190131271',
            'id' => 'profile-4094989',
            'name' => 'Enosh .',
            'url' => 'https://www.geni.com/api/profile-4094989',
            'profile_url' => 'https://www.geni.com/people/Enosh/6000000000190131271',
            'public' => '1',
            'first_name' => 'Enosh',
            'maiden_name' => '.',
            'last_name' => '.',
            'is_alive' => '',
            'gender' => 'male',
            'occupation' => "Biblical figure, Born: 436 AM, Luke 3:38, b.ca3765bc d.ca2870bc, 905år, Man'; aka IdrisBorn: 3769 BC Died: 2864 BC, Lived 905 years, Patriarch, Died at 905, Primero en invocar el nombre de Yahveh Dios.",
            'created_by' => 'profile-9288190',
            'claimed' => '',
            'curator' => 'profile-9288190',
            'unions' => [
                '0' => 'union-19124527',
                '1' => 'union-18123201'
            ],

            'marriage_orders' => [
                '6000000003513277528' => '0'
            ],

            'birth_order' => '1',
            'living' => '',
            'creator' => 'user-1444024',
            'photo_urls' => [
                'medium' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_medium.jpg?hash=6ba1ce163e7b26f845e291c49734dfb87f5ecbc4b3b0a8fa5c59ce657b382f87.1746687599',
                'small' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_small.jpg?hash=6c210d5d6cb725e0ac6d3bf7b73010233c2e411e358650c0d9642066d4a1f263.1746687599',
                'thumb' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_t.jpg?hash=4e18202456852652a42522d878eaa6c112808bf59ed19b5f2f133386b0e0690c.1746687599',
                'print' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_print.jpg?hash=a108f653addc10832bb570c44031ddeba11ebb78de04368e87263f6e8a4214ce.1746687599',
                'thumb2' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_t2.jpg?hash=842304e281c3f835518a385cd66ed23d8fb80abd62e23f697f7bba483acc2ab7.1746687599',
                'url' => 'https://www.geni.com/api/photo_crop-6000000008771363267'
            ],

            'created_at' => '1172481280',
            'updated_at' => '1788995364',
            'documents_updated_at' => '1544469383',
            'photos_updated_at' => '1764894645',
            'managers' => [
                "profile-9288190"
            ],
            'master_profile' => true,
            'locked' => true,
            'deleted' => '',

            'birth'  => null,
            'burial' => null,
            'death'  => null,

            'names' => null,
            'nicknames' => [
                '0' => 'Enosh',
                '1' => 'Enos',
                '2' => 'Anoosh',
                '3' => 'Anoush',
                '4' => 'Anush',
                '5' => 'Anwas'
            ],

            'about_me' => "* [http://mechon-mamre.org/p/pt/pt0104.htm#26 Genesis 4:26.]\r\n" . "* [http://mechon-mamre.org/p/pt/pt0105.htm#6 Genesis 5:6-11.]\r\n" . "* [https://mechon-mamre.org/p/pt/pt25a01.htm#1 1 Chronicles 1:1.]\r\n" . "* [http://www.pseudepigrapha.com/jubilees/4.htm Book of Jubilees: 4:11-13.]\r\n" . "\r\n" . "Wikipedia: [http://en.wikipedia.org/wiki/Enos_%28Bible%29 Enos]\r\n" . "\r\n" . "See also (don't merge): [http://www.geni.com/profile/index/6000000003645882249 Henokh]\r\n" . "\r\n" . "He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry),\r\n" . "\r\n" . "Ethiopian Orthodox tradition considers him a \"faithful and righteous servant of God\", and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, \"as an instrument for codifying the laws\".\r\n" . "\r\n" . "He is commemorated as one of the Holy Forefathers in the Calendar of Saints of the Armenian Apostolic Church on July 30.\r\n" . "\r\n" . "According to the Book of Jubilees (4:11-13) in the Ethiopian Orthodox Bible, Enos was born in the Year of the World 235, and \"he began to call on the name of the Lord on the earth.\" He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), and she bare him Kenan in the year 325. Ethiopian Orthodox tradition considers him a \"faithful and righteous servant of God\", and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, \"as an instrument for codifying the laws\"[1].\r\n" . "\r\n" . "Enos in Hinduism is mentioned as Anoohan and is considered being the son of Seth (Shwetha-nama)[citation needed]. The text carried about Enoch was mentioned in the last Purana the Bhavishya Purana which was summarised text done in Sanskrit and attributed to Rishi Vyasa, the compiler of the Vedas.\r\n"
        ]

        // 'setFields' => []
    ];

    // =====================================================================
    //
    // ..###......###..###########..
    // ..###......###....##.........
    // ..##.##. ##.##....##.........
    // ..##..####..##....######.....
    // ..##...##...##....##.........
    // ..##........##....##.........
    // ..##........##..###########..
    //
    // =====================================================================

    /**
     * Used by API_ProfileTest & API_ProfileTestUpdate classes.
     * @var array <string, mixed> $Myself
     */
    public static $Myself = [
        'isBrief' => false,
        'projects' => [],
        'documents' => [],
        'photos' => [],
        'videos' => [],

        'values' => [
            'id' => 'profile-9288190',
            'guid' => '5098603059080070794',
            'name' => 'Shmuel-Aharon Kam (Kahn / שמואל-אהרן קם (קאן',
            'url' => 'https://www.geni.com/api/profile-9288190',
            'profile_url' => 'https://www.geni.com/people/Shmuel-Aharon-Kam-Kahn-%D7%A9%D7%9E%D7%95%D7%90%D7%9C-%D7%90%D7%94%D7%A8%D7%9F-%D7%A7%D7%9D-%D7%A7%D7%90%D7%9F/5098603059080070794',
            'public' => 0,
            'first_name' => 'Shmuel-Aharon',
            'maiden_name' => 'Kahn',
            'last_name' => 'Kam',
            'display_name' => 'Shmuel-Aharon Kam (Kahn / שמואל-אהרן קם (קאן',
            'is_alive' => 1,
            'gender' => 'male',
            'occupation' => 'Software Engineer',
            'created_by' => 'profile-9288190',
            'big_tree' => 1,
            'claimed' => 1,
            'language' => 'en-US',
            'unions' => [
                '0' => 'union-9135214',
                '1' => 'union-4627702'
            ],

            'relationship' => 'yourself',
            'phone_numbers' => [
                '0' => [
                    'type' => 'Mobile',
                    'area_code' => '52',
                    'number' => '5317-243'
                ],

                '1' => [
                    'type' => 'Home',
                    'area_code' => '4',
                    'number' => '635-0484'
                ]
            ],

            'marriage_orders' => [
                '5098603145210070701' => 0
            ],

            'birth_order' => 1,
            'living' => 1,
            'location' => null,

            'creator' => 'user-1444024',
            'account_type' => 'pro',
            'photo_urls' => [
                'large' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_large.jpg',
                'medium' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_medium.jpg',
                'small' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_small.jpg',
                'thumb' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_t.jpg',
                'print' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_print.jpg',
                'thumb2' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_t2.jpg',
                'original' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_original.jpg',
                'url' => 'https://www.geni.com/api/photo-37606528'
            ],

            'created_at' => '1174012484',
            'updated_at' => '1732701215',
            'deleted' => false,
            'is_curator' => 1,

            'birth'  => null,
            'names' => null,
        ],

        'setFields' => [
            '0' => 'id',
            '1' => 'guid',
            '2' => 'name'
        ]
    ];

    //=====================================================================
    // arrays of parameters for the SUB-objects that go into the big variables
    // above. These are used when the file is first loaded in run-time.
    // =====================================================================

    /**
     * @var array <string, array <string, array <string, mixed>>> $PROFILE_OBJECT_DATA
     */
    private static $PROFILE_OBJECT_DATA = [
        'Adam' => [
            'birth' => [
                'name' => 'Birth of Adam of Eden',
                'description' => '',
                'date' => [ 'year' => -3760 ]
            ],

            'burial' => [
                'name' =>'Burial of Adam of Eden',
                'description' =>'',
                'date' => null,
                'location' => [
                    'country' => 'Israel',
                    'city' => 'Hebron',
                    'place_name' => 'Cave of Machpelah',
                    'longitude' => '-86.226768',
                    'latitude' => '34.572189',
                    'country_code' => 'IL'
                ],
            ],

            'death' => [
                'name' =>'Death of Adam of Eden',
                'description' =>'',
                'date' => [ 'year' => - 2830 ],
                'location' =>null
            ],

            'current_residence' => [
                'place_name' => 'Cave on Mountain Above Garden of Eden',
                'longitude' => - 122.0928486,
                'latitude' => 37.6853373
            ],

            'location' => [
                'place_name' => 'Cave of Machpelah',
                'city'       => 'Hebron',
                'country'    => 'Israel',
                'country_code' => 'IL'
            ],

            'mugshot_urls' => [
                'large' => '',
                'medium' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_medium.jpg?hash=4cce8c1ad8c27284b6433d6c4586b16682b594af3069c61cf2af186df281e7ea.1746687599',
                'origin' => '',
                'print' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_print.jpg?hash=74300e06ab8b18658e3494317313e006fd8b2e148a101a6f3da701f28639e380.1746687599',
                'small' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_small.jpg?hash=1259133d44dcbe5e9e18da9551fcb30ccf16b3e3504584c7662c6eb8fc4d44c2.1746687599',
                'thumb' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_t.jpg?hash=77dc3069008e5a750d2a02542699b1666d000f401b94b10a82aa082c9b46ad2b.1746687599',
                'thumb2' => 'https://media.geni.com/p13/d7/2e/d9/89/53444843ef340915/biy85kuk_t2.jpg?hash=6a6f308057035d5fd53d06e692c3590cb2687845330a34af03c1fd617875b30f.1746687599'
            ],
        ],

        //======================================================

        'Seth' => [
            'birth' => [
                'name' =>'Birth of Seth .',
                'description' =>"Génesis 5\r\n" . "Reina-Valera 1960 (RVR1960)\r\n" . "Los descendientes de Adán\r\n" . "(1 Cr. 1.1-4)\r\n" . "\r\n" . "3 Y vivió Adán ciento treinta años, y engendró un hijo a su semejanza, conforme a su imagen, y llamó su nombre Set.",
                'date' => [ 'year' => - 3630 ],
                'location' => [
                    'place_name' => 'Olaha, Shinehah',
                ],
            ],

            'death' => [
                'name' =>'Death of Seth . at East of Eden', 'description' =>"Génesis 5\r\n" . "Reina-Valera 1960 (RVR1960)\r\n" . "Los descendientes de Adán\r\n" . "(1 Cr. 1.1-4)\r\n" . "\r\n" . "7 Y vivió Set, después que engendró a Enós, ochocientos siete años, y engendró hijos e hijas.\r\n" . "\r\n" . "8 Y fueron todos los días de Set novecientos doce años; y murió.",
                'date' => [ 'year' => - 2718 ],
                'location' => [
                    'place_name' => 'East of Eden'
                ],
            ],

            'mugshot_urls' => [
                'large' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_large.jpg?hash=ebfa9b296164eeb3ed6a9d87b2611ebd9af5e8f6bac3d9e3fb381282870d4a30.1746687599',
                'medium' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_medium.jpg?hash=02205ad1dfc230de836db87f5201951ea018d93ad1146dc8b9d8a4badf146921.1746687599',
                'origin' => '',
                'print' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_print.jpg?hash=8771cd12391394fd35115adab3d22699009d955fe733c1af386d9943a4ed0ded.1746687599',
                'small' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_small.jpg?hash=766d6fae0b4ca29ac75e0dbee42beca303e7b716a83dc63874a0eb4785d25b09.1746687599',
                'thumb' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_t.jpg?hash=42d3852878c9553ebcd7fa53d910166d0f94dfe7a848c8df226bcd550c489b4d.1746687599',
                'thumb2' => 'https://media.geni.com/p13/de/fa/d7/b6/5344483b71a93526/xiz95jeh_t2.jpg?hash=ae8f3b842ab2971907852385e4c684903e00d6990b9329d14adb51c20d7498dc.1746687599'
            ],
        ],

        //======================================================

        'Enosh' => [
            'birth' =>[
                'name' => 'Birth of Enosh .',
                'description' =>"Génesis 5\r\n" . "Reina-Valera 1960 (RVR1960)\r\n" . "Los descendientes de Adán\r\n" . "(1 Cr. 1.1-4)\r\n" . "\r\n" . "6 Vivió Set ciento cinco años, y engendró a Enós.",
                'date' => [ 'year' => - 3525 ],
                'location' => [
                    'place_name' => 'Shulon, East Eden'
                    ],
                ],

            'death' =>[
                'name' =>'Death of Enosh . at East of Eden',
                'description' => "Génesis 5\r\n" . "Reina-Valera 1960 (RVR1960)\r\n" . "Los descendientes de Adán\r\n" . "(1 Cr. 1.1-4)\r\n" . "\r\n" . "10 Y vivió Enós, después que engendró a Cainán, ochocientos quince años, y engendró hijos e hijas.\r\n" . "\r\n" . "11 Y fueron todos los días de Enós novecientos cinco años; y murió.",
                'date' => [ 'year' => - 2620 ],
                'location' => [
                    'place_name' => 'East of Eden'
                ],
            ],

            'mugshot_urls' => [
                'large' => '',
                'medium' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_medium.jpg?hash=6ba1ce163e7b26f845e291c49734dfb87f5ecbc4b3b0a8fa5c59ce657b382f87.1746687599',
                'origin' => '',
                'print' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_print.jpg?hash=a108f653addc10832bb570c44031ddeba11ebb78de04368e87263f6e8a4214ce.1746687599',
                'small' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_small.jpg?hash=6c210d5d6cb725e0ac6d3bf7b73010233c2e411e358650c0d9642066d4a1f263.1746687599',
                'thumb' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_t.jpg?hash=4e18202456852652a42522d878eaa6c112808bf59ed19b5f2f133386b0e0690c.1746687599',
                'thumb2' => 'https://media.geni.com/p10/2446/0735/53444837f72861c3/siv33fuk_t2.jpg?hash=842304e281c3f835518a385cd66ed23d8fb80abd62e23f697f7bba483acc2ab7.1746687599'
            ],
        ],

        //======================================================

        'Myself' => [
            'birth' =>[
                'name' =>'Birth of Shmuel-Aharon Kam (Kahn / שמואל-אהרן קם (קאן',
                'description' =>'',
                'date' => [
                    'day' => '20',
                    'month' => '6',
                    'year' => '1971'
                ],
                'location' => [
                    'country' => 'IL',
                    'city' => 'Tel-Aviv',
                    'longitude' => '34.769711',
                    'latitude' => '32.045101',
                    'country_code' => 'IL'
                ]
            ],

            'current_residence' => [
                'country' => 'Israel',
                'city' => 'Tal Menashe',
                'longitude' => '35.36124',
                'latitude' => '31.389299',
                'country_code' => 'IL',
                'formatted_location' => 'Tal Menashe, Israel'
            ],

            'location' => [
                'city' => 'Tal Menashe',
                'country' => 'Israel',
                'country_code' => 'IL',
                'latitude' => '31.389299',
                'longitude' => '35.36124',
                'formatted_location' => 'Tal Menashe, Israel'
            ],

            'mugshot_urls' => [
                'large' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_large.jpg',
                'medium' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_medium.jpg',
                'print' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_print.jpg',
                'small' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_small.jpg',
                'thumb' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_t.jpg',
                'thumb2' => 'https://media.geni.com/p13/22/c9/59/b1/5344484623782bc0/img-20171029-wa0005-1_t2.jpg'
            ],
        ],
    ];

    // =====================================================================

    /**
     * Used for the above variables to create API_Name objects, at run-time.
     * @var array <string, array <string, array <string, string|array<string>>>> $PROFILE_OBJECT_NAMES
     */
    private static $PROFILE_OBJECT_NAMES = [
        'Adam'
        => [
            'en-US' => [
                'display_name' => 'Adam of Eden',
                'first_name' => 'Adam',
                'last_name' => '.',
                'maiden_name' => '.',
                'nicknames' => [
                    'Nabi Adam'
                ]
            ],

            'he' => [
                'display_name' => 'אדם הראשון',
                'first_name' => 'אדם',
                'nicknames' => [
                    '0' => 'אדם הראשון'
                ]
            ],

            'ar' => [
                'first_name' => 'آدم'
            ],

            'ru' => [
                'first_name' => 'Адам',
                'suffix' => 'первый человек'
            ],

            'id' => [
                'nicknames' => [
                    '0' => 'Nabi 1 Adam AS',
                    '1' => ' Manusia Pertama',
                    '2' => ' Akar dari seluruh silsilah keluarga bersama Hawa [1]'
                ]
            ],

            'pt' => [
                'first_name' => 'Adão'
            ],

            'es' => [
                'first_name' => 'Adan',
                'nicknames' => [
                    '0' => 'Principe de los Ejercitos'
                ]
            ],

            'et' => [
                'display_name' => 'Adam - esimene inimene',
                'first_name' => 'Adam',
                'nicknames' => [
                    '0' => 'Esimene Inimene'
                ]
            ],

            'ms' => [
                'title' => 'Nabi',
                'first_name' => 'Adam'
            ],
        ],

        //======================================================

        'Seth'
        => [
            'en-US' => [
                'first_name' => 'Seth',
                'last_name' => '.',
                'maiden_name' => '.',
                'nicknames' => [
                    'Sis'
                ]
            ],

            'he' => [
                'first_name' => 'שת'
            ],

            'ar' => [
                'first_name' => 'شيث'
            ],

            'et' => [
                'first_name' => 'Sett'
            ],

            'es' => [
                'first_name' => 'Set',
                'nicknames' => [
                    'Uno de los Poderosos en el Mundo de los Espiritus'
                ]
            ],
        ],

        //======================================================

        'Enosh'
        => [
            'en-US' => [
                'first_name' => 'Enosh',
                'last_name' => '.',
                'maiden_name' => '.',
                'nicknames' => [
                    'Enosh',
                    'Enos',
                    'Anoosh',
                    'Anoush',
                    'Anush',
                    'Anwas'
                ]
            ],

            'he' => [
                'first_name' => 'אנוש'
            ],

            'ar' => [
                'first_name' => 'أنوش'
            ],

            'et' => [
                'first_name' => 'Enos'
            ],

            'id' => [
                'first_name' => 'Anwas'
            ],

            'es' => [
                'first_name' => 'Enos'
            ],
        ],

        //======================================================

        'Myself'
        => [
            'en-US' => [
                'display_name' => 'Shmuel-Aharon Kam (Kahn / שמואל-אהרן קם (קאן',
                'first_name' => 'Shmuel-Aharon',
                'last_name' => 'Kam',
                'maiden_name' => 'Kahn'
            ],

            'he' => [
                'display_name' => '(שמואל אהרן קם (קאן',
                'first_name' => 'שמואל-אהרן',
                'last_name' => 'קם',
                'maiden_name' => 'קאן'
            ],
        ],
    ];
}
