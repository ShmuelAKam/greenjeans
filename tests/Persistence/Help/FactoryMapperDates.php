<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Date;

/**
 * Factory for creating test API_Date instances for DB_DateMapper unit tests.
 */
class FactoryMapperDates extends FactoryMapperBase
{
    public const DATE_PRESETS = [
        'exact_date' => [
            'day'   => 22,
            'month' => 2,
            'year'  => 1732,
            'circa' => false,
        ],
        'circa_date' => [
            'day'   => null,
            'month' => null,
            'year'  => 1850,
            'circa' => true,
        ],
        'date_range' => [
            'day'       => 10,
            'month'     => 5,
            'year'      => 1914,
            'circa'     => false,
            'end_day'   => 11,
            'end_month' => 11,
            'end_year'  => 1918,
            'end_circa' => false,
            'range'     => 'between',
        ],
        'year_only' => [
            'year'  => 1620,
            'circa' => false,
        ],
    ];

    /**
     * Create an API_Date instance from a preset key.
     */
    public function makePresetDate(string $presetKey): API_Date
    {
        $preset = self::DATE_PRESETS[$presetKey] ?? self::DATE_PRESETS['exact_date'];

        $params = [];
        foreach ($preset as $key => $value)
        {
            if ($value !== null)
            {
                $params[$key] = $value;
            }
        }

        return API_Date::newFromArray($params);
    }
}
