<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_LocationMapper
 * Handles persistence for the API_Location sub-object.
 */
class DB_LocationMapper extends DB_BaseMapper
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
        parent::__construct($pdo, $run_id, 'geni_locations', 'id');

        $sql = "INSERT INTO geni_locations (
                    owner_id,
                    country, county, city, place_name, state,
                    street_address1, street_address2, street_address3,
                    longitude, latitude, formatted_location, country_code,
                    run_id
                ) VALUES (
                    :owner_id,
                    :country, :county, :city, :place_name, :state,
                    :street_address1, :street_address2, :street_address3,
                    :longitude, :latitude, :formatted_location, :country_code,
                    :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
    }

    //------------------------------------------------------

    /**
     * Save logic for sub-objects.
     */
    public function save(API_Location $location, string $ownerID): int
    {
        return $this->create($location, $ownerID);
    }

    /**
     * Persist a new location.
     * @param API_Location $location
     * @return int The generated ID.
     */
    public function create(API_Location $location, string $ownerID): int
    {
        $streetAddress = $location->getStreetAddress();

        $this->executeWithBinds($this->queryInsert, [
            'owner_id'           => $ownerID,
            'country'            => $location->getCountry(),
            'county'             => $location->getCounty(),
            'city'               => $location->getCity(),
            'place_name'         => $location->getPlaceName(),
            'state'              => $location->getState(),
            'street_address1'    => $streetAddress ['street_address1'] ?? '',
            'street_address2'    => $streetAddress ['street_address2'] ?? '',
            'street_address3'    => $streetAddress ['street_address3'] ?? '',
            'longitude'          => $location->getLongitude(),
            'latitude'           => $location->getLatitude(),
            'formatted_location' => $location->getFormattedLocation(),
            'country_code'       => $location->getCountryCode()
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    /**
     * Read a location by its internal INT ID.
     * @param int $id
     * @return API_Location|NULL
     */
    public function read(int $id): ?API_Location
    {
        $locationArray = $this->readArray($id);

        return $locationArray ? API_Location::newFromArray($locationArray) : null;
    }

    /**
     * Delete a location record.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->deleteById($id);
    }

    //------------------------------------------------------

    /**
     * Read a location by its internal INT ID.
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

        unset($row ['id']);
        unset($row ['owner_id']);
        unset($row ['run_id']);

        return $row;
    }
}
