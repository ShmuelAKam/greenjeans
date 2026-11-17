<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_User;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;

/**
 * DB_UserMapper
 * Handles persistence for the API_User object.
 * Maps the authorized user of the SDK and their associated collections.
 * Metadata is stored as JSON in the database.
 */
class DB_UserMapper extends DB_BaseMapper
{
    /**
     * Maps the various API_List objects and where to get/put the data.
     * @var array <string, array <string, string>>
     */
    private const COLLECTIONS
        = [
            'followed-documents' => [ 'get' => 'getFollowedDocuments', 'col' => 'doc_id',     'table' => 'geni_documents' ],
            'followed-profiles'  => [ 'get' => 'getFollowedProfiles',  'col' => 'profile_id', 'table' => 'geni_profiles'  ],
            'followed-projects'  => [ 'get' => 'getFollowedProjects',  'col' => 'project_id', 'table' => 'geni_projects'  ],
            'followed-surnames'  => [ 'get' => 'getFollowedSurnames',  'col' => 'surname_id', 'table' => 'geni_surnames'  ],
            'managed-profiles'   => [ 'get' => 'getManagedProfiles',   'col' => 'profile_id', 'table' => 'geni_profiles'  ],
            'max-family'         => [ 'get' => 'getMaxFamily',         'col' => 'profile_id', 'table' => 'geni_profiles'  ],
            'my-albums'          => [ 'get' => 'getMyAlbums',          'col' => 'album_id',   'table' => 'geni_photo_albums' ],
            'uploaded-documents' => [ 'get' => 'getUploadedDocuments', 'col' => 'doc_id',     'table' => 'geni_documents' ],
            'uploaded-photos'    => [ 'get' => 'getUploadedPhotos',    'col' => 'photo_id',   'table' => 'geni_photos'    ],
            'uploaded-videos'    => [ 'get' => 'getUploadedVideos',    'col' => 'video_id',   'table' => 'geni_videos'    ]
        ];
    private DB_LinkTableMapper $linkMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $queryUpdate;
    private \PDOStatement $queryRead;
    private \PDOStatement $querySaved;
    private string $userID;

    //------------------------------------------------------

    public function __construct(PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_users', 'user_id');

        $this->linkMapper = $this->getMapper(DB_LinkTableMapper::class);

        //--------------------------------------------------
        $sql = "INSERT INTO geni_users (name, account_type, metadata_json, run_id)
                VALUES (:name, :account, :metadata, :run_id)";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        //--------------------------------------------------
        $sql = "UPDATE geni_users SET
                    name          = :name,
                    account_type  = :account,
                    metadata_json = :metadata
                WHERE run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        //--------------------------------------------------
        $sql = "SELECT * FROM geni_users WHERE run_id = :run_id";

        $this->queryRead = $pdo->prepare($sql);
        $this->queryRead->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        //--------------------------------------------------
        // Check if user saved to DB.

        $sql = "SELECT 1 FROM `geni_users` WHERE run_id = :run_id LIMIT 1";

        $this->querySaved = $pdo->prepare($sql);
        $this->querySaved->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        //--------------------------------------------------
        //--------------------------------------------------
        // Get user-ID. Doesn't change throughout the run.

        $sql = "SELECT user_id FROM geni_users WHERE run_id = :run_id";

        $queryUserID = $pdo->prepare($sql);
        $queryUserID->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $userRow      = $queryUserID->fetch(PDO::FETCH_ASSOC);
        $this->userID = $userRow ? (int) $userRow ['user_id'] : '0';
    }

    //------------------------------------------------------

    /**
     * Entry point for persistence.
     */
    public function save(API_User $user): void
    {
        if ($this->exists($this->getRunID()))
        {
            $this->update($user);
        }
        else
        {
            $this->create($user);
        }
    }

    /**
     * Create: Insert the user record and associated link table collections.
     */
    protected function create(API_User $user): void
    {
        $metadata = $user->isLoaded('metadata') ? json_encode($user->getMetadata()) : null;

        $this->executeWithBinds($this->queryInsert, [
            'name'     => $user->getName(),
            'account'  => $user->getAccountType(),
            'metadata' => $metadata
        ]);

        $this->userID = $this->getPDO()->lastInsertId();

        $this->saveCollections($user, $this->getUserID());
    }

    /**
     * Read the user for the current run_id context.
     */
    public function read(bool $fullRead = true): ?API_User
    {
        $this->executeWithBinds($this->queryRead, []);

        $userRow = $this->queryRead->fetch(PDO::FETCH_ASSOC);
        if (!$userRow)
        {
            return null;
        }

        $userID = $userRow ['user_id'];

        if (isset($userRow['metadata_json']))
        {
            $userRow['metadata'] = json_decode($userRow['metadata_json'], true) ?? [];
            unset($userRow['metadata_json']);
        }

        if ($fullRead)
        {
            $collections = $this->readCollections($userID);
            $userRow     = array_merge($userRow, $collections);
        }

        return API_User::newFromArray($userRow, fromDB: true);
    }

    /**
     * Update: Refresh user data and collections.
     */
    protected function update(API_User $user): void
    {
        if ($user->isLoaded('metadata'))
        {
            $metadata = json_encode($user->getMetadata());

            $this->executeWithBinds($this->queryUpdate, [
                'name'     => $user->getName(),
                'account'  => $user->getAccountType(),
                'metadata' => $metadata,
                'run_id'   => $this->getRunID()
            ]);
        }
        else
        {
            // If metadata is NOT loaded, update scalar fields without overwriting metadata_json
            $sql = "UPDATE geni_users SET
                        name         = :name,
                        account_type = :account
                    WHERE run_id = :run_id";

            $stmt = $this->getPDO()->prepare($sql);
            $this->executeWithBinds($stmt, [
                'name'     => $user->getName(),
                'account'  => $user->getAccountType(),
                'run_id'   => $this->getRunID()
            ]);
        }

        $userID = $this->getUserID();

        $this->saveCollections($user, $userID);
    }

    /**
     * Delete: Remove user and cleanup all link tables.
     */
    public function delete(): void
    {
        $userID = $this->getUserID();

        $this->deleteById($userID);

        $this->deleteCollections($userID);
    }

    //--------------------------------------------------

    private function getUserID(): string
    {
        return $this->userID;
    }

    public function setUserID(string $ID): void
    {
        $this->userID = $ID;
    }

    /**
     * Primary Shortcut: Check if a record exists by the main ID column.
     */
    protected function exists(mixed $id): bool
    {
        $this->executeWithBinds($this->querySaved, []);

        return (bool) $this->querySaved->fetchColumn();
    }

    /**
     * Collection Delegation: Maps the extensive list of user-followed and uploaded items.
     */
    private function saveCollections(API_User $user, string $userID): void
    {
        foreach (self::COLLECTIONS as $callAPI => $work)
        {
            if ($user->isLoaded($callAPI))
            {
                $tableName  = 'geni_user_' . str_replace('-', '_', $callAPI);
                $listValues = $user->{$work['get']}()->getList();

                $this->linkMapper->save($tableName, 'user_id', $userID, $work['col'], $listValues, true);
            }
        }

        if ($user->isLoaded('my-labels'))
        {
            $labels = $user->getMyLabels()->getList();

            $this->linkMapper->saveSequence('geni_user_labels', 'user_id', $userID, 'label', 'insert_order', $labels);
        }
    }

    private function deleteCollections(string $userID): void
    {
        foreach (array_keys(self::COLLECTIONS) as $callAPI)
        {
            $tableName = 'geni_user_' . str_replace('-', '_', $callAPI);

            $this->linkMapper->clear($tableName, 'user_id', $userID);
        }

        $this->linkMapper->clear('geni_user_labels', 'user_id', $userID);
    }

    /**
     * @param string $userID
     * @return array <string, array <string>>
     */
    private function readCollections(string $userID): array
    {
        $collections = [];

        foreach (self::COLLECTIONS as $callAPI => $work)
        {
            $tableName = 'geni_user_' . str_replace('-', '_', $callAPI);

            $values = $this->linkMapper->readCol($tableName, 'user_id', $userID, $work['col']);

            if (count($values) > 0)
            {
                $collections [$callAPI] = $values;
            }
        }

        $labels = $this->linkMapper->readCol('geni_user_labels', 'user_id', $userID, 'label', 'insert_order');

        if (count($labels) > 0)
        {
            $collections ['my-labels'] = $labels;
        }

        return $collections;
    }
}
