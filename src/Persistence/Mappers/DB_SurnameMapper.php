<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Surname;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;

/**
 * DB_SurnameMapper
 * Handles persistence for the API_Surname object.
 * Manages collections for profiles and followers.
 */
class DB_SurnameMapper extends DB_BaseMapper
{
    private DB_LinkTableMapper $linkMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $queryUpdate;

    //------------------------------------------------------

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_surnames', 'surname_id');

        $this->linkMapper = $persistor->getMapper(DB_LinkTableMapper::class);

        $sql = "INSERT INTO geni_surnames (
                    surname_id, slugged_name, description, url,
                    follower_count, profile_count, run_id
                ) VALUES (
                    :id, :slug, :desc, :url,
                    :f_count, :p_count, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "UPDATE geni_surnames SET
                    slugged_name = :slug, description = :desc,
                    url = :url, follower_count = :f_count,
                    profile_count = :p_count
                WHERE surname_id = :id AND run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);

    }

    /**
     * Main persistence entry point.
     */
    public function save(API_Surname $surname): string
    {
        if ($this->exists($surname->getID()))
        {
            $this->update($surname);
        }
        else
        {
            $this->create($surname);
        }

        return $surname->getID();
    }

    /**
     * Create: Insert the parent record and its associated lists.
     */
    protected function create(API_Surname $surname): void
    {
        $followerCount = $surname->isLoaded('followers') ? $surname->getFollowerCount() : -1;
        $profileCount  = $surname->isLoaded('profiles') ? $surname->getProfileCount() : -1;

        $this->executeWithBinds($this->queryInsert, [
            'id'      => $surname->getID(),
            'slug'    => $surname->getSluggedName(),
            'desc'    => $surname->getDescription(),
            'url'     => $surname->getURL(),
            'f_count' => $followerCount,
            'p_count' => $profileCount
        ]);

        $this->saveCollections($surname);
    }

    /**
     * Read: Retrieve a surname by its API ID.
     */
    public function read(string $surnameID, bool $fullRead): ?API_Surname
    {
        $row = $this->readArray($surnameID);

        if (!$row)
        {
            return null;
        }

        // Map DB columns back to factory keys
        $data = [
            'id'           => $row['surname_id'],
            'slugged_name' => $row['slugged_name'],
            'description'  => $row['description'],
            'url'          => $row['url']
        ];

        if ($fullRead)
        {
            $followerIDs = $this->linkMapper->readCol('geni_surname_followers', 'surname_id', $surnameID, 'profile_id');

            if (count($followerIDs) > 0)
            {
                $data ['followers'] = $followerIDs;
            }

            $profileIDs = $this->linkMapper->readCol('geni_surname_profiles', 'surname_id', $surnameID, 'profile_id');

            if (count($profileIDs) > 0)
            {
                $data ['profiles'] = $profileIDs;
            }
        }

        return API_Surname::newFromArray($data, fromDB: true);
    }

    /**
     * Update: Refresh parent record and update collections.
     */
    protected function update(API_Surname $surname): void
    {
        $followerCount = $surname->isLoaded('followers') ? $surname->getFollowerCount() : -1;
        $profileCount  = $surname->isLoaded('profiles') ? $surname->getProfileCount() : -1;

        $this->executeWithBinds($this->queryUpdate, [
            'slug'    => $surname->getSluggedName(),
            'desc'    => $surname->getDescription(),
            'url'     => $surname->getURL(),
            'f_count' => $followerCount,
            'p_count' => $profileCount,
            'id'      => $surname->getID()
        ]);

        $this->saveCollections($surname);
    }

    /**
     * Delete: Remove record (cascades to links).
     */
    public function delete(string $surnameID): void
    {
        $this->deleteById($surnameID);

        $this->deleteCollections($surnameID);
    }

    /**
     * Collection Delegation: Links followers and profiles to the surname.
     */
    private function saveCollections(API_Surname $surname): void
    {
        $surnameID = $surname->getID();

        if ($surname->isLoaded('followers'))
        {
            /** @var array <string> $followersList */
            $followersList = array_keys($surname->getFollowers()->getList());

            $this->linkMapper->save(
                'geni_surname_followers',
                'surname_id',
                $surnameID,
                'profile_id',
                $followersList,
                true,
            );
        }

        if ($surname->isLoaded('profiles'))
        {
            /** @var array <string> $profilesList */
            $profilesList = array_keys($surname->getProfiles()->getList());

            $this->linkMapper->save(
                'geni_surname_profiles',
                'surname_id',
                $surnameID,
                'profile_id',
                $profilesList,
                true,
            );
        }
    }

    private function deleteCollections(string $surnameID): void
    {
        $this->linkMapper->clear('geni_surname_followers', 'surname_id', $surnameID);
        $this->linkMapper->clear('geni_surname_profiles', 'surname_id', $surnameID);
    }
}
