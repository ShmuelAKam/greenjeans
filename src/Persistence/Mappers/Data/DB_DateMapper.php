<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_DateMapper
 * Handles persistence for the API_Date sub-object.
 */
class DB_DateMapper extends DB_BaseMapper
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
        parent::__construct($pdo, $run_id, 'geni_dates', 'id');

        $sql = "INSERT INTO geni_dates (
                    owner_id,
                    circa, day, month, year,
                    end_circa, end_day, end_month, end_year,
                    date_range, run_id
                ) VALUES (
                    :owner_id,
                    :circa, :day, :month, :year,
                    :end_circa, :end_day, :end_month, :end_year,
                    :range, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    //------------------------------------------------------

    public function save(API_Date $date, string $ownerID): int
    {
        return $this->create($date, $ownerID);
    }

    /**
     * Persist a new date.
     * @param API_Date $date
     * @param string   $ownerID ID string of object that owns this date.
     * @return int     The generated ID.
     */
    public function create(API_Date $date, string $ownerID): int
    {
        $v = $date->makeParamArray();

        $isCirca    = (bool) ($v['circa'] ?? false);
        $isEndCirca = (bool) ($v['end_circa'] ?? false);

        $this->executeWithBinds($this->queryInsert, [
            'owner_id'  => $ownerID,
            'circa'     => $isCirca,
            'day'       => $v['day'] ?? null,
            'month'     => $v['month'] ?? null,
            'year'      => $v['year'] ?? null,

            'end_circa' => $isEndCirca,
            'end_day'   => $v['end_day'] ?? null,
            'end_month' => $v['end_month'] ?? null,
            'end_year'  => $v['end_year'] ?? null,

            'range'     => $v['range'] ?? null
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    /**
     * Read a date by its internal INT ID.
     * @param int $id
     * @return API_Date|NULL
     */
    public function read(int $id): ?API_Date
    {
        $dateArray = $this->readArray($id);

        return $dateArray ? API_Date::newFromArray($dateArray) : null;
    }

    /**
     * Delete a date record.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->deleteById($id);
    }

    //------------------------------------------------------

    /**
     * Read a date by its internal INT ID.
     * @param int $id
     * @return array<string, string>|NULL
     */
    public function readArray(int|string $id): ?array
    {
        /**
         * @var array <string, string|NULL> $row
         */
        $row = parent::readArray($id);

        if (!$row)
        {
            return null;
        }

        unset($row ['id']);
        unset($row ['owner_id']);
        unset($row ['run_id']);

        // Because ALL values are optional, we must remove all of the NULL
        // value columns, so that they don't get added to the object.

        $row = array_filter($row, fn($value) => $value !== null);

        // circa and end_circa should only be in the object if SET to 1.

        if (isset($row['circa']) && $row['circa'] == 0)
        {
            unset($row['circa']);
        }

        if (isset($row['end_circa']) && $row['end_circa'] == 0)
        {
            unset($row['end_circa']);
        }

        // Rename 'date_range' column to object's 'date'.

        if (isset($row['date_range']))
        {
            $row['range'] = $row['date_range'];
            unset($row['date_range']);
        }

        return $row;
    }
}
