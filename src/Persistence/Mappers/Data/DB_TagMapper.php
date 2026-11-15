<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;

/**
 * DB_TagMapper
 * Handles persistence for Media Tags (linking profiles to photos/videos/docs).
 */
class DB_TagMapper extends DB_BaseMapper
{
    private DB_LinkTableMapper $linkMapper;

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_media_tags', 'owner_id');

        $this->linkMapper = $persistor->getMapper(DB_LinkTableMapper::class);
    }

    /**
     * Create a single tag.
     */
    public function save(string $profileID, string $ownerID): void
    {
        $tagArray = [ $profileID => true];
        $this->linkMapper->save('geni_media_tags', 'owner_id', $ownerID, 'profile_id', $tagArray, false);
    }

    /**
     * CREATE set of tags for a specific media object.
     * @param array<int, string>|array<string, TRUE>  $tags can be either array of 'profile IDs' or look-up array,
     *                     in which the profile IDs are the keys, with a TRUE value.
     * @param string $ownerID
     */
    public function createAll(array $tags, string $ownerID): void
    {
        $clearTable = $this->tableHasRows('geni_media_tags', 'owner_id', $ownerID);

        /**
         * @var array <int, string>|array<string, TRUE> $profileIds
         */
        $profileIds = array_is_list($tags) ? $tags : array_keys($tags);

        $this->linkMapper->save('geni_media_tags', 'owner_id', $ownerID, 'profile_id', $profileIds, $clearTable);
    }

    /**
     * READ all tags for a specific media object.
     * @return array <string> column values
     */
    public function readAll(string $ownerID): array
    {
        return $this->linkMapper->readCol('geni_media_tags', 'owner_id', $ownerID, 'profile_id');
    }

    /**
     * DELETE all tags belonging to a media object.
     */
    public function deleteAll(string $ownerID): void
    {
        $this->deleteBy('owner_id', $ownerID);
    }
}
