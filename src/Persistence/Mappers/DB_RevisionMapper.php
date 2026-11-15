<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Revision;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_RevisionMapper
 * Handles persistence for the API_Revision object.
 * Revisions are read-only from the API but persisted for local tracking/history.
 */
class DB_RevisionMapper extends DB_BaseMapper
{
    private \PDOStatement $queryInsert;
    private \PDOStatement $queryUpdate;

    //------------------------------------------------------

    /**
     * @param DB_Persistor $persistor
     * @param PDO $pdo
     * @param int $run_id
     * @phpstan-ignore constructor.unusedParameter
     */
    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_revisions', 'revision_id');

        $sql = "INSERT INTO geni_revisions (
                    revision_id, guid, action, story,
                    date_local, time_local, server_timestamp, run_id
                ) VALUES (
                    :id, :guid, :action, :story,
                    :date_l, :time_l, :ts, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "UPDATE geni_revisions SET
                    guid = :guid, action = :action, story = :story,
                    date_local = :date_l, time_local = :time_l,
                    server_timestamp = :ts
                WHERE revision_id = :id AND run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Entry point for persistence.
     */
    public function save(API_Revision $revision): string
    {
        if ($this->exists($revision->getID()))
        {
            $this->update($revision);
        }
        else
        {
            $this->create($revision);
        }

        return $revision->getID();
    }

    /**
     * Create: Insert the revision record.
     */
    protected function create(API_Revision $revision): void
    {
        $this->executeWithBinds($this->queryInsert, [
            'id'       => $revision->getID(),
            'guid'     => $revision->getGUID(),
            'action'   => $revision->getAction(),
            'story'    => $revision->getStory(),
            'date_l'   => $revision->getDateLocal(),
            'time_l'   => $revision->getTimeLocal(),
            'ts'       => $revision->getTimestamp()
        ]);
    }

    /**
     * Read: Retrieve a revision by its API ID.
     */
    public function read(string $revisionID): ?API_Revision
    {
        $row = $this->readArray($revisionID);

        if (!$row)
        {
            return null;
        }

        // Map DB columns back to expected factory keys
        $data = [
            'id'         => $row['revision_id'],
            'guid'       => $row['guid'],
            'action'     => $row['action'],
            'story'      => $row['story'],
            'date_local' => $row['date_local'],
            'time_local' => $row['time_local'],
            'timestamp'  => $row['server_timestamp']
        ];

        return API_Revision::newFromArray($data);
    }

    /**
     * Update: Refresh existing revision record.
     */
    protected function update(API_Revision $revision): void
    {
        $this->executeWithBinds($this->queryUpdate, [
            'guid'     => $revision->getGUID(),
            'action'   => $revision->getAction(),
            'story'    => $revision->getStory(),
            'date_l'   => $revision->getDateLocal(),
            'time_l'   => $revision->getTimeLocal(),
            'ts'       => $revision->getTimestamp(),
            'id'       => $revision->getID()
        ]);
    }

    /**
     * Delete: Remove a revision record.
     */
    public function delete(string $revisionID): void
    {
        $this->deleteById($revisionID);
    }
}
