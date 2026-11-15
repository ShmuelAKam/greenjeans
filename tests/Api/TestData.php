<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Gender;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_Name;

class TestData
{
    /**
     * This function does any final set-up operations on the variables defined
     * below. The function is called in the bootstrap code, in GlobalSetup.php.
     */
    public static function setup(): void
    {
        self::setupGET_Events();
        self::setupSET_Events();
        self::setupBaptismEvents();

        self::setupAddProfiles();
        self::setupCurrentResidences();

        self::setupFullNames();
    }

    public static function setupGET_Events(): void
    {
        foreach (self::$testGET_Events as $name => $values)
        {
            $events = $values ['event-data'];

            foreach ($events as $eventParams)
            {
                self::$testGET_Events [$name]['events'][] = API_Event::newFromarray($eventParams);
            }
        }
    }

    public static function setupSET_Events(): void
    {
        foreach (self::$testSET_Events_DATA as $name => $events)
        {
            self::$testSET_Events [$name] = [];

            foreach ($events as $eventName => $eventParams)
            {
                self::$testSET_Events [$name][$eventName] = API_Event::newFromarray($eventParams);
            }
        }
    }

    public static function setupBaptismEvents(): void
    {
        foreach (self::$baptismEvents_DATA as $name => $event)
        {
            self::$baptismEvents [$name] = API_Event::newFromarray($event);
        }
    }

    public static function setupAddProfiles(): void
    {
        self::$addProfiles;

        foreach (self::$addProfiles as $name => $profile)
        {
            $profileArray  = reset($profile);
            $profileValues = $profileArray['values'];

            if (isset($profileValues ['current_residence']))
            {
                self::$addProfiles [$name][0]['values']['current_residence']
                    = API_Location::newFromarray($profileValues ['current_residence']);
            }

            if (isset($profileValues ['birth']))
            {
                self::$addProfiles [$name][0]['values']['birth']
                    = API_Event::newFromarray($profileValues ['birth']);
            }
        }
    }

    public static function setupCurrentResidences(): void
    {
        foreach (self::$currentResidences_DATA as $location)
        {
            self::$currentResidences [] = [ API_Location::newFromarray($location) ];
        }
    }

    public static function setupFullNames(): void
    {
        foreach (self::$fullNames_DATA as $testName => $names)
        {
            self::$fullNames [$testName] = [ API_Name::newFromarray($names) ];
        }
    }

    // =====================================================================

    /**
     * IDs of test profiles that EXIST on the server.
     *
     * @var array <string, string> $testProfileIDs Used by API_ProfileTest & API_ProfileTestUpdate
     */
    public static $testProfileIDs = [
        'Adam'  => 'profile-34838408159',
        'Seth'  => 'profile-34838408177',
        'Enosh' => 'profile-34838408195'
    ];

    /** @var array <string, array <string, mixed>> $testGET_Events  Used by API_ProfileTest */
    public static $testGET_Events = [
        "Adam" => [
            "id"         => "profile-53597431",
            "name"       => "Adam of Eden",
            'events'     => [],  // Will be filled with API_Event objects by setup function,
            "event-data" => [   // using the data in this entry, which will then be deleted.
                1 => [
                    'name' => "נולד אדם הראשון Born Adam First",
                    'description' => "אירוע בכרונולוגיה המסורתית\r\n\r\nיצירת אדם הראשון\r\n\r\nתאריך עברי\r\n\r\n2 - ב (בראש השנה)\r\n\r\nתאריך לועזי\r\nלפי הכרונולוגיה המסורתית\r\n\r\n3759 לפנה\"ס\r\n\r\nתאריך לועזי\r\nלפי המחקר ההיסטורי\r\n\r\n?\r\n\r\nThe traditional chronology event\r\n\r\nCreating Adam\r\n\r\nHebrew date\r\n\r\n2 - B (Rosh Hashanah)\r\n\r\nGregorian Date\r\nAccording to the traditional chronology\r\n\r\n3759 BC\r\n\r\nGregorian Date\r\nAccording to historical research\r\n\r\n?\r\n",
                    'date' => [ 'year' => -3759 ],
                    'location' => null,
                ],
                2 => [
                    'name' => "Biblical creation myth",
                    'description' => "creation",
                    'date' => [ 'year' => -4004 ],
                    'location' => null,
                ],
                3 => [
                    'name' => "Burial of Adam of Eden",
                    'description' => '',
                    'date' => null,
                    'location' => [
                        "place_name" => "Cave of Machpelah",
                        "city" => "Hebron",
                        "country" => "Israel",
                        "country_code" => "IL",
                        "latitude" => 34.572189,
                        "longitude" => -86.226768
                    ]],
                4 => [
                    'name' => "Marriage of Adam and Eve .",
                    'description' => '',
                    'date' => null,
                    'location' => [
                        "place_name" => "Garden Of Eden" ]
                ],
                5 => [
                    'name' => "Death of (No Name)",
                    'description' => '',
                    'date' => null,
                    'location' => null,
                ],
                6 => [
                    'name' => "Death of Norn DS",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                7 => [
                    'name' => "Birth of Adam of Eden",
                    'description' => '',
                    'date' => [ 'year' => -3760 ],
                    'location' => null
                ],
                8 => [
                    'name' => "Death of Adam of Eden",
                    'description' => '',
                    'date' => [ 'year' => -2830 ],
                    'location' => null
                ],
                9 => [
                    'name' => "Birth of Azura .",
                    'description' => '',
                    'date' => null,
                    'location' => [ "place_name" => "Olaha,Shinehah,," ],
                ],
                10 => [
                    'name' => "Birth of Seth .",
                    'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n3 Y vivió Adán ciento treinta años, y engendró un hijo a su semejanza, conforme a su imagen, y llamó su nombre Set.",
                    'date' => [ 'year' => -3630 ],
                    'location' => [ "place_name" => "Olaha, Shinehah" ],
                ],
                11 => [
                    'name' => "Birth of Awan .",
                    'description' => '',
                    'date' => null,
                    'location' => [ "place_name" => "Olaha (Shinehah)", ],
                ],
                12 => [
                    'name' => "Birth of Cain .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                13 => [
                    'name' => "Birth of Abel .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
            ],
        ],

        "Seth" => [
            "id" => "profile-4095031",
            "name" => "Seth .",
            'events'     => [],  // Will be filled with API_Event objects by setup function,
            "event-data" => [   // using the data in this entry, which will then be deleted.
                1 => [
                    'name' => "Death of (No Name)",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                2 => [
                    'name' => "Death of (No Name)",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                3 => [
                    'name' => "Burial of Seth .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                4 => [
                    'name' => "Death of Seth . at East of Eden",
                    'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n7 Y vivió Set, después que engendró a Enós, ochocientos siete años, y engendró hijos e hijas.\r\n\r\n8 Y fueron todos los días de Set novecientos doce años; y murió.",
                    'date' => [ 'year' => -2718 ],
                    'location' => [ "place_name" => "East of Eden" ]
                ],
                5 => [
                    'name' => "Marriage of Seth and Azura .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                6 => [
                    'name' => "Birth of Seth .",
                    'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n3 Y vivió Adán ciento treinta años, y engendró un hijo a su semejanza, conforme a su imagen, y llamó su nombre Set.",
                    'date' => [ 'year' => -3630 ],
                    'location' => [ "place_name" => "Olaha, Shinehah" ],
                ],
                7 => [
                    'name' => "Birth of Other Sons .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                8 => [
                    'name' => "Birth of Enosh .",
                    'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n6 Vivió Set ciento cinco años, y engendró a Enós.",
                    'date' => [ 'year' => -3525 ],
                    'location' => [ "place_name" => "Shulon, East Eden" ],
                ],
                9 => [
                    'name' => "Birth of Noam .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                10 => [
                    'name' => "Birth of Niba .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                11 => [
                    'name' => "Birth of Edna .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                12 => [
                    'name' => "Birth of Thila .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                13 => [
                    'name' => "Birth of Fonna .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                14 => [
                    'name' => "Birth of Matha .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                15 => [
                    'name' => "Birth of Malida .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                16 => [
                    'name' => "Birth of Elidia .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                17 => [
                    'name' => "Birth of Other Daughters .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
            ]
        ],

        "Enosh" => [
            "id" => "profile-4094989",
            "name" => "Enosh .",
            'events'     => [],  // Will be filled with API_Event objects by setup function,
            "event-data" => [   // using the data in this entry, which will then be deleted.
                1 => [
                    'name' => "Burial of Enosh .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                2 => [
                    'name' => "Birth of Enosh .",
                    'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n6 Vivió Set ciento cinco años, y engendró a Enós.",
                    'date' => [ 'year' => -3525 ],
                    'location' => [ "place_name" => "Shulon, East Eden" ],
                ],
                3 => [
                    'name' => "Death of Enosh . at East of Eden",
                    'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n10 Y vivió Enós, después que engendró a Cainán, ochocientos quince años, y engendró hijos e hijas.\r\n\r\n11 Y fueron todos los días de Enós novecientos cinco años; y murió.",
                    'date' => [ 'year' => -2620 ],
                    'location' => [ "place_name" => "East of Eden" ],
                ],
                4 => [
                    'name' => "Death of (No Name)",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                5 => [
                    'name' => "Death of (No Name)",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                6 => [
                    'name' => "Death of (No Name)",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                7 => [
                    'name' => "Death of (No Name)",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                8 => [
                    'name' => "Marriage of Enosh and Noam .",
                    'description' => '',
                    'date' => [ 'year' => -3692 ],
                    'location' => [ "place_name" => "Adam-Ondi-Ahman" ],
                ],
                9 => [
                    'name' => "Birth of Cainan .",
                    'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n9 Vivió Enós noventa años, y engendró a Cainán.",
                    'date' => null,
                    'location' => [ "place_name" => "East Of Eden" ],
                ],
                10 => [
                    'name' => "Birth of Mualeleth .",
                    'description' => '',
                    'date' => null,
                    'location' => [ "place_name" => "Shulon East Eden" ],
                ],
                11 => [
                    'name' => "Birth of Barakiel .",
                    'description' => '',
                    'date' => null,
                    'location' => [ "place_name" => "East Of Eden" ],
                ],
                12 => [
                    'name' => "Birth of Rachujal",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
                13 => [
                    'name' => "Birth of Phoe .",
                    'description' => '',
                    'date' => [ 'year' => -3349 ],
                    'location' => null
                ],
                14 => [
                    'name' => "Birth of Catennath .",
                    'description' => '',
                    'date' => [ 'year' => -3339 ],
                    'location' => null
                ],
                15 => [
                    'name' => "Birth of Thaal .",
                    'description' => '',
                    'date' => null,
                    'location' => null
                ],
            ]
        ]
    ];

    // ==============================================================================

    /** @var array <API_Event> $testSET_Events */
    public static $testSET_Events = [];

    /** @var array <string, array <string, mixed>> $testSET_Events_DATA */
    private static $testSET_Events_DATA = [
        "Adam" => [
            'Birth' => [
                'name' => "Birth of Adam of Eden",
                'description' => 'Let us make man in our image',
                'date' => [ 'year' => -3760 ],
                'location' => null
            ],
            'Death' => [
                'name' => "Death of Adam of Eden",
                'description' => '',
                'date' => [ 'year' => -2830 ],
                'location' => null
            ],
            'Burial' => [
                'name' => "Burial of Adam of Eden",
                'description' => '',
                'date' => null,
                'location' => [
                    "place_name" => "Cave of Machpelah",
                    "city" => "Hebron",
                    "country" => "Israel"
                ]
            ]
        ],

        "Seth" => [
            'Birth' => [
                'name' => "Birth of Seth .",
                'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n3 Y vivió Adán ciento treinta años, y engendró un hijo a su semejanza, conforme a su imagen, y llamó su nombre Set.",
                'date' => [ 'year' => -3630 ],
                'location' => [ "place_name" => "Olaha, Shinehah" ]
            ],
            'Death' => [
                'name' => "Death of Seth . at East of Eden",
                'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n7 Y vivió Set, después que engendró a Enós, ochocientos siete años, y engendró hijos e hijas.\r\n\r\n8 Y fueron todos los días de Set novecientos doce años; y murió.",
                'date' => [ 'year' => -2718 ],
                'location' => [ "place_name" => "East of Eden" ]
            ],
        ],

        "Enosh" => [
            'Birth' => [
                'name' => "Birth of Enosh .",
                'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n6 Vivió Set ciento cinco años, y engendró a Enós.",
                'date' => [ 'year' => -3525 ],
                'location' => [ "place_name" => "Shulon, East Eden" ]
            ],
            'Death' => [
                'name' => "Death of Enosh . at East of Eden",
                'description' => "Génesis 5\r\nReina-Valera 1960 (RVR1960)\r\nLos descendientes de Adán\r\n(1 Cr. 1.1-4)\r\n\r\n10 Y vivió Enós, después que engendró a Cainán, ochocientos quince años, y engendró hijos e hijas.\r\n\r\n11 Y fueron todos los días de Enós novecientos cinco años; y murió.",
                'date' => [ 'year' => -2620 ],
                'location' => [ "place_name" => "East of Eden" ]
            ]
        ]
    ];

    // ==============================================================================

    /** @var array <API_Event> $baptismEvents  Used by API_ProfileTestUpdate */
    public static $baptismEvents = [];

    /** @var array <array <string, array <string, mixed>>> $baptismEvents_DATA */
    private static $baptismEvents_DATA = [
        [
            'Yoshke'
            => [
                'name' => "Bap Bap",
                'description' => '',
                'date' => [ 'month' => 6, 'year' => 1971 ],
                'location' => [
                    "place_name" => "Trump Tower",
                    "city" => "New York",
                    "country" => "USA"
                ]
            ],
            'Dingbat'
            => [
                'name' => "Tism Tism",
                'description' => '',
                'date' => null,
                'location' => [
                    "place_name" => "City square",
                    "city" => "Vatican City"
                ]
            ],
            'Superman'
            => [
                'name' => "Holy Dunken Donuts",
                'description' => '',
                'date' => null,
                'location' => [
                    "place_name" => "Crystal Castle",
                    "city" => "North Pole"
                ]
            ]
        ]
    ];

    // ==============================================================================

    /** @var array <string, array <string, array <string, string>>> $setProfiles  Used by API_ProfileTest */
    public static $setProfiles = [
        'Adam' => [
            'values' => [
                'cause_of_death' => 'Sin',
                'email' => "adam@eden.com",
                'merge_note' => "Don't do it!",
                'occupation' => 'What occupation?'
            ]
        ],

        'Seth' => [
            'values' => [
                'cause_of_death' => 'Plague',
                'email' => "seth@westofeden.org",
                'merge_note' => "He did it!",
                'occupation' => 'Free Kashmir!'
            ]
        ],

        'Enosh' => [
            'values' => [
                'cause_of_death' => 'Banana peel',
                'email' => "enosh@humanity.com",
                'merge_note' => "Oh No!",
                'occupation' => "No no no. We will not let you go."
            ]
        ]
    ];

    /** @var array <string, array <string, mixed>> $setProfilesNames  Used by API_ProfileTest */
    public static $setProfilesNames = [
        'Adam' => [
            'names' => [
                'first_name' => 'Adama',
                'middle_name' => 'Middler',
                'last_name' => 'Lustig',
                'maiden_name' => 'Cohen',
                'suffix' => '',
                'title' => '',
                'display_name' => 'The Daring Dudette',

                'nicknames' => [
                    'Adam',
                    'First Man',
                    'Father of All'
                ]
            ]
        ],

        'Seth' => [
            'names' => [
                'first_name' => 'Yo',
                'middle_name' => 'Mamma',
                'last_name' => 'So-Fat',
                'maiden_name' => 'She',
                'suffix' => 'Sits',
                'title' => 'Around',
                'display_name' => '',

                'nicknames' => []
            ]
        ],

        'Enosh' => [
            'names' => [
                'first_name' => 'His Honor',
                'middle_name' => 'Master',
                'last_name' => 'B.',
                'maiden_name' => 'Ater',
                'suffix' => 'Jr.',
                'title' => 'Prix',
                'display_name' => 'One and Only',

                'nicknames' => [
                    'Enosh',
                    'Enos',
                    'Anoosh',
                    'Anoush',
                    'Anush',
                    'Anwas'
                ]
            ]
        ]
    ];

    /** @var array <string, array <array <string, mixed>>> $addProfiles  Used by API_ProfileTest */
    public static $addProfiles = [
        "Lilith" => [
            [
                'values' => [
                    'first_name' => 'Lilith',
                    'last_name' => '.',
                    'maiden_name' => 'Temptress',
                    'public' => '1',
                    'gender' => API_Gender::Female,
                    'current_residence' => [  'place_name' => 'Hell' ],
                    'about_me' => "Test profile. Please ignore.\r\n"
                ]
            ]
        ],

        "Baal-Zebuv" => [
            [
                'values' => [
                    'first_name' => 'Baal-Zebuv',
                    'public' => '1',
                    'is_alive' => 0,
                    'gender' => API_Gender::Male,
                    'current_residence' =>  ['place_name' => 'Unknown' ],
                    'about_me' => "Test profile. Please ignore.\r\n"
                ]
            ]
        ],

        "Serpent" => [
            [
                'values' => [
                    'first_name' => 'Serpent',
                    'middle_name' => 'Sneaky',
                    'last_name' => 'Bastard',
                    'public' => '1',
                    'gender' => API_Gender::Male,
                    'birth' => [
                        'name' => 'Birth of Snake',
                        'description' => 'Genesis',
                        'date' => [ 'year' => - 3760 ],
                        'location' => null,
                    ],
                    'about_me' => "Test profile. Please ignore.\r\n"
                ]
            ]
        ]
    ];

    /** @var array <API_Location> $currentResidences  Used by API_ProfileTest & API_ProfileTestUpdate */
    public static $currentResidences = [];

    /** @var array <array <string, string>> $currentResidences_DATA */
    private static $currentResidences_DATA = [
        [
            'place_name' => 'Bet Haddasa',
            'city' => 'Hebron',
            'country' => 'Israel'
        ],
        [
            'street_address1' => 'Ahad Haam 21',
            'city' => 'Rehovot',
            'country' => 'Israel'
        ],
        [
            'street_address1' => 'Nahar Hayarkon 38',
            'city' => 'Bet Shemesh',
            'country' => 'Israel'
        ],
        [
            'street_address1' => 'Peter Pan Ave. 1',
            'city' => 'Never',
            'country' => 'Never Land'
        ],
        [
            'place_name' => "Patrician's Palace",
            'street_address1' => 'City Square',
            'city' => 'Ankh-Morpork',
            'country' => 'Discworld'
        ]
    ];

    /** @var array <array <array <string>>> $nicknames  Used by API_ProfileTestUpdate */
    public static $nicknames = [
        [
            [
                'eeny',
                'meany',
                'miny',
                'mo'
            ]
        ],
        [
            [
                'Sneezy',
                'Sleepy',
                'Grumpy',
                'Dopey',
                'Doc',
                'Happy',
                'Bashful'
            ]
        ],
        [
            [
                'Toshke',
                'The Athlete',
                'Billy-Bob'
            ]
        ],
        [
            [
                'Who',
                'the',
                'Hell',
                'Is',
                'This'
            ]
        ],
        [
            []
        ]
    ];

    /** @var array <string, API_Name> $fullNames  Used by API_ProfileTestUpdate */
    public static $fullNames = [];

    /** @var array <string, array <string, string>> $fullNames_DATA */
    private static $fullNames_DATA = [
        'first_name'       => [
            'first_name'   => 'first_name'
        ],
        'middle_name'      => [
            'middle_name'  => 'middle_name'
        ],
        'last_name'        => [
            'last_name'    => 'last_name'
        ],
        'maiden_name'      => [
            'maiden_name'  => 'maiden_name'
        ],
        'suffix'           => [
            'suffix'       => 'Sussex' // Can't use 'suffix' as test data.
        ],
        'title'             => [
            'title'         => 'tittles'  // Can't use 'title' as test data.
        ],
        'display_name'     => [
            'display_name' => 'display_name'
        ],
        'first & middle'   => [
            'first_name'   => 'first_name',
            'middle_name'  => 'middle_name'
        ],
        'last & maiden'    => [
            'last_name'    => 'last_name',
            'maiden_name'  => 'maiden_name'
        ],
        'first & last'     => [
            'first_name'   => 'first_name',
            'last_name'    => 'last_name'
        ],
        'first middle last' => [
            'first_name'   => 'first_name',
            'middle_name'  => 'middle_name',
            'last_name'    => 'last_name'
        ],
        'all' => [
            'first_name'    => 'first_name',
            'middle_name'   => 'middle_name',
            'last_name'     => 'last_name',
            'maiden_name'   => 'maiden_name',
            'suffix'        => 'Sussex', // Can't use 'suffix' as test data.
            'title'         => 'tittles', // Can't use 'title' as test data.
            'display_name' => 'display_name'
        ]
    ];

    /** @var array <array <string, string|array <string, string>>> $detailStrings  Used by API_ProfileTest */
    public static $detailStrings = [
        [
            // 'name' => 'Seth .',
            // 'guid' => '6000000006375916501',
            // 'detail_strings' => array (
            // 'en-US' => "* [http://mechon-mamre.org/p/pt/pt0104.htm#25 Genesis 4:25-26] * [http://mechon-mamre.org/p/pt/pt0105.htm#3 Genesis 5:3-8] lists his place in the ten generations between Adam & Noah. Normally genealogies are traced by the first-born sons, but Cain killed Abel and then was banished, so Seth became almost like a new “first-born”. * [http://www.biblestudytools.com/history/flavius-josephus/antiquities-jews/book-1/chapter-2.html Antiquities of the Jews, Book 1 Chapter 2:3.] Wikipedia: * [http://en.wikipedia.org/wiki/Seth Seth] and [http://he.wikipedia.org/wiki/%D7%A9%D7%AA שת]. * [https://en.wikipedia.org/wiki/Seth#Islam Seth in Islam.] Seth, 3rd son of Adam & Eve, was born after Cain murdered Abel. So the family line is continued through him. His name denoted this being derived from the root meaning \"appointed-given\" instead of his brother(s), as Eve herself explains. ---- See also: [http://www.geni.com/profile/index/6000000003645707049 Seth Sat-naal] ",
            // 'es' => "Su nombre significa sustisución. Fue el hijo que Dios le dio a Adán y Eva después de la muerte de su hijo menor a manos de Caín. (Gén. 4:8). (c. 3874-2962 a. de J.C.). Adán tenía 130 años cuando nació su hijo, al que llamó Set, y que era parecido a él en todo. Después de esto, Adán vivió 800 años más, y tuvo otros hijos e hijas; así que vivió 930 años en total. A esa edad murió. . . Set tenía 105 años cuando nació su hijo Enós. Después de esto, Set vivió 807 años más, y tuvo otros hijos e hijas, así que vivió 912 años en total. A esa edad murió. . . Enós tenía noventa años cuando nació su hijo Cainán. Después de esto, Enós vivió 815 años más, y tuvo otros hijos e hijas; así que vivió 905 años en total. A esa edad murió. . . Cainán tenía setenta años cuando nació su hijo Mahalaleel. Después de esto, Cainán vivió 840 años más, y tuvo otros hijos e hijas; así que vivió 910 años en total. A esa edad murió. . . Mahalaleel tenía 65 años cuando nació su hijo Jared. Después de esto, Mahalaleel vivió 830 años más, y tuvo otros hijos e hijas; así que vivió 895 años en total. A esa edad murió. . . Jared tenía 162 años cuando nació su hijo Enoc. Después de esto, Jared vivió 800 años más, y tuvo otros hijos e hijas; así que vivió 972 años en total. A esa edad murió. . . Enoc tenía 65 años de edad cuando nació su hijo Matusalén. Enoc vivió de acuerdo con la voluntad de Dios. Después que Matusalén nació, Enoc vivió 300 años más, y tuvo otros hijos e hijas; así que vivió 365 años en total. Como Enoc vivió de acuerdo con la voluntad de Dios, un día desapareció porque Dios se lo llevó. . . Matusalén tenía 187 años cuando nació su hijo Lamec. Después de esto, Matusalén vivió 782 años más, y tuvo otros hijos e hijas; así que vivió 969 años en total. A esa edad murió. (Gén. 5:3-27).",
            // 'et' => "* [http://mechon-mamre.org/p/pt/pt0104.htm#25 Genesis 4:25-26] * [http://mechon-mamre.org/p/pt/pt0105.htm#3 Genesis 5:3-8] lists his place in the ten generations between Ja Aadam sai taas ühte oma naisega, kes tõi ilmale poja ja pani temale nimeks Sett, öeldes: „Jumal andis mulle teise järeltulija Aabeli asemele, kuna Kain ta tappis.” (1Ms 4:25) Kui Aadam oli elanud sada kolmkümmend aastat, siis sündis temale poeg, kes oli tema sarnane, tema nägu, ja ta pani sellele nimeks Sett. Ja Aadama elupäevi oli pärast Seti sündimist kaheksasada aastat, ja temale sündis poegi ja tütreid. (1Ms 5:3-4) Adam & Noah. Normally genealogies are traced by the first-born sons, but Cain killed Abel and then was banished, so Seth became almost like a new “first-born”. * [http://www.biblestudytools.com/history/flavius-josephus/antiquities-jews/book-1/chapter-2.html Antiquities of the Jews, Book 1 Chapter 2:3.] Wikipedia: [http://en.wikipedia.org/wiki/Seth Seth] and [http://he.wikipedia.org/wiki/%D7%A9%D7%AA שת]. See also (but don't merge): [http://www.geni.com/profile/index/6000000003645707049 Seth Sat-naal] Seth, 3rd son of Adam & Eve, was born after Cain murdered Abel. So the family line is continued through him. His name denoted this being derived from the root meaning \"appointed-given\" instead of his brother(s), as Eve herself explains. -------------------- Set var Adams och Evas tredje son. Han föddes efter det att Kain dödat Abel. Set omnämns i Första Mosebok kap. 4:25-26 och 5:3. -------------------- Su nombre significa sustisución. Fue el hijo que Dios le dio a Adán y Eva después de la muerte de su hijo menor a manos de Caín. (Gén. 4:8). (c. 3874-2962 a. de J.C.). -------------------- '''''Adán''''' tenía 130 años cuando nació su hijo, al que llamó Set, y que era parecido a él en todo. Después de esto, Adán vivió 800 años más, y tuvo otros hijos e hijas; así que vivió 930 años en total. A esa edad murió. . . '''''Set''''' tenía 105 años cuando nació su hijo Enós. Después de esto, Set vivió 807 años más, y tuvo otros hijos e hijas, así que vivió 912 años en total. A esa edad murió. . . '''''Enós''''' tenía noventa años cuando nació su hijo Cainán. Después de esto, Enós vivió 815 años más, y tuvo otros hijos e hijas; así que vivió 905 años en total. A esa edad murió. . . '''''Cainán''''' tenía setenta años cuando nació su hijo Mahalaleel. Después de esto, Cainán vivió 840 años más, y tuvo otros hijos e hijas; así que vivió 910 años en total. A esa edad murió. . . ''''''''Mahalaleel'''''''' tenía 65 años cuando nació su hijo Jared. Después de esto, Mahalaleel vivió 830 años más, y tuvo otros hijos e hijas; así que vivió 895 años en total. A esa edad murió. . . '''Jared''' tenía 162 años cuando nació su hijo Enoc. Después de esto, Jared vivió 800 años más, y tuvo otros hijos e hijas; así que vivió 972 años en total. A esa edad murió. . . '''''Enoc''''' tenía 65 años de edad cuando nació su hijo Matusalén. Enoc vivió de acuerdo con la voluntad de Dios. Después que Matusalén nació, Enoc vivió 300 años más, y tuvo otros hijos e hijas; así que vivió 365 años en total. '''Como Enoc vivió de acuerdo con la voluntad de Dios, un día desapareció''' '''porque Dios se lo llevó. . .''' '''''Matusalén''''' tenía 187 años cuando nació su hijo Lamec. Después de esto, Matusalén vivió 782 años más, y tuvo otros hijos e hijas; así que vivió 969 años en total. A esa edad murió. (Gén. 5:3-27). -------------------- Seth was the third son of Adam and Eve brother of Cain and Abel, who were the only other of their children mentioned by name. According to Genesis 4:25, Seth was born after the slaying of Abel by Cain, and Eve believed God had appointed him as a replacement for Abel. -------------------- Age 972 yrs. -------------------- Seth (Hebrew: שֵׁתֿ, Standard Šet, Tiberian Šēṯ; Arabic: شيث‎ Sheeth; placed; appointed\"), in Judaism, Christianity and Islam, was the third son of Adam and Eve and brother of Cain and Abel, who were the only other of their children mentioned by name in the Tanakh (Old Testament). According to Genesis 4:25, Seth was born after Abel's murder, and Eve believed God had appointed him as a replacement for Abel. According to Genesis, Seth was born when Adam was 130 years old[1] \"a son in his likeness and image.\"[1] The genealogy is repeated at 1 Chronicles 1:1–3. Genesis 5:4–5 states that Adam fathered \"sons and daughters\" before his death, aged 930 years. According to the Bible, Seth lived to the age of 912.[2] Jewish tradition Seth figures in The Life of Adam and Eve, also known, in its Greek version, as the Apocalypse of Moses, a Jewish pseudepigraphical group of writings. It recounts the lives of Adam and Eve from after their expulsion from the Garden of Eden to their deaths. The surviving versions were composed from the early 3rd to the 5th century,[3]:252 the literary units in the work are considered to be older and predominantly of Jewish origin.[4] There is wide agreement that the original was composed in a Semitic language[3]:251 in the 1st century AD/CE.[3]:252 In the Greek versions Seth and Eve travel to the doors of the Garden to beg for some oil of the tree of mercy (i.e. the Tree of Life). On the way Seth is attacked and bitten by a wild beast, which goes away when ordered by Seth. Michael refuses to give them the oil at that time, but promises to give it at the end of time, when all flesh will be raised up, the delights of paradise will be given to the holy people and God will be in their midst. On their return, Adam says to Eve: \"What hast thou done? Thou hast brought upon us great wrath which is death.\" (chapters 5-14) Later only Seth can witness the taking-up of Adam at his funeral in a divine chariot, which deposits him in the Garden of Eden. Rashi (Rabbi Shlomo Yitzhaqi) refers to Seth as the ancestor of Noah and hence the father of all mankind, all other humans having perished in the Great Flood. In gnosticism, Seth is seen as a replacement given by God for Abel, whom Cain had slain. It is said that late in life, Adam gave Seth secret teachings that would become the kabbalah. The Zohar refers to Seth as \"ancestor of all the generations of the tzaddikim\" (Hebrew: righteous ones).[5] According to Seder Olam Rabbah, based on Jewish reckoning, he was born in 130 AM. According to Aggadah, he had 33 sons and 23 daughters. According to the Seder Olam Rabbah, he died in 1042 AM. According to Josephus In the Antiquities of the Jews, Josephus refers to Seth as virtuous and of excellent character, and reports that his descendants invented the wisdom of the heavenly bodies, and built the \"pillars of the sons of Seth\", two pillars inscribed with many scientific discoveries and inventions, notably in astronomy. They were built by Seth's descendants based on Adam's prediction that the world would be destroyed at one time by fire and another time by global flood, in order to protect the discoveries and be remembered after the destruction. One was composed of brick, and the other of stone, so that if the pillar of brick should be destroyed, the pillar of stone would remain, both reporting the ancient discoveries, and informing men that a pillar of brick was also erected. Josephus reports that the pillar of stone remained in the land of Siriad in his day. William Whiston, a 17/18th century translator of the Antiquities, stated in a footnote that he believed Josephus mistook Seth for Sesostris, king of Egypt, the erector of the pillar in Siriad (being a contemporary name for the territories in which Sirius was venerated (i.e., Egypt). He stated that there was no way for any pillars of Seth to survive the deluge, because the deluge buried all such pillars and edifices far underground in the sediment of its waters. The Perennialist writer Nigel Jackson identifies the land of Siriad in Josephus' account with Syria, citing related Mandaean legends regarding the 'Oriental Land of Shyr' in connection with the visionary mytho-geography of the prophetic traditions surrounding Seth.[13]('On the Prophethood of Seth in the Abrahamic Traditions', Sacred Web volume 25, Summer 2010)",
            // 'sv' => "Set var Adams och Evas tredje son. Han föddes efter det att Kain dödat Abel. Set omnämns i Första Mosebok kap. 4:25-26 och 5:3.",
            // )

            'name' => 'Enosh .',
            'guid' => '6000000000190131271',
            'detail_strings' => [
                'en-US' => "* [http://mechon-mamre.org/p/pt/pt0104.htm#26 Genesis 4:26.]\r\n" . "* [http://mechon-mamre.org/p/pt/pt0105.htm#6 Genesis 5:6-11.]\r\n" . "* [https://mechon-mamre.org/p/pt/pt25a01.htm#1 1 Chronicles 1:1.]\r\n" . "* [http://www.pseudepigrapha.com/jubilees/4.htm Book of Jubilees: 4:11-13.]\r\n\r\n" . "Wikipedia: [http://en.wikipedia.org/wiki/Enos_%28Bible%29 Enos]\r\n\r\n" . "See also (don't merge): [http://www.geni.com/profile/index/6000000003645882249 Henokh]\r\n\r\n" . "He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry),\r\n\r\n" . "Ethiopian Orthodox tradition considers him a \"faithful and righteous servant of God\", and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, \"as an instrument for codifying the laws\".\r\n\r\n" . "He is commemorated as one of the Holy Forefathers in the Calendar of Saints of the Armenian Apostolic Church on July 30.\r\n\r\n" . "According to the Book of Jubilees (4:11-13) in the Ethiopian Orthodox Bible, Enos was born in the Year of the World 235, and \"he began to call on the name of the Lord on the earth.\" He married his sister, No'am (this necessary practice was discontinued after his generation, once there were more distant relations available to marry), and she bare him Kenan in the year 325. Ethiopian Orthodox tradition considers him a \"faithful and righteous servant of God\", and further credits him with the introduction, following a divine revelation, of the Ge'ez alphabet in its original, consonant-only form, \"as an instrument for codifying the laws\"[1].\r\n\r\n" . "Enos in Hinduism is mentioned as Anoohan and is considered being the son of Seth (Shwetha-nama)[citation needed]. The text carried about Enoch was mentioned in the last Purana the Bhavishya Purana which was summarised text done in Sanskrit and attributed to Rishi Vyasa, the compiler of the Vedas.\r\n",

                'es' => "[http://mechon-mamre.org/p/pt/pt0105.htm#6 Genesis 5:6,9-11]\r\n\r\n" . "Hijo de Set, de la generación de Adán. Nieto de nuestros primeros padres; fue padre Cainán y de Kenan y tuvo otros hijos e hijas. Alcanzó a vivir hasta la séptima generación de su descendencia. (c. 3769-2864 a. de J.C.). Desde entonces se comenzó a invocar el nombre del Señor. (Gén. 4:26). . . \r\n\r\n" . "--------------------\r\n" . "Y a Set también le nació un hijo, y llamó su nombre Enós. Entonces los hombres comenzaron a invocar el nombre de Jehová.",

                'et' => "[http://mechon-mamre.org/p/pt/pt0105.htm#6 Genesis 5:6,9-11]\r\n\r\n" . "Wikipedia: [http://en.wikipedia.org/wiki/Enos_%28Bible%29 Enos]\r\n\r\n" . "Ja Setile sündis ka poeg, ja ta pani temale nimeks Enos. Sel ajal hakati Issanda nime appi hüüdma. (1Ms 4:26)\r\n\r\n" . "Kui Sett oli elanud sada viis aastat, siis sündis temale Enos. \r\n\r\n" . "Ja Sett elas pärast Enose sündimist kaheksasada seitse aastat, ja temale sündis poegi ja tütreid. \r\n\r\n" . "Nõnda oli kõiki Seti elupäevi üheksasada kaksteist aastat; siis ta suri. \r\n\r\n" . "Kui Enos oli elanud üheksakümmend aastat, siis sündis temale Keenan. \r\n\r\n" . "Ja Enos elas pärast Keenani sündimist kaheksasada viisteist aastat, ja temale sündis poegi ja tütreid. \r\n\r\n" . "Nõnda oli kõiki Enose elupäevi üheksasada viis aastat; siis ta suri.  (1Ms 5:6-11)"
            ]
        ],
        [
            'name' => 'Cainan .',
            'guid' => '6000000000190220038',
            'detail_strings' => [
                'en-US' => "* [http://mechon-mamre.org/p/pt/pt0105.htm#9 Genesis 5:9, 12-14]\r\n" . "* [https://mechon-mamre.org/p/pt/pt25a01.htm#2 1 Chronicles 1:2.]\r\n" . "* [https://sacred-texts.com/chr/apo/jasher/2.htm Book of Jasher 2:10-17.] Lists his other children.\r\n\r\n" . "Wikipedia: [http://en.wikipedia.org/wiki/Kenan Kenan] and [http://he.wikipedia.org/wiki/%D7%A7%D7%99%D7%A0%D7%9F קינן]\r\n\r\n",
                'et' => "[http://mechon-mamre.org/p/pt/pt0105.htm#9 Genesis 5:9, 12-14]\r\n\r\n" . "Wikipedia: [http://en.wikipedia.org/wiki/Kenan Kenan] and [http://he.wikipedia.org/wiki/%D7%A7%D7%99%D7%A0%D7%9F קינן]\r\n\r\n" . "Kui Enos oli elanud üheksakümmend aastat, siis sündis temale Keenan. (1Ms 5:9)",
                'he' => "[http://mechon-mamre.org/p/pt/pt0105.htm#9 Genesis 5:9, 12-14]\r\n\r\n" . "Wikipedia: [http://en.wikipedia.org/wiki/Kenan Kenan] and [http://he.wikipedia.org/wiki/%D7%A7%D7%99%D7%A0%D7%9F קינן]"
            ]
        ]
    ];

    /** @var array <string, mixed> $adamPathArray */
    public static $adamPathArray = [
        'inlaw_distance' => 0,
        'relations' => [
            [
                'name' => "Adam of Eden",
                'url' => "https://www.geni.com/api/profile-53597431"
            ],
            [
                'name' => "Seth .",
                'relation' => "son",
                'url' => "https://www.geni.com/api/profile-4095031"
            ],
            [
                'name' => "Enosh .",
                'relation' => "son",
                'url' => "https://www.geni.com/api/profile-4094989"
            ],
            [
                'name' => "Cainan .",
                'relation' => "son",
                'url' => "https://www.geni.com/api/profile-4094860"
            ]
        ],
        'relationship' => "great grandson",
        'status' => "done",
        'step_count' => 3,
        'url' => "https://www.geni.com/path/Adam-of-Eden+is+related+to+Cainan?from=6000000003538706117&path_type=blood&to=6000000000190220038"
    ];

    /** @var array <string, mixed> $noahPathArray */
    public static $noahPathArray = [
        'inlaw_distance' => 1,
        'relations' => [
            [
                'name' => "Noah .",
                'url' => "https://www.geni.com/api/profile-3917277"
            ],
            [
                'name' => "Naamah .",
                'relation' => "wife",
                'url' => "https://www.geni.com/api/profile-4094581"
            ],
            [
                'name' => "Enoch / Idris",
                'relation' => "father",
                'url' => "https://www.geni.com/api/profile-3917300"
            ],
            [
                'name' => "Jared .",
                'relation' => "father",
                'url' => "https://www.geni.com/api/profile-3917310"
            ]
        ],

        'relationship' => "wife's grandfather",
        'status' => "done",
        'step_count' => 3,
        'url' => "https://www.geni.com/path/Noah+is+related+to+Jared?from=6000000002145556219&path_type=inlaw&to=6000000001048812668"
    ];

    /** @var array <string, mixed> $shmuelPathArray */
    public static $shmuelPathArray = [
        'inlaw_distance' => 0,
        'relations' => [
            [
                'name' => "Shmuel-Aharon Kam (Kahn / שמואל-אהרן קם (קאן",
                'url' => "https://www.geni.com/api/profile-9288190"
            ],
            [
                'name' => "Simmie Asulin (Etkin)",
                'relation' => "mother",
                'url' => "https://www.geni.com/api/profile-9288151"
            ],
            [
                'name' => "Judy Lev (Etkin)",
                'relation' => "sister",
                'url' => "https://www.geni.com/api/profile-9247443"
            ],
            [
                'name' => "Shmuel Eyal Lew",
                'relation' => "son",
                'url' => "https://www.geni.com/api/profile-9247366"
            ]
        ],
        'relationship' => "first cousin",
        'status' => "done",
        'step_count' => 3,
        'url' => "https://www.geni.com/path/Shmuel-Aharon-Kam-Kahn-%D7%A9%D7%9E%D7%95%D7%90%D7%9C-%D7%90%D7%94%D7%A8%D7%9F-%D7%A7%D7%9D-%D7%A7%D7%90%D7%9F+is+related+to+Shmuel-Lew?from=5098603059080070794&path_type=blood&to=4048631877200038987"
    ];

    /** @var array <string, string> $detailStringsGoodMorning  Used by API_ProfileTest */
    public static $detailStringsGoodMorning = [
        'sp' => 'Buenos días',
        'fr' => 'Bonjour',
        'ge' => 'Guten Morgen',
        'it' => 'Buongiorno',
        'zh' => '早上好',
        'ja' => 'おはようございます',
        'ko' => '좋은 아침이에요',
        'ar' => 'صباح الخير',
        'hi' => 'शुभ प्रभात',
        'ru' => 'доброе утро'
    ];
}
