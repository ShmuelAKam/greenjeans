<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

/**
 * Factory for creating test Tag arrays for DB_TagMapper unit tests.
 */
class FactoryMapperTags extends FactoryMapperBase
{
    public const TAG_PRESETS = [
        'single_profile_list' => [
            'profile-tag-101',
        ],
        'single_profile_list_lookup' => [
            'profile-tag-101' => true,
        ],
        'multiple_profiles_list' => [
            'profile-tag-101',
            'profile-tag-102',
            'profile-tag-103',
        ],
        'multiple_profiles_lookup' => [
            'profile-tag-101' => true,
            'profile-tag-102' => true,
            'profile-tag-103' => true,
        ],
    ];

    /**
     * Get tag array preset testing both list and lookup table formats.
     * @return array <string>|array<string, true>
     */
    public function makePresetTags(string $presetKey): array
    {
        return self::TAG_PRESETS[$presetKey] ?? self::TAG_PRESETS['single_profile_list'];
    }
}
