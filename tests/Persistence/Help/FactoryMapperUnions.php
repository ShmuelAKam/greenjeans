<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Union;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 * Factory for creating 24 comprehensive test API_Union objects and parameter arrays
 * for DB_UnionMapper unit tests without making remote API calls.
 */
class FactoryMapperUnions extends FactoryMapperBase
{
    /**
     * 24 union presets covering all possible status, partner, child, and event permutations.
     */
    public const UNION_PRESETS = [
        // Standard Married Unions
        'married_bio_children'           => ['status' => 'spouse',    'partners' => 2, 'bio' => 3, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => false],
        'married_adopted_children'       => ['status' => 'spouse',    'partners' => 2, 'bio' => 0, 'adopt' => 2, 'foster' => 0, 'marriage' => true,  'divorce' => false],
        'married_foster_children'        => ['status' => 'spouse',    'partners' => 2, 'bio' => 0, 'adopt' => 0, 'foster' => 2, 'marriage' => true,  'divorce' => false],
        'married_blended_children'       => ['status' => 'spouse',    'partners' => 2, 'bio' => 2, 'adopt' => 1, 'foster' => 1, 'marriage' => true,  'divorce' => false],
        'married_childless'              => ['status' => 'spouse',    'partners' => 2, 'bio' => 0, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => false],
        'married_single_partner_record'  => ['status' => 'spouse',    'partners' => 1, 'bio' => 2, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => false],

        // Ex-Spouse / Divorced Unions
        'divorced_bio_children'          => ['status' => 'ex_spouse', 'partners' => 2, 'bio' => 2, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => true],
        'divorced_adopted_children'      => ['status' => 'ex_spouse', 'partners' => 2, 'bio' => 0, 'adopt' => 2, 'foster' => 0, 'marriage' => true,  'divorce' => true],
        'divorced_blended_children'      => ['status' => 'ex_spouse', 'partners' => 2, 'bio' => 1, 'adopt' => 1, 'foster' => 1, 'marriage' => true,  'divorce' => true],
        'divorced_childless'             => ['status' => 'ex_spouse', 'partners' => 2, 'bio' => 0, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => true],
        'divorced_no_marriage_event'     => ['status' => 'ex_spouse', 'partners' => 2, 'bio' => 1, 'adopt' => 0, 'foster' => 0, 'marriage' => false, 'divorce' => true],

        // Partners / Unmarried / Domestic Unions
        'partner_bio_children'           => ['status' => 'partner',   'partners' => 2, 'bio' => 2, 'adopt' => 0, 'foster' => 0, 'marriage' => false, 'divorce' => false],
        'partner_foster_children'        => ['status' => 'partner',   'partners' => 2, 'bio' => 0, 'adopt' => 0, 'foster' => 3, 'marriage' => false, 'divorce' => false],
        'partner_childless'              => ['status' => 'partner',   'partners' => 2, 'bio' => 0, 'adopt' => 0, 'foster' => 0, 'marriage' => false, 'divorce' => false],

        // Single Parent / Unilateral Unions
        'single_parent_bio'              => ['status' => 'spouse',    'partners' => 1, 'bio' => 3, 'adopt' => 0, 'foster' => 0, 'marriage' => false, 'divorce' => false],
        'single_parent_adopted'          => ['status' => 'spouse',    'partners' => 1, 'bio' => 0, 'adopt' => 2, 'foster' => 0, 'marriage' => false, 'divorce' => false],
        'single_parent_foster'           => ['status' => 'spouse',    'partners' => 1, 'bio' => 0, 'adopt' => 0, 'foster' => 2, 'marriage' => false, 'divorce' => false],

        // Same-Sex Unions
        'same_sex_married_adopted'       => ['status' => 'spouse',    'partners' => 2, 'bio' => 0, 'adopt' => 3, 'foster' => 0, 'marriage' => true,  'divorce' => false],
        'same_sex_divorced_foster'       => ['status' => 'ex_spouse', 'partners' => 2, 'bio' => 0, 'adopt' => 0, 'foster' => 2, 'marriage' => true,  'divorce' => true],

        // Complex / Edge Case Unions
        'large_family_union'             => ['status' => 'spouse',    'partners' => 2, 'bio' => 6, 'adopt' => 2, 'foster' => 2, 'marriage' => true,  'divorce' => false],
        'widowed_union'                  => ['status' => 'spouse',    'partners' => 2, 'bio' => 2, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => false],
        'annulled_union'                 => ['status' => 'ex_spouse', 'partners' => 2, 'bio' => 0, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => true],
        'multi_partner_polygamous'       => ['status' => 'spouse',    'partners' => 3, 'bio' => 4, 'adopt' => 0, 'foster' => 0, 'marriage' => true,  'divorce' => false],
        'minimal_empty_union'            => ['status' => 'spouse',    'partners' => 0, 'bio' => 0, 'adopt' => 0, 'foster' => 0, 'marriage' => false, 'divorce' => false],
    ];

    /**
     * Create an API_Union instance by preset key using reflection (since constructor is private).
     */
    public function makePresetUnion(string $presetKey, ?string $id = null): API_Union
    {
        $preset = self::UNION_PRESETS[$presetKey] ?? self::UNION_PRESETS['married_bio_children'];

        $params = $this->makeParamArray(
            status: $preset['status'],
            numPartners: $preset['partners'],
            numBio: $preset['bio'],
            numAdopted: $preset['adopt'],
            numFoster: $preset['foster'],
            hasMarriage: $preset['marriage'],
            hasDivorce: $preset['divorce'],
            id: $id,
        );

        return $this->newUnionFromArray($params);
    }

    /**
     * Reflective instantiation of API_Union since its factory method is private/internal.
     * @param array <string, string|array<string, string|array<string, string>>> $params
     */
    public function newUnionFromArray(array $params): API_Union
    {
        $refMethod = new \ReflectionMethod(API_Union::class, 'newFromArray');
        $refMethod->setAccessible(true);

        return $refMethod->invoke(null, $params);
    }

    /**
     * Generates a fully customizable parameter array representing an API_Union.
     * @param string $status
     * @param int $numPartners
     * @param int $numBio
     * @param int $numAdopted
     * @param int $numFoster
     * @param bool $hasMarriage
     * @param bool $hasDivorce
     * @param string $id
     * @return array <string, string|array <string>|array <string, array <string, string>>>
     */
    public function makeParamArray(
        string  $status = 'spouse',
        int     $numPartners = 2,
        int     $numBio = 2,
        int     $numAdopted = 0,
        int     $numFoster = 0,
        bool    $hasMarriage = true,
        bool    $hasDivorce = false,
        ?string $id = null,
    ): array {
        $unionID = ($id !== null && $id !== '')
            ? $id
            : FactoryBase::generatePrefixedId('union');

        $unionParam = [
            'id'       => $unionID,
            'status'   => $status,
            'partners' => FactoryBase::makeIDList('profile', $numPartners),
        ];

        if ($hasMarriage)
        {
            $unionParam['marriage'] = [
                'date'     => ['formatted_date' => '1995-06-20'],
                'location' => ['place_name' => 'London, UK']
            ];
        }

        if ($hasDivorce)
        {
            $unionParam['divorce'] = [
                'date'     => ['formatted_date' => '2010-09-15'],
                'location' => ['place_name' => 'London, UK']
            ];
        }

        $bioList    = FactoryBase::makeIDList('profile', $numBio);
        $adoptList  = FactoryBase::makeIDList('profile', $numAdopted);
        $fosterList = FactoryBase::makeIDList('profile', $numFoster);

        if (!empty($bioList))
        {
            $unionParam['children'] = $bioList;
        }

        if (!empty($adoptList))
        {
            $unionParam['adopted_children'] = $adoptList;
        }

        if (!empty($fosterList))
        {
            $unionParam['foster_children'] = $fosterList;
        }

        return $unionParam;
    }
}
