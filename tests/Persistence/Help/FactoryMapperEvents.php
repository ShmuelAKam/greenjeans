<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;

/**
 * Factory for creating test API_Event instances for DB_EventMapper unit tests.
 */
class FactoryMapperEvents extends FactoryMapperBase
{
    public const EVENT_PRESETS = [
        'birth' => [
            'name'        => 'birth',
            'description' => 'Born at home in Virginia',
            'date' => [
                'day'   => 22,
                'month' => 2,
                'year'  => 1732,
            ],
            'location' => [
                'name'    => 'Pope\'s Creek Estate',
                'city'    => 'Colonial Beach',
                'county'  => 'Westmoreland County',
                'state'   => 'Virginia',
                'country' => 'United States',
            ],
        ],
        'marriage' => [
            'name'        => 'marriage',
            'description' => 'Married at White House plantation',
            'date' => [
                'day'   => 6,
                'month' => 1,
                'year'  => 1759,
            ],
            'location' => [
                'county'  => 'New Kent County',
                'state'   => 'Virginia',
                'country' => 'United States',
            ],
        ],
        'death' => [
            'name'        => 'death',
            'description' => 'Passed away at Mount Vernon',
            'date' => [
                'day'   => 14,
                'month' => 12,
                'year'  => 1799,
            ],
            'location' => [
                'name'    => 'Mount Vernon Estate',
                'state'   => 'Virginia',
                'country' => 'United States',
            ],
        ],
        'undated' => [
            'name'        => 'residence',
            'description' => 'Lived in Philadelphia during Constitutional Convention',
            'date'     => null,
            'location' => [
                'city'    => 'Philadelphia',
                'state'   => 'Pennsylvania',
                'country' => 'United States',
            ],
        ],
    ];

    /**
     * Create an API_Event instance from a preset key.
     */
    public function makePresetEvent(string $presetKey): API_Event
    {
        $preset = self::EVENT_PRESETS [$presetKey] ?? self::EVENT_PRESETS['birth'];

        $dateObj = null;
        if (isset($preset['date']))
        {
            $dateObj = API_Date::newFromArray($preset['date']);
        }

        $locationObj = API_Location::newFromArray($preset['location']);

        return new API_Event(
            $preset['name'],
            $preset['description'],
            $dateObj,
            $locationObj,
        );
    }
}
