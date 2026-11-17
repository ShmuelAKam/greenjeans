<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_VideoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_VideoImageSizeMapMapper
 * Handles persistence for the API_VideoImageSizeMap sub-object.
 * Stores the various preview/thumbnail URLs associated with a Video.
 */
class DB_VideoImageSizeMapMapper extends DB_BaseMapper
{
    private PDOStatement $queryInsert;

    /**
     * @param PDO $pdo
     * @param int $run_id
     */
    public function __construct(PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_video_sizes', 'id');

        $sql = "INSERT INTO geni_video_sizes (
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
    public function save(API_VideoImageSizeMap $sizeMap): int
    {
        return $this->create($sizeMap);
    }

    /**
     * Persist a new video size map.
     * @param API_VideoImageSizeMap $sizeMap
     * @return int The generated ID.
     */
    public function create(API_VideoImageSizeMap $sizeMap): int
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
     * Read a video size map by its internal INT ID.
     * @param int $id
     * @return API_VideoImageSizeMap|NULL
     */
    public function read(int|string $id): ?API_VideoImageSizeMap
    {
        $sizeArray = $this->readArray($id);

        return $sizeArray ? API_VideoImageSizeMap::newFromArray($sizeArray) : null;
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
     * Read a video size map by its internal INT ID.
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
