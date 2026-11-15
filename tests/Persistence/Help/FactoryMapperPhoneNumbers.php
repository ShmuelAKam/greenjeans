<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_PhoneNumber;

/**
 * Factory for creating test API_PhoneNumber instances for DB_PhoneNumberMapper unit tests.
 */
class FactoryMapperPhoneNumbers extends FactoryMapperBase
{
    public const PHONE_PRESETS = [
        'mobile_us' => [
            'area_code'    => '202',
            'country_code' => '1',
            'number'       => '5550143',
            'type'         => 'mobile',
        ],
        'home_us' => [
            'area_code'    => '703',
            'country_code' => '1',
            'number'       => '5550198',
            'type'         => 'home',
        ],
        'work_uk' => [
            'area_code'    => '20',
            'country_code' => '44',
            'number'       => '79460912',
            'type'         => 'work',
        ],
        'fax' => [
            'area_code'    => '212',
            'country_code' => '1',
            'number'       => '5550111',
            'type'         => 'fax',
        ],
    ];

    /**
     * Create an API_PhoneNumber instance from a preset key.
     */
    public function makePresetPhoneNumber(string $presetKey): API_PhoneNumber
    {
        $preset = self::PHONE_PRESETS[$presetKey] ?? self::PHONE_PRESETS['mobile_us'];

        return API_PhoneNumber::newFromArray($preset);
    }
}
