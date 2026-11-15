<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Revision;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 * Factory for creating test API_Revision objects and parameter arrays
 * for DB_RevisionMapper unit tests without making remote API calls.
 */
class FactoryMapperRevisions extends FactoryMapperBase
{
    /**
     * @var array <string, array <string>>
     */
    public const REVISION_LIST = [
        'profile update' => ['update_profile', 'Updated birth date and location for profile', '2026-10-04', '14:30:00', '1759588200'],
        'union merge'    => ['merge_unions',   'Merged duplicate union records',              '2026-09-15', '09:15:22', '1757927722'],
        'photo tagged'   => ['tag_photo',      'Added tag for profile-1234',                  '2026-08-01', '18:45:10', '1754073910'],
    ];

    /**
     * Build an API_Revision object directly from values.
     */
    public function makeMapRevision(
        string  $action = 'update',
        ?string $story = 'Default story',
        string  $dateLocal = '2026-10-04',
        string  $timeLocal = '12:00:00',
        ?string $timestamp = null,
        ?string $id = null,
    ): API_Revision {
        $params = $this->makeParamArray($action, $story, $dateLocal, $timeLocal, $timestamp, id: $id);

        return API_Revision::newFromArray($params);
    }

    /**
     * Create raw parameter array representation of an API_Revision.
     * @param string $action
     * @param string $story
     * @param string $dateLocal
     * @param string $timeLocal
     * @param string $timestamp
     * @param string $id
     * @return array <string, string>
     */
    public function makeParamArray(
        string  $action = 'update',
        ?string $story = 'Default story',
        string  $dateLocal = '2026-10-04',
        string  $timeLocal = '12:00:00',
        ?string $timestamp = null,
        ?string $id = null,
    ): array {
        $revID = ($id !== null && $id !== '')
            ? $id
            : FactoryBase::generatePrefixedId('revision');

        return [
            'id'         => $revID,
            'guid'       => FactoryBase::generateGuidFromId($revID),
            'action'     => $action,
            'story'      => $story,
            'date_local' => $dateLocal,
            'time_local' => $timeLocal,
            'timestamp'  => $timestamp ?? (string) time()
        ];
    }
}
