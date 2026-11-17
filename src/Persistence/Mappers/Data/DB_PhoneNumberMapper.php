<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_PhoneNumber;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_PhoneNumberMapper
 * Handles persistence for the API_PhoneNumber sub-object.
 * Sub-objects use INT AUTO_INCREMENT IDs and are stored in geni_phone_numbers.
 */
class DB_PhoneNumberMapper extends DB_BaseMapper
{
    private PDOStatement $queryInsert;

    /**
     * @param PDO $pdo
     * @param int $run_id
     */
    public function __construct(PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_phone_numbers', 'id');

        $sql = "INSERT INTO geni_phone_numbers (owner_id, area_code, country_code, number, type, run_id)
                VALUES (:owner_id, :area_code, :country_code, :number, :type, :run_id)";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Save logic for sub-objects.
     */
    public function save(API_PhoneNumber $phoneNumber, string $ownerID): int
    {
        return $this->create($phoneNumber, $ownerID);
    }

    /**
     * Persist a new phone number.
     * @param API_PhoneNumber $phoneNumber
     * @return int The generated ID.
     */
    public function create(API_PhoneNumber $phoneNumber, string $ownerID): int
    {
        $this->executeWithBinds($this->queryInsert, [
            'owner_id'     => $ownerID,
            'area_code'    => $phoneNumber->getAreaCode(),
            'country_code' => $phoneNumber->getCountryCode(),
            'number'       => $phoneNumber->getNumber(),
            'type'         => $phoneNumber->getType()
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    /**
     * Read a phone number by its internal INT ID.
     * @param int $id
     * @return API_PhoneNumber|NULL
     */
    public function read(int $id): ?API_PhoneNumber
    {
        $phoneArray = $this->readArray($id);

        return ($phoneArray !== null) ? API_PhoneNumber::newFromArray($phoneArray) : null;
    }

    /**
     * Delete a phone number record.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->deleteById($id);
    }

    //------------------------------------------------------

    /**
     * Save array of phone numbers belonging to object.
     * @param array <API_PhoneNumber> $phoneNumbers
     * @param string $ownerID
     */
    public function createAll(array  $phoneNumbers, string $ownerID): void
    {
        $this->deleteAll($ownerID);

        foreach ($phoneNumbers as $number)
        {
            $this->create($number, $ownerID);
        }
    }

    /**
     * Read all phone numbers belonging to owner.
     * @param  string $ownerID
     * @return array <API_PhoneNumber> objects (or empty).
     */
    public function readAll(string $ownerID): array
    {
        $rows = $this->readAllBy('owner_id', $ownerID);

        return array_map(fn($row) => API_PhoneNumber::newFromArray($row), $rows);
    }

    /**
     * Delete all phone numbers belonging to object.
     * @param string $ownerID
     */
    public function deleteAll(string $ownerID): void
    {
        $this->deleteBy('owner_id', $ownerID);
    }
}
