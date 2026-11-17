<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_DocumentImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_DocumentImageSizeMapMapper
 * Handles persistence for the API_DocumentImageSizeMap sub-object.
 * Stores URLs for various document image sizes.
 */
class DB_DocumentImageSizeMapMapper extends DB_BaseMapper
{
    private PDOStatement $queryInsert;

    /**
     * @param PDO $pdo
     * @param int $run_id
     */
    public function __construct(PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_document_sizes', 'id');

        $sql = "INSERT INTO geni_document_sizes (
                    url_large, url_origin, url_thumb, run_id)
                    VALUES (:large, :origin, :thumb, :run_id)";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Save logic for sub-objects.
     */
    public function save(API_DocumentImageSizeMap $sizeMap): int
    {
        return $this->create($sizeMap);
    }

    /**
     * Persist a new document size map.
     * @param API_DocumentImageSizeMap $sizeMap
     * @return int The generated ID.
     */
    public function create(API_DocumentImageSizeMap $sizeMap): int
    {
        $this->executeWithBinds($this->queryInsert, [
            'large'  => $sizeMap->getLarge(),
            'origin' => $sizeMap->getOrigin(),
            'thumb'  => $sizeMap->getThumb()
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    /**
     * Read a document size map by its internal INT ID.
     * @param int $id
     * @return API_DocumentImageSizeMap|NULL
     */
    public function read(int $id): ?API_DocumentImageSizeMap
    {
        $sizeArray = $this->readArray($id);

        return $sizeArray ? API_DocumentImageSizeMap::newFromArray($sizeArray) : null;
    }

    /**
     * Read a document size map by its internal INT ID.
     * @param int $id
     * @return array<string, string>|NULL
     */
    public function readArray(int|string $id): ?array
    {
        $row = parent::readArray($id);

        if (!$row)
        {
            return null;
        }

        if (isset($row['url_large']))
        {
            // Map database columns back to factory array keys
            $data = [
                'large'  => $row['url_large'],
                'origin' => $row['url_origin'],
                'thumb'  => $row['url_thumb']
            ];
        }
        else
        {
            $data = $row;
        }

        return $data;
    }

    /**
     * Delete a size map record.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
