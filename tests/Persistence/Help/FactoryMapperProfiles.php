<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 * Factory for creating diverse test API_Profile objects and parameter arrays
 * for DB_ProfileMapper unit tests without making remote API calls.
 */
class FactoryMapperProfiles extends FactoryMapperBase
{
    /**
     * Named profile presets covering edge cases and complex configurations.
     */
    public const PROFILE_PRESETS = [
        'presidential' => [
            'first_name'  => 'George',
            'last_name'   => 'Washington',
            'gender'      => 'male',
            'birth_date'  => '1732-02-22',
            'birth_place' => 'Westmoreland County, Virginia',
            'death_date'  => '1799-12-14',
            'death_place' => 'Mount Vernon, Virginia',
            'is_alive'    => false,
            'public'      => true,
            'occupation'  => 'General & President',
            'languages'   => ['en-US' => 'First President of the United States.'],
            'nicknames'   => ['Father of His Country', 'General Washington'],
        ],
        'multilingual' => [
            'first_name'  => 'Jean',
            'last_name'   => 'Dupont',
            'gender'      => 'male',
            'birth_date'  => '1920-05-15',
            'birth_place' => 'Paris, France',
            'death_date'  => '1995-11-20',
            'death_place' => 'Lyon, France',
            'is_alive'    => false,
            'public'      => true,
            'occupation'  => 'Historian',
            'languages'   => [
                'en-US' => 'French historian specializing in genealogy.',
                'fr-FR' => 'Historien français spécialisé en généalogie.',
                'de-DE' => 'Französischer Historiker spezialisiert auf Genealogie.'
            ],
            'nicknames'   => ['Jeannot'],
        ],
        'living_private' => [
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'gender'      => 'female',
            'birth_date'  => '1985-08-12',
            'birth_place' => 'Boston, Massachusetts',
            'death_date'  => null,
            'death_place' => null,
            'is_alive'    => true,
            'public'      => false,
            'occupation'  => 'Software Engineer',
            'languages'   => ['en-US' => 'Private living profile.'],
            'nicknames'   => ['Janey'],
        ],
        'minimal_brief' => [
            'first_name'  => 'John',
            'last_name'   => 'Smith',
            'gender'      => 'male',
            'birth_date'  => null,
            'birth_place' => null,
            'death_date'  => null,
            'death_place' => null,
            'is_alive'    => true,
            'public'      => false,
            'occupation'  => null,
            'languages'   => [],
            'nicknames'   => [],
        ]
    ];

    /**
     * Create an API_Profile instance by preset key.
     */
    public function makePresetProfile(string $presetKey, bool $full = true, ?string $id = null): API_Profile
    {
        $preset = self::PROFILE_PRESETS[$presetKey] ?? self::PROFILE_PRESETS['presidential'];

        $params = $this->makeParamArray(
            firstName: $preset['first_name'],
            lastName: $preset['last_name'],
            gender: $preset['gender'],
            birthDate: $preset['birth_date'],
            birthPlace: $preset['birth_place'],
            deathDate: $preset['death_date'],
            deathPlace: $preset['death_place'],
            isAlive: $preset['is_alive'],
            isPublic: $preset['public'],
            occupation: $preset['occupation'],
            languages: $preset['languages'],
            nicknames: $preset['nicknames'],
            full: $full,
            id: $id,
        );

        return API_Profile::newFromArray($params, brief: !$full, fromDB: false);
    }

    /**
     * Generates a fully customizable parameter array representing an API_Profile.
     * @param string $firstName
     * @param string $lastName
     * @param string $gender
     * @param string $birthDate
     * @param string $birthPlace
     * @param string $deathDate
     * @param string $deathPlace
     * @param bool $isAlive
     * @param bool $isPublic
     * @param string $occupation
     * @param array <string, string> $languages
     * @param array <string>         $nicknames
     * @param bool $full
     * @param string $id
     * @return array<string, string|int|bool|null|array<string, string>|list<string>|array<string, array<string, string>>>
     */
    public function makeParamArray(
        string  $firstName = 'George',
        string  $lastName = 'Washington',
        string  $gender = 'male',
        ?string $birthDate = '1732-02-22',
        ?string $birthPlace = 'Westmoreland County, Virginia',
        ?string $deathDate = '1799-12-14',
        ?string $deathPlace = 'Mount Vernon, Virginia',
        bool    $isAlive = false,
        bool    $isPublic = true,
        ?string $occupation = 'General & President',
        array   $languages = ['en-US' => 'First President'],
        array   $nicknames = ['General'],
        bool    $full = true,
        ?string $id = null,
    ): array {
        $profileID = ($id !== null && $id !== '')
        ? $id
        : FactoryBase::generatePrefixedId('profile');

        $profileParam = [
            'id'           => $profileID,
            'guid'         => FactoryBase::generateGuidFromId($profileID),
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'name'         => trim($firstName . ' ' . $lastName),
            'display_name' => trim($firstName . ' ' . $lastName),
            'gender'       => $gender,
            'is_alive'     => $isAlive ? 1 : 0,
            'public'       => $isPublic ? 1 : 0,
            'claimed'      => 0,
            'big_tree'     => 1,
            'locked'       => 0,
            'occupation'   => $occupation,
            'created_at'   => (string) (time() - 86400),
            'updated_at'   => (string) time(),
            'nicknames'    => $nicknames,
        ];

        // Complex Sub-Object: Birth Event
        if ($birthDate !== null || $birthPlace !== null)
        {
            $profileParam['birth'] = [
                'date'     => ['formatted_date' => $birthDate ?? ''],
                'location' => ['place_name' => $birthPlace ?? '']
            ];
        }

        // Complex Sub-Object: Death Event
        if ($deathDate !== null || $deathPlace !== null)
        {
            $profileParam['death'] = [
                'date'     => ['formatted_date' => $deathDate ?? ''],
                'location' => ['place_name' => $deathPlace ?? '']
            ];
        }

        // Complex Sub-Object: Burial Event
        if (!$isAlive)
        {
            $profileParam['burial'] = [
                'date'     => ['formatted_date' => $deathDate ?? ''],
                'location' => ['place_name' => $deathPlace ?? '']
            ];
        }

        // Multi-locale Detail Strings (About Me descriptions)
        if (!empty($languages))
        {
            $profileParam['detail_strings'] = $languages;
            $profileParam['about_me']       = reset($languages);
        }

        // Cover / Mugshot Size Map Sub-object
        if ($full)
        {
            $profileParam['mugshot_urls'] = [
                'large'  => "https://photos.geni.com/p13/{$profileID}_large.jpg",
                'medium' => "https://photos.geni.com/p13/{$profileID}_medium.jpg",
                'thumb'  => "https://photos.geni.com/p13/{$profileID}_thumb.jpg",
                ];

            // Relational collections (IDs)
            $profileParam['managers']         = [$this->makeID('profile-')];
            $profileParam['requested_merges'] = [$this->makeID('profile-')];
            $profileParam['unions']           = [$this->makeID('union-')];
            $profileParam['projects']         = [$this->makeID('project-')];
            $profileParam['documents']        = [$this->makeID('doc-')];
            $profileParam['photos']           = [$this->makeID('photo-')];
            $profileParam['videos']           = [$this->makeID('video-')];
        }

        return $profileParam;
    }

    private function makeID(string $prefix): string
    {
        return FactoryBase::generatePrefixedId($prefix);
    }
}
