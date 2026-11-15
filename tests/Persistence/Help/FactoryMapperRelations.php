<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Relation;

/**
 * Factory for creating test API_Relation objects and parameter arrays
 * for DB_RelationMapper unit tests without making remote API calls.
 */
class FactoryMapperRelations extends FactoryMapperBase
{
    public const RELATION_STEPS = [
        'father' => ['John Smith',   'father', 'https://www.geni.com/api/profile-1001'],
        'mother' => ['Mary Smith',   'mother', 'https://www.geni.com/api/profile-1002'],
        'spouse' => ['Jane Doe',     'wife',   'https://www.geni.com/api/profile-1003'],
        'child'  => ['Junior Smith', 'son',    'https://www.geni.com/api/profile-1004'],
    ];

    /**
     * Build an API_Relation object directly from values.
     */
    public function makeRelation(string $name, string $relationType, string $url): API_Relation
    {
        return API_Relation::newFromArray([
            'name'     => $name,
            'relation' => $relationType,
            'url'      => $url
        ]);
    }

    /**
     * Generate an ordered list of API_Relation objects.
     *
     * @return array<API_Relation>
     */
    public function makeRelationList(int $count = 3): array
    {
        $steps = array_values(self::RELATION_STEPS);
        $list  = [];

        for ($i = 0; $i < $count; $i++)
        {
            $step   = $steps[$i % count($steps)];
            $list[] = $this->makeRelation($step[0] . ' ' . ($i + 1), $step[1], $step[2] . '-' . $i);
        }

        return $list;
    }
}
