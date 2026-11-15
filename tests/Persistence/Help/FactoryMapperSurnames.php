<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Surname;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 * Factory for creating test API_Surname objects and parameter arrays
 * for DB_SurnameMapper unit tests without making remote API calls.
 */
class FactoryMapperSurnames extends FactoryMapperBase
{
    /**
     * @var array <string, array <string|int|NULL>>
     */
    public const SURNAME_LIST = [
        //                    Slugged Name   Description                             Followers Profiles
        'ALL fields'     => [ 'smith',       'Common Anglo-Saxon occupational name', 5,        12 ],
        'slug only'      => [ 'van-gogh',    null,                                   0,         0 ],
        'description'    => [ 'mcdonald',    'Scottish patronymic surname',          2,         4 ],
    ];

    /**
     * Build an API_Surname object directly from parameter values.
     */
    public function makeMapSurname(
        string  $sluggedName = '',
        ?string $description = null,
        int     $numFollowers = 0,
        int     $numProfiles = 0,
        ?string $id = null,
        bool    $full = true,
    ): API_Surname {
        $surnameParam = $this->makeParamArray(
            $sluggedName,
            $description,
            $numFollowers,
            $numProfiles,
            id: $id,
            full: $full,
        );

        return API_Surname::newFromArray($surnameParam, fromDB: true);
    }

    /**
     * Create raw parameter array representation of an API_Surname.
     * @param string $sluggedName
     * @param string $description
     * @param int $numFollowers
     * @param int $numProfiles
     * @param string $id
     * @param bool $full
     * @return array <string, int|string|array <string>>
     */
    public function makeParamArray(
        string  $sluggedName = '',
        ?string $description = null,
        int     $numFollowers = 0,
        int     $numProfiles = 0,
        ?string $id = null,
        bool    $full = true,
    ): array {
        $surnameID = ($id !== null && $id !== '')
            ? $id
            : FactoryBase::generatePrefixedId('surname');

        $surnameParam ['id']             = $surnameID;
        $surnameParam ['slugged_name']   = $sluggedName;
        $surnameParam ['follower_count'] = $numFollowers;
        $surnameParam ['profile_count']  = $numProfiles;
        $surnameParam ['url']            = 'https://www.geni.com/api/surname/' . $sluggedName;

        if ($description !== null)
        {
            $surnameParam ['description'] = $description;
        }

        if ($full)
        {
            $surnameParam ['followers'] = FactoryBase::makeIDList('profile', $numFollowers);
            $surnameParam ['profiles']  = FactoryBase::makeIDList('profile', $numProfiles);
        }

        return $surnameParam;
    }
}
