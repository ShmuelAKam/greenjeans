<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Project;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;

/**
 * DB_ProjectMapper
 * Handles persistence for the API_Project primary object.
 * Manages collections for profiles, collaborators, and followers.
 */
class DB_ProjectMapper extends DB_BaseMapper
{
    private DB_LinkTableMapper $linkMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $queryUpdate;

    //------------------------------------------------------

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_projects', 'project_id');

        $this->linkMapper = $persistor->getMapper(DB_LinkTableMapper::class);

        $sql = "INSERT INTO geni_projects (
                    project_id, name, description, created_at,
                    updated_at, url, run_id
                ) VALUES (
                    :id, :name, :desc, :created,
                    :updated, :url, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "UPDATE geni_projects SET
                    name = :name, description = :desc,
                    created_at = :created, updated_at = :updated,
                    url = :url
                WHERE project_id = :id AND run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Entry point for persistence.
     */
    public function save(API_Project $project, bool $fullSave): string
    {
        if ($this->exists($project->getID()))
        {
            $this->update($project, $fullSave);
        }
        else
        {
            $this->create($project, $fullSave);
        }

        return $project->getID();
    }

    /**
     * Create: Insert the parent record and its associated lists.
     */
    protected function create(API_Project $project, bool $fullSave): void
    {
        $this->executeWithBinds($this->queryInsert, [
            'id'      => $project->getID(),
            'name'    => $project->getName(),
            'desc'    => $project->getDescription(),
            'created' => $project->getCreatedAt(),
            'updated' => $project->getUpdatedAt(),
            'url'     => $project->getURL()
        ]);

        $this->saveCollections($project, $fullSave);
    }

    public function read(string $projectID, bool $fullRead): ?API_Project
    {
        static $docColumnMap = [
            'project_id' => 'id',
        ];

        $projectRow = $this->readArray($projectID);

        if ($projectRow === null)
        {
            return null;
        }

        $projectRow = $this->mapColumns($projectRow, $docColumnMap);

        if ($fullRead)
        {
            // Read Collaborators, Followers, and Profiles.

            $collabIDs = $this->linkMapper->readCol('geni_project_collaborators', 'project_id', $projectID, 'profile_id');

            if (count($collabIDs) > 0)
            {
                $projectRow ['collaborators'] = $collabIDs;
            }

            $followerIDs = $this->linkMapper->readCol('geni_project_followers', 'project_id', $projectID, 'profile_id');

            if (count($followerIDs) > 0)
            {
                $projectRow ['followers'] = $followerIDs;
            }

            $profileIDs = $this->linkMapper->readCol('geni_project_profiles', 'project_id', $projectID, 'profile_id');

            if (count($profileIDs) > 0)
            {
                $projectRow ['profiles'] = $profileIDs;
            }
        }

        return API_Project::newFromArray($projectRow, true);
    }

    /**
     * Update: Refresh parent record and update collections.
     */
    protected function update(API_Project $project, bool $fullSave): void
    {
        $this->executeWithBinds($this->queryUpdate, [
            'name'    => $project->getName(),
            'desc'    => $project->getDescription(),
            'created' => $project->getCreatedAt(),
            'updated' => $project->getUpdatedAt(),
            'url'     => $project->getURL(),
            'id'      => $project->getID()
        ]);

        $this->saveCollections($project, $fullSave);
    }

    /**
     * Delete: Remove parent and associated collections.
     */
    public function delete(string $projectID): void
    {
        $this->deleteById($projectID);
        $this->deleteCollections($projectID);
    }

    /**
     * Collection Delegation: Persists project-profile associations.
     */
    private function saveCollections(API_Project $project, bool $fullSave): void
    {
        $projectID = $project->getID();

        // Persist Profiles list
        if ($project->isLoaded('profiles'))
        {
            $this->linkMapper->save(
                'geni_project_profiles',
                'project_id',
                $projectID,
                'profile_id',
                $project->getProfiles()->getListIDs(),
                true,
            );
        }

        // Persist Collaborators list
        if ($project->isLoaded('collaborators'))
        {
            $this->linkMapper->save(
                'geni_project_collaborators',
                'project_id',
                $projectID,
                'profile_id',
                $project->getCollaborators()->getListIDs(),
                true,
            );
        }

        // Persist Followers list
        if ($project->isLoaded('followers'))
        {
            $this->linkMapper->save(
                'geni_project_followers',
                'project_id',
                $projectID,
                'profile_id',
                $project->getFollowers()->getListIDs(),
                true,
            );
        }
    }

    private function deleteCollections(string $ownerID): void
    {
        $this->linkMapper->clear('geni_project_profiles', 'project_id', $ownerID);
        $this->linkMapper->clear('geni_project_collaborators', 'project_id', $ownerID);
        $this->linkMapper->clear('geni_project_followers', 'project_id', $ownerID);
    }
}
