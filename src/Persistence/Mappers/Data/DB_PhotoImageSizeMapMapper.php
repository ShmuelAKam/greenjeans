<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_PhotoImageSizeMapMapper
 * Handles persistence for the API_PhotoImageSizeMap sub-object.
 * Stores the various preview/thumbnail URLs associated with a Photo.
 */
class DB_PhotoImageSizeMapMapper extends DB_BaseMapper
{
    private PDOStatement $queryInsert;

    /**
     * @param DB_Persistor $persistor
     * @param PDO $pdo
     * @param int $run_id
     * @phpstan-ignore constructor.unusedParameter
     */
    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_photo_sizes', 'id');

        $sql = "INSERT INTO geni_photo_sizes (
                    url_large, url_medium, url_origin, url_print,
                    url_small, url_thumb, url_thumb2, run_id
                ) VALUES (
                    :large, :medium, :origin, :print,
                    :small, :thumb, :thumb2, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Save logic for sub-objects.
     */
    public function save(API_PhotoImageSizeMap $sizeMap): int
    {
        return $this->create($sizeMap);
    }

    /**
     * Persist a new photo size map.
     * @param API_PhotoImageSizeMap $sizeMap
     * @return int The generated ID.
     */
    public function create(API_PhotoImageSizeMap $sizeMap): int
    {
        $this->executeWithBinds($this->queryInsert, [
            'large'  => $sizeMap->getLarge(),
            'medium' => $sizeMap->getMedium(),
            'origin' => $sizeMap->getOrigin(),
            'print'  => $sizeMap->getPrint(),
            'small'  => $sizeMap->getSmall(),
            'thumb'  => $sizeMap->getThumb(),
            'thumb2' => $sizeMap->getThumb2()
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    /**
     * Read a photo size map by its internal INT ID.
     * @param int $id
     * @return API_PhotoImageSizeMap|NULL
     */
    public function read(int|string $id): ?API_PhotoImageSizeMap
    {
        $sizeArray = $this->readArray($id);

        return $sizeArray ? API_PhotoImageSizeMap::newFromArray($sizeArray) : null;
    }

    /**
     * Delete a size map record.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->deleteById($id);
    }

    /**
     * Read a photo size map by its internal INT ID.
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
            // Map database columns back to factory array keys. It does NOT make
            // sense to call the mapColumns function, because this is a small
            // object and ALL of the columns need to be changed.
            $data = [
                'large'  => $row['url_large'],
                'medium' => $row['url_medium'],
                'origin' => $row['url_origin'],
                'print'  => $row['url_print'],
                'small'  => $row['url_small'],
                'thumb'  => $row['url_thumb'],
                'thumb2' => $row['url_thumb2']
            ];
        }
        else
        {
            $data = $row;
        }

        return $data;
    }
}
