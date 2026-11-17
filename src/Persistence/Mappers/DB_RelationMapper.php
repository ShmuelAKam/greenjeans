<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\Data\API_Relation;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_RelationMapper
 * Manages the persistence of individual relationship steps.
 */
class DB_RelationMapper extends DB_BaseMapper
{
    private \PDOStatement $querySave;

    /**
     * @param PDO $pdo
     * @param int $run_id
     */
    public function __construct(PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_relations', 'parent_id');

        // Simple Insert. No Update logic needed.
        $sql = "INSERT INTO geni_relations (parent_id, name, relation_type, url, sequence_order, run_id)
                VALUES (:pid, :name, :rel, :url, :seq, :run_id)";

        $this->querySave = $pdo->prepare($sql);
        $this->querySave->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Save a relation step.
     * Since there are no updates, we just fire the insert.
     */
    public function save(string $parentId, API_Relation $relation, int $sequence): void
    {
        $this->executeWithBinds($this->querySave, [
            'pid'  => $parentId,
            'name' => $relation->getName(),
            'rel'  => $relation->getRelation(),
            'url'  => $relation->getUrl(),
            'seq'  => $sequence
        ]);
    }

    /**
     * Fetch all relation steps for a parent, ordered by sequence.
     * @return array <API_Relation>
     */
    public function findByParentId(string $parentId): array
    {
        // Use the inherited readAllBy from the BaseMapper!
        // It already filters by parent_id and run_id.
        $rows = $this->readAllBy('parent_id', $parentId, 'sequence_order');

        return array_map(fn($row) => API_Relation::newFromArray([
            'name'     => $row['name'],
            'relation' => $row['relation_type'],
            'url'      => $row['url']
        ]), $rows);
    }

    /**
     * Persist a list of relation steps for a parent object.
     *
     * @param array<API_Relation> $relations
     * @param string              $parentId
     * @param bool                $clear    Whether to wipe existing relations for this parent first.
     */
    public function createAll(array $relations, string $parentId, bool $clear = true): void
    {
        if ($clear)
        {
            $this->deleteAll($parentId);
        }

        $sequence = 1;
        foreach ($relations as $relation)
        {
            $this->save($parentId, $relation, $sequence++);
        }
    }

    /**
     * Delete all relation steps belonging to a parent object within the current run scope.
     *
     * @param string $parentId
     */
    public function deleteAll(string $parentId): void
    {
        $sql  = "DELETE FROM geni_relations WHERE parent_id = :pid AND run_id = :run_id";
        $stmt = $this->getPDO()->prepare($sql);

        $this->executeWithBinds($stmt, [
            'pid' => $parentId
        ]);
    }
}
