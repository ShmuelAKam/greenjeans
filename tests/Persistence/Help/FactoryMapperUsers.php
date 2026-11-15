<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_User;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 * Factory for creating test API_User objects and parameter arrays
 * for DB_UserMapper unit tests without making remote API calls.
 */
class FactoryMapperUsers extends FactoryMapperBase
{
    /**
     * @var array <string, array <string|int|array <string, string>>>
     */
    public const USER_LIST = [
        'pro user'   => ['John Doe',   'pro',   ['theme' => 'dark',  'lang' => 'en'], 2, 3, 4],
        'basic user' => ['Jane Smith', 'basic', ['theme' => 'light', 'lang' => 'es'], 0, 1, 2],
        'no_docs'    => ['Ding Dong',  'basic', ['theme' => 'pale',  'lang' => 'po'], 4, 0, 1],
        'no_profs'   => ['Jane Smith', 'basic', ['theme' => 'dim',   'lang' => 'he'], 5, 1, 0],
    ];

    /**
     * @param string $name
     * @param string $accountType
     * @param array <string, string> $metadata
     * @param int $numLabels
     * @param int $numFollowed
     * @param int $numProfiles
     * @param bool $full
     * @return API_User
     */
    public function makeMapUser(
        string $name = 'Test User',
        string $accountType = 'pro',
        array  $metadata = ['setting' => 'value'],
        int    $numLabels = 2,
        int    $numFollowed = 3,
        int    $numProfiles = 4,
        bool   $full = true,
    ): API_User {
        $params = $this->makeParamArray($name, $accountType, $metadata, $numLabels, $numFollowed, $numProfiles, full: $full);

        return API_User::newFromArray($params, fromDB: true);
    }

    /**
     * @param string $name
     * @param string $accountType
     * @param array <string, string> $metadata
     * @param int $numLabels
     * @param int $numFollowed
     * @param int $numProfiles
     * @param bool $full
     * @return array <string, string|array <string, string>>
     */
    public function makeParamArray(
        string $name = 'Test User',
        string $accountType = 'pro',
        array  $metadata = ['setting' => 'value'],
        int    $numLabels = 2,
        int    $numFollowed = 3,
        int    $numProfiles = 4,
        bool   $full = true,
    ): array {
        $userParam = [
            'name'         => $name,
            'account_type' => $accountType,
            'metadata'     => $metadata,
        ];

        if ($full)
        {
            $userParam['my-labels']          = $this->makeLabelList($numLabels);
            $userParam['followed-documents'] = FactoryBase::makeIDList('doc', $numFollowed);
            $userParam['followed-profiles']  = FactoryBase::makeIDList('profile', $numProfiles);
        }

        return $userParam;
    }

    /**
     * @param int $count
     * @return array <string>
     */
    private function makeLabelList(int $count): array
    {
        static $labels = ['Family', 'Genealogy', 'DNA Match', 'Research', 'Archive'];

        $madeLabels = array_slice($labels, 0, $count);

        return $madeLabels;
    }
}
