<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Union;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_EventMapper;

/**
 * DB_UnionMapper
 * Handles persistence for the API_Union object.
 * Delegates marriage and divorce events to DB_EventMapper.
 * Manages collections of partners and consolidated children via Link Tables.
 */
class DB_UnionMapper extends DB_BaseMapper
{
    private DB_EventMapper     $eventMapper;
    private DB_LinkTableMapper $linkMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $querySelectIDs;
    private \PDOStatement $queryUpdate;
    private \PDOStatement $queryInsertChildren;

    //------------------------------------------------------

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_unions', 'union_id');

        $this->eventMapper = $persistor->getMapper(DB_EventMapper::class);
        $this->linkMapper  = $persistor->getMapper(DB_LinkTableMapper::class);

        $sql = "INSERT INTO geni_unions (union_id, status, marriage_id, divorce_id, run_id)
                VALUES (:id, :status, :marriage_id, :divorce_id, :run_id)";
        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql                  = "SELECT marriage_id, divorce_id FROM geni_unions WHERE union_id = :union_id AND run_id = :run_id";
        $this->querySelectIDs = $pdo->prepare($sql);
        $this->querySelectIDs->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "UPDATE geni_unions SET
                    status = :status, marriage_id = :marriage_id, divorce_id = :divorce_id
                WHERE union_id = :id AND run_id = :run_id";
        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql                       = "INSERT INTO geni_union_children (union_id, profile_id, child_type, run_id) VALUES (:union_id, :profile_id, :type, :run_id)";
        $this->queryInsertChildren = $pdo->prepare($sql);
        $this->queryInsertChildren->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    //------------------------------------------------------

    /**
     * Entry point for persistence. Wraps logic in a transaction.
     */
    public function save(API_Union $union): string
    {
        if ($this->exists($union->getID()))
        {
            $this->update($union);
        }
        else
        {
            $this->create($union);
        }

        return $union->getID();
    }

    /**
     * Create: Persist event sub-objects first, then the parent union.
     */
    protected function create(API_Union $union): void
    {
        $unionID = $union->getID();

        $marriageId = $union->getMarriage() ? $this->eventMapper->create($union->getMarriage(), $unionID) : null;
        $divorceId  = $union->getDivorce() ? $this->eventMapper->create($union->getDivorce(), $unionID) : null;

        $this->executeWithBinds($this->queryInsert, [
            'id'          => $unionID,
            'status'      => $union->getStatus(),
            'marriage_id' => $marriageId,
            'divorce_id'  => $divorceId
        ]);

        $this->saveCollections($union);
    }

    public function read(string $unionID): ?API_Union
    {
        static $unionColumnMap = [
            'union_id'       => 'id',
        ];

        $unionRow = $this->readArray($unionID);

        if ($unionRow === null)
        {
            return null;
        }

        $unionRow = $this->mapColumns($unionRow, $unionColumnMap);

        if (!$unionRow)
        {
            return null;
        }

        if (isset($unionRow ['marriage_id']))
        {
            $unionRow ['marriage'] = $this->eventMapper->readArray($unionRow ['marriage_id']);
            unset($unionRow ['marriage_id']);
        }

        if (isset($unionRow ['divorce_id']))
        {
            $unionRow ['divorce'] = $this->eventMapper->readArray($unionRow ['divorce_id']);
            unset($unionRow ['divorce_id']);
        }

        $collections = $this->readCollections($unionID);

        $fullRow = array_merge($unionRow, $collections);

        return API_Union::newFromArray($fullRow);
    }

    /**
     * Update: Follows fetch-old / create-new / update-parent / delete-old pattern.
     */
    protected function update(API_Union $union): void
    {
        $unionID = $union->getID();

        $this->executeWithBinds($this->querySelectIDs, ['union_id' => $unionID]);

        $old = $this->querySelectIDs->fetch(PDO::FETCH_ASSOC);

        $marriageId = $union->getMarriage() ? $this->eventMapper->create($union->getMarriage(), $unionID) : null;
        $divorceId  = $union->getDivorce() ? $this->eventMapper->create($union->getDivorce(), $unionID) : null;

        $this->executeWithBinds($this->queryUpdate, [
            'status'      => $union->getStatus(),
            'marriage_id' => $marriageId,
            'divorce_id'  => $divorceId,
            'id'          => $unionID
        ]);

        $this->saveCollections($union);

        if ($old && $old['marriage_id'])
        {
            $this->eventMapper->delete((int) $old['marriage_id']);
        }

        if ($old && $old['divorce_id'])
        {
            $this->eventMapper->delete((int) $old['divorce_id']);
        }
    }

    /**
     * Delete: Removes parent and cleans up event sub-objects.
     */
    public function delete(string $unionID): void
    {
        $this->executeWithBinds($this->querySelectIDs, ['union_id' => $unionID]);

        $row = $this->querySelectIDs->fetch(PDO::FETCH_ASSOC);

        if (!$row)
        {
            return;
        }

        $this->deleteById($unionID);

        if ($row['marriage_id'])
        {
            $this->eventMapper->delete((int) $row['marriage_id']);
        }

        if ($row['divorce_id'])
        {
            $this->eventMapper->delete((int) $row['divorce_id']);
        }

        $this->deleteCollections($unionID);
    }

    /**
     * Collection Delegation: Partners and consolidated Children using child_type indicator.
     */
    private function saveCollections(API_Union $union): void
    {
        $unionID = $union->getID();

        // 1. Partners
        $this->linkMapper->save('geni_union_partners', 'union_id', $unionID, 'profile_id', $union->getPartners(), true);

        // 2. Children
        $this->linkMapper->clear('geni_union_children', 'union_id', $unionID);

        foreach ($union->getChildren() as $childId)
        {
            $this->executeWithBinds($this->queryInsertChildren, [
                'union_id'   => $unionID,
                'profile_id' => $childId,
                'type'       => 'biological'
            ]);
        }

        foreach ($union->getAdoptedChildren() as $childId)
        {
            $this->executeWithBinds($this->queryInsertChildren, [
                'union_id'   => $unionID,
                'profile_id' => $childId,
                'type'       => 'adopted'
            ]);
        }

        foreach ($union->getFosterChildren() as $childId)
        {
            $this->executeWithBinds($this->queryInsertChildren, [
                'union_id'   => $unionID,
                'profile_id' => $childId,
                'type'       => 'foster'
            ]);
        }
    }

    private function deleteCollections(string $unionID): void
    {
        $this->linkMapper->clear('geni_union_partners', 'union_id', $unionID);
        $this->linkMapper->clear('geni_union_children', 'union_id', $unionID);
    }

    /**
     * @param string $unionID
     * @return array<string, array <string>|array <string, string>>
     */
    private function readCollections(string $unionID): array
    {
        /**
         * @var array<string, array <string>|array <string, string>> $collections
         */
        $collections = [];

        // 1. Partners
        /** array <string> */
        $collections ['partners'] = $this->linkMapper->readCol('geni_union_partners', 'union_id', $unionID, 'profile_id');

        // 2. Children

        /** @var array <array <string, string>> $allChildren */
        $allChildren = $this->linkMapper->readRow('geni_union_children', 'union_id', $unionID);

        if ($allChildren)
        {
            $childrenBiological = [];
            $childrenAdopted    = [];
            $childrenFoster     = [];

            // If single row returned wrap, or iterate if multi-row
            $rows = isset($allChildren[0]) ? $allChildren : [ $allChildren ];

            foreach ($rows as $childRow)
            {
                if (!isset($childRow['child_type']))
                {
                    continue;
                }

                switch ($childRow['child_type'])
                {
                    case 'biological': $childrenBiological[] = $childRow['profile_id'];
                        break;

                    case 'adopted':    $childrenAdopted[] = $childRow['profile_id'];
                        break;

                    case 'foster':     $childrenFoster[] = $childRow['profile_id'];
                        break;
                }
            }

            if (count($childrenBiological) > 0)
            {
                $collections ['children'] = $childrenBiological;
            }

            if (count($childrenAdopted) > 0)
            {
                $collections ['adopted_children'] = $childrenAdopted;
            }

            if (count($childrenFoster) > 0)
            {
                $collections ['foster_children'] = $childrenFoster;
            }
        }

        return $collections;
    }
}
