<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Location;

/**
 * Factory for creating test API_Location instances for DB_LocationMapper unit tests.
 */
class FactoryMapperLocations extends FactoryMapperBase
{
    public const LOCATION_PRESETS = [
        'full_location' => [
            'country'            => 'United States',
            'state'              => 'Virginia',
            'county'             => 'Fairfax County',
            'city'               => 'Mount Vernon',
            'place_name'         => 'Mount Vernon Estate',
            'street_address1'    => '3200 Mount Vernon Memorial Hwy',
            'street_address2'    => 'Mansion House',
            'street_address3'    => 'Suite A',
            'longitude'          => -77.0861,
            'latitude'           => 38.7079,
            'formatted_location' => 'Mount Vernon Estate, Mount Vernon, VA 22121, USA',
            'country_code'       => 'US',
        ],
        'city_state_only' => [
            'country'            => 'United States',
            'state'              => 'Pennsylvania',
            'city'               => 'Philadelphia',
            'place_name'         => 'Independence Hall',
            'longitude'          => -75.1500,
            'latitude'           => 39.9489,
            'formatted_location' => 'Independence Hall, Philadelphia, PA, USA',
            'country_code'       => 'US',
        ],
        'international' => [
            'country'            => 'France',
            'city'               => 'Paris',
            'place_name'         => 'Archives Nationales',
            'street_address1'    => '60 Rue des Francs-Bourgeois',
            'longitude'          => 2.3582,
            'latitude'           => 48.8596,
            'formatted_location' => '60 Rue des Francs-Bourgeois, 75003 Paris, France',
            'country_code'       => 'FR',
        ],
        'minimal' => [
            'country'            => 'United Kingdom',
            'city'               => 'London',
        ],
    ];

    /**
     * Create an API_Location instance from a preset key.
     */
    public function makePresetLocation(string $presetKey): API_Location
    {
        $preset = self::LOCATION_PRESETS[$presetKey] ?? self::LOCATION_PRESETS['full_location'];

        return API_Location::newFromArray($preset);
    }
}
