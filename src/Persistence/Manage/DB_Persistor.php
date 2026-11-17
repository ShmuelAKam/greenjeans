<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use PDO;

class DB_Persistor
{
    private static ?DB_Persistor $instance = null;
    private DB_MapperFactory $factory;
    private DB_RunMapper     $runner;
    private PDO              $pdo;
    private int              $runID;

    private function __construct(PDO $pdo, string $username)
    {
        $this->pdo = $pdo;

        $clientID     = $this->getClientID($username);
        $this->runner = new DB_RunMapper($pdo, $clientID);

        $this->runID = $this->runner->getRunID();
        $profileID   = $this->runner->getProfileID();

        $this->factory = new DB_MapperFactory($pdo, $this->runID, $profileID);
    }

    public static function init(PDO $pdo, string $username): DB_Persistor
    {
        if (self::$instance !== null)
        {
            throw new \LogicException('DB_Persistor has already been initialized.');
        }

        self::$instance = new self($pdo, $username);

        return self::$instance;
    }

    public static function getInstance(): DB_Persistor
    {
        if (self::$instance === null)
        {
            throw new \LogicException('DB_Persistor must be initialized via init() before calling getInstance().');
        }

        return self::$instance;
    }

    //--------------------------------------------------------------

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

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    public function getRunID(): int
    {
        return $this->runID;
    }

    protected function getClientID(string $username): int
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
}
