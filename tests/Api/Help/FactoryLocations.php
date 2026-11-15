<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Location;

class FactoryLocations extends FactoryBase
{
    // @formatter:off
    private const COUNTRY_CAPITALS = [
        [ 'Algeria',        'Algiers'     ],
        [ 'Angola',         'Luanda'      ],
        [ 'Austria',        'Vienna'      ],
        [ 'Belgium',        'Brussels'    ],
        [ 'Botswana',       'Gaborone'    ],
        [ 'Chad',           'N\'Djamena'  ],
        [ 'Denmark',        'Copenhagen'  ],
        [ 'Egypt',          'Cairo'       ],
        [ 'Ethiopia',       'Addis Ababa' ],
        [ 'Finland',        'Helsinki'    ],
        [ 'France',         'Paris'       ],
        [ 'Gabon',          'Libreville'  ],
        [ 'Germany',        'Berlin'      ],
        [ 'Ghana',          'Accra'       ],
        [ 'Greece',         'Athens'      ],
        [ 'Ireland',        'Dublin'      ],
        [ 'Italy',          'Rome'        ],
        [ 'Kenya',          'Nairobi'     ],
        [ 'Libya',          'Tripoli'     ],
        [ 'Mali',           'Bamako'      ],
        [ 'Morocco',        'Rabat'       ],
        [ 'Namibia',        'Windhoek'    ],
        [ 'Netherlands',    'Amsterdam'   ],
        [ 'Nigeria',        'Abuja'       ],
        [ 'Poland',         'Warsaw'      ],
        [ 'Portugal',       'Lisbon'      ],
        [ 'Russia',         'Moscow'      ],
        [ 'Senegal',        'Dakar'       ],
        [ 'South Africa',   'Pretoria'    ],
        [ 'Spain',          'Madrid'      ],
        [ 'Sudan',          'Khartoum'    ],
        [ 'Sweden',         'Stockholm'   ],
        [ 'Switzerland',    'Bern'        ],
        [ 'Tanzania',       'Dodoma'      ],
        [ 'Tunisia',        'Tunis'       ],
        [ 'Uganda',         'Kampala'     ],
        [ 'Ukraine',        'Kyiv'        ],
        [ 'United Kingdom', 'London'      ],
        [ 'Zambia',         'Lusaka'      ],
        [ 'Zimbabwe',       'Harare'      ],
    ];

    private const STREET_NAMES = [
        [ 'Cedar Street' ],
        [ 'Church Lane' ],
        [ 'Church Road' ],
        [ 'Elm Street' ],
        [ 'Fifth Avenue' ],
        [ 'Grange Road' ],
        [ 'Green Lane' ],
        [ 'High Street' ],
        [ 'Hill Street' ],
        [ 'Kings Road' ],
        [ 'Kingsway' ],
        [ 'Lake Street' ],
        [ 'London Road' ],
        [ 'Main Street' ],
        [ 'Manor Road' ],
        [ 'Maple Avenue' ],
        [ 'New Road' ],
        [ 'Oak Street' ],
        [ 'Park Avenue' ],
        [ 'Park Road' ],
        [ 'Pine Street' ],
        [ 'Queens Road' ],
        [ 'Second Street' ],
        [ 'Station Road' ],
        [ 'The Avenue' ],
        [ 'The Crescent' ],
        [ 'Victoria Road' ],
        [ 'View Street' ],
        [ 'Washington Street' ],
        [ 'Windsor Road' ],
    ];
    // @formatter:on

    public function __construct()
    {
        // API_Dates are always created as PART of larger objects.
        // So shredding is NOT needed.

        parent::__construct(ShredderDummy::class);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function makeTestLocation(string $country = null, ?string $city, string $streetAddress): API_Location
    {
        $loco = new API_Location();

        $loco->setLocation($country, null, null, $city);
        $loco->setStreetAddress($streetAddress);

        return $loco;
    }

    public function makeRandomLocation(): API_Location
    {
        $cityInd = array_rand(self::COUNTRY_CAPITALS);
        $city    = self::COUNTRY_CAPITALS[$cityInd][0];
        $country = self::COUNTRY_CAPITALS[$cityInd][1];

        $street = self::STREET_NAMES[array_rand(self::STREET_NAMES)][0];
        $house  = rand(1, 99);

        $street .= ' ' . $house;

        return $this->makeTestLocation($country, $city, $street);
    }

    public static function equalLocations(?API_Location $location1, ?API_Location $location2): bool
    {
        $locationsEqual = false;

        if ($location1 != null && $location2 != null)
        {
            if ($location1->getCountry() == $location2->getCountry() && $location1->getCity() == $location2->getCity() && $location1->getStreetAddress() == $location2->getStreetAddress())
            {
                $locationsEqual = true;
            }
        }
        elseif ($location1 == null && $location2 == null)
        {
            $locationsEqual = true;
        }

        return $locationsEqual;
    }
}
