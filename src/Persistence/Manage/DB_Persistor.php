<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use PDO;

/**
 * Is the main object of the persistor layer. As such it is a singleton. All
 * operational functions pass through it. This is done by wrapping instances of
 * the DB_MapperFactory and DB_RunMapper classes, which are NOT accessed directly
 * by the user.
 *
 * Of course once the user receives a specific mapper that is the interface for
 * those functions.
 */
class DB_Persistor
{
    /**
     * @var DB_Persistor|null
     */
    private static ?DB_Persistor $instance = null;

    /**
     * @var DB_MapperFactory
     */
    private DB_MapperFactory $factory;

    /**
     * @var DB_RunMapper
     */
    private DB_RunMapper     $runner;

    /**
     * @var \PDO This is the global PDO object used throughout the package.
     */
    private PDO     $pdo;

    /**
     * @var int run_ID defined at the beginning of the run. Is used throughout
     *          the persistence layer to define a "unit of work".
     */
    private int     $runID;

    //--------------------------------------------------------------

    /**
     * Private constructor which is called by init function to create singleton.
     *
     * @param PDO    $pdo      Global PDO used throughout the persistence layer.
     * @param string $username Identifies user, as determined by start-up script.
     *                         Used to determine user and profile IDs. These are
     *                         used to create a run.
     */
    private function __construct(PDO $pdo, string $username)
    {
        $this->pdo = $pdo;

        $clientID     = $this->getClientID($username);
        $this->runner = new DB_RunMapper($pdo, $clientID);

        $this->runID = $this->runner->getRunID();
        $profileID   = $this->runner->getProfileID();

        $this->factory = new DB_MapperFactory($pdo, $this->runID, $profileID);
    }

    //--------------------------------------------------------------

    /**
     * Create DB_Persistor singleton.
     * @param PDO    $pdo      Global PDO used throughout the persistence layer.
     * @param string $username Identifies user, as determined by start-up script.
     *                         Used to determine user and profile IDs. These are
     *                         used to create a run.
     * @return DB_Persistor
     */
    public static function init(PDO $pdo, string $username): DB_Persistor
    {
        if (self::$instance !== null)
        {
            throw new \LogicException('DB_Persistor has already been initialized.');
        }

        self::$instance = new self($pdo, $username);

        return self::$instance;
    }

    /**
     * @return DB_Persistor
     */
    public static function getInstance(): DB_Persistor
    {
        if (self::$instance === null)
        {
            throw new \LogicException('DB_Persistor must be initialized via init() before calling getInstance().');
        }

        return self::$instance;
    }

    private function getClientID(string $username): int
    {
        $sql  = "SELECT client_id FROM `geni_clients` WHERE username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':username' => $username]);

        $row      = $stmt->fetch(PDO::FETCH_ASSOC);
        $clientID = -1;

        if ($row)
        {
            $clientID = $row ['client_id'];
        }

        return (int) $clientID;
    }

    //--------------------------------------------------------------
    // Passthrough functions to the factory and runner.

    public function getMapper(string|object $target): mixed
    {
        return $this->factory->getMapper($target);
    }

    public function complete(): bool
    {
        return $this->runner->complete();
    }

    public function fail(): bool
    {
        return $this->runner->fail();
    }

    //--------------------------------------------------------------
    // Getters.

    /**
     * Get global PDO object. The user should not really be accessing this object,
     * except maybe for user defined transactions. This function exists for the
     * TEST code.
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    public function getRunID(): int
    {
        return $this->runID;
    }
}
