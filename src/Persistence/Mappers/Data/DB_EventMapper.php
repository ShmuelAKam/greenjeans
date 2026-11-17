<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_EventMapper
 * Handles persistence for the API_Event sub-object.
 * Delegates Date and Location persistence to their respective mappers.
 */
class DB_EventMapper extends DB_BaseMapper
{
    private DB_DateMapper     $dateMapper;
    private DB_LocationMapper $locationMapper;
    private PDOStatement $queryInsert;
    private PDOStatement $querySelectIDs;

    public function __construct(PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_events', 'id');

        $this->dateMapper     = $this->getMapper(DB_DateMapper::class);
        $this->locationMapper = $this->getMapper(DB_LocationMapper::class);

        $sql = "INSERT INTO geni_events (owner_id, name, description, date_id, location_id, run_id)
                VALUES (:owner_id, :name, :description, :date_id, :location_id, :run_id)";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "SELECT date_id, location_id FROM geni_events WHERE id = :id AND run_id = :run_id";

        $this->querySelectIDs = $pdo->prepare($sql);
        $this->querySelectIDs->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    //------------------------------------------------------

    /**
     * Save logic for sub-objects.
     */
    public function save(API_Event $event, string $ownerID): int
    {
        return $this->create($event, $ownerID);
    }

    /**
     * Create: Persist sub-objects first, then insert parent with returned IDs.
     * @param API_Event $event
     * @return int The generated event ID.
     */
    public function create(API_Event $event, string $ownerID): int
    {
        $dateId     = null;
        $locationId = null;

        $eventDate = $event->getDate();

        if ($eventDate)
        {
            $dateId = $this->dateMapper->create($eventDate, $ownerID);
        }

        $eventLocation = $event->getLocation();
        if ($eventLocation)
        {
            $locationId = $this->locationMapper->create($eventLocation, $ownerID);
        }

        $this->executeWithBinds($this->queryInsert, [
            'owner_id'    => $ownerID,
            'name'        => $event->getName(),
            'description' => $event->getDescription(),
            'date_id'     => $dateId,
            'location_id' => $locationId
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    /**
     * Read: Fetch event and hydrate sub-objects via their mappers.
     * @param int $id
     * @return API_Event|NULL
     */
    public function read(int $id): ?API_Event
    {
        // This readArray also reads the arrays of the sub-objects date and location.
        $rowArray = $this->readArray($id);

        if (!$rowArray)
        {
            return null;
        }

        unset($rowArray ['id']);
        unset($rowArray ['owner_id']);
        unset($rowArray ['run_id']);

        $date     = null;
        $location = null;

        if (isset($rowArray['date']))
        {
            $date = API_Date::newFromArray($rowArray ['date']);
        }

        if (isset($rowArray['location']))
        {
            $location = API_Location::newFromArray($rowArray['location']);
        }

        // Construct using factory-style logic compatible with the constructor
        return new API_Event($rowArray['name'], $rowArray['description'], $date, $location);
    }

    /**
     * Read: Fetch event and hydrate sub-objects via their mappers.
     * @param int $id
     * @return array<string, string|array<string, string>>|NULL
     */
    public function readArray(int|string $id): ?array
    {
        $row = parent::readArray($id);

        unset($row ['id']);
        unset($row ['owner_id']);
        unset($row ['run_id']);

        if (!$row)
        {
            return null;
        }

        if ($row['date_id'])
        {
            $row['date'] = $this->dateMapper->readArray((int) $row['date_id']);
            unset($row['date_id']);
        }

        if ($row['location_id'])
        {
            $row['location'] = $this->locationMapper->readArray((int) $row['location_id']);
            unset($row['location_id']);
        }

        return $row;
    }

    /**
     * Delete: Fetch sub-object IDs, delete parent, then delete sub-objects.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->executeWithBinds($this->querySelectIDs, ['id'  => $id]);
        $row = $this->querySelectIDs->fetch(PDO::FETCH_ASSOC);

        // Select for sub objects returned NULL. Event obviously deleted already.

        if (!$row)
        {
            return;
        }

        // Delete parent first
        $this->deleteById($id);

        // Clean up sub-objects
        if ($row['date_id'])
        {
            $this->dateMapper->delete((int) $row['date_id']);
        }

        if ($row['location_id'])
        {
            $this->locationMapper->delete((int) $row['location_id']);
        }
    }

    //------------------------------------------------------

    /**
     * Save array of events belonging to owner (Profile or Union object).
     * Events are sorted chronologically before saving.
     *
     * @param array<API_Event|array<string,string>> $events
     * @param string                 $ownerID
     */
    public function createAll(array $events, string $ownerID): void
    {
        if (empty($events))
        {
            return;
        }

        $this->deleteAll($ownerID);

        $eventObjects = [];

        foreach ($events as $event)
        {
            $eventObjects[] = is_object($event) ? $event : API_Event::newFromArray($event);
        }

        API_Event::sortChronologically($eventObjects);

        foreach ($eventObjects as $eventObject)
        {
            $this->create($eventObject, $ownerID);
        }
    }

    /**
     * Read all events belonging to owner (Profile or Union object).
     * Returns an array of API_Event objects sorted chronologically by date.
     *
     * @param  string $ownerID
     * @return array <API_Event> Array of API_Event objects (or empty array).
     */
    public function readAll(string $ownerID): array
    {
        $eventIDs = $this->readColBy('owner_id', $ownerID, 'id');
        $events   = [];

        foreach ($eventIDs as $eventID)
        {
            $eventArray = $this->readArray($eventID);

            unset($eventArray ['id']);
            unset($eventArray ['owner_id']);
            unset($eventArray ['run_id']);

            $events [] = API_Event::newFromArray($eventArray);
        }

        API_Event::sortChronologically($events);

        return $events;
    }

    /**
     * Delete all events belonging to owner (Profile object).
     * @param string $ownerID
     */
    public function deleteAll(string $ownerID): void
    {
        $this->deleteBy('owner_id', $ownerID);
    }
}
