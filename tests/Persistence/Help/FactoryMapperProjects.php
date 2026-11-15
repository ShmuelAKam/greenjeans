<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Project;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 * Factory for creating test API_Project objects and parameter arrays
 * for DB_ProjectMapper unit tests without making remote API calls.
 */
class FactoryMapperProjects extends FactoryMapperBase
{
    public const PROJECT_LIST = [
        //                    Name                       Description                             Profiles Collabs Followers
        'ALL fields'     => [ 'Mayflower Descendants',   'Documenting 1620 passenger lines',      3,       2,      4 ],
        'name only'      => [ 'Royal Families of Europe', null,                                   0,       0,      0 ],
        'description'    => [ 'Civil War Regiments',     'Tracking soldiers and company rosters', 2,       1,      0 ],
    ];

    /**
     * Build an API_Project object directly from array parameters.
     */
    public function makeMapProject(
        string  $name = '',
        ?string $description = null,
        int     $numProfiles = 0,
        int     $numCollabs = 0,
        int     $numFollowers = 0,
        ?string $id = null,
        bool    $full = true,
    ): API_Project {
        $projectParam = $this->makeParamArray($name, $description, $numProfiles, $numCollabs, $numFollowers, id: $id, full: $full);

        return API_Project::newFromArray($projectParam, fromDB: true);
    }

    /**
     * Create raw parameter array representation of an API_Project.
     * @param string $name
     * @param string $description
     * @param int $numProfiles
     * @param int $numCollabs
     * @param int $numFollowers
     * @param string $id
     * @param bool $full
     * @return array <string, string|array <string>>
     */
    public function makeParamArray(
        string  $name = '',
        ?string $description = null,
        int     $numProfiles = 0,
        int     $numCollabs = 0,
        int     $numFollowers = 0,
        ?string $id = null,
        bool    $full = true,
    ): array {
        static $HALF_HOUR = 1800;

        $projectID = ($id !== null && $id !== '')
            ? $id
            : FactoryBase::generatePrefixedId('project');

        $projectParam ['id']         = $projectID;
        $projectParam ['name']       = $name;
        $projectParam ['created_at'] = (string) (time() - $HALF_HOUR);
        $projectParam ['updated_at'] = (string) time();
        $projectParam ['url']        = 'https://www.geni.com/api/project/' . $projectID;

        if ($description !== null)
        {
            $projectParam ['description'] = $description;
        }

        if ($full)
        {
            $projectParam ['profiles']      = FactoryBase::makeIDList('profile', $numProfiles);
            $projectParam ['collaborators'] = FactoryBase::makeIDList('profile', $numCollabs);
            $projectParam ['followers']     = FactoryBase::makeIDList('profile', $numFollowers);
        }

        return $projectParam;
    }
}
