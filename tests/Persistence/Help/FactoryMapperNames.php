<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Name;

/**
 * Factory for creating test API_Name instances for DB_NameMapper unit tests.
 */
class FactoryMapperNames extends FactoryMapperBase
{
    public const NAME_PRESETS = [
        'full_name' => [
            'display_name' => 'General George Washington',
            'first_name'   => 'George',
            'middle_name'  => 'Augustine',
            'last_name'    => 'Washington',
            'title'        => 'General',
            'suffix'       => 'Esq.',
            'nicknames'    => 'Father of His Country, General Washington',
        ],
        'maiden_name' => [
            'display_name' => 'Martha Washington',
            'first_name'   => 'Martha',
            'last_name'    => 'Washington',
            'maiden_name'  => 'Dandridge',
            'nicknames'    => 'Patsy',
        ],
        'simple_name' => [
            'first_name'   => 'John',
            'last_name'    => 'Adams',
            'display_name' => 'John Adams',
        ],
    ];

    /**
     * Create an API_Name instance from a preset key.
     */
    public function makePresetName(string $presetKey): API_Name
    {
        $preset = self::NAME_PRESETS[$presetKey] ?? self::NAME_PRESETS['full_name'];

        return API_Name::newFromArray($preset);
    }
}
