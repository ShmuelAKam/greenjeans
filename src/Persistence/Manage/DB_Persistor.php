<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use PDO;

class DB_Persistor
{
    private DB_MapperFactory $factory;
    private DB_RunMapper     $runner;
    private PDO              $pdo;
    private int              $runID;

    public function __construct(PDO $pdo, string $username)
    {
        $this->pdo = $pdo;

        $clientID     = $this->getClientID($username);
        $this->runner = new DB_RunMapper($pdo, $clientID);

        $this->runID = $this->runner->getRunID();
        $profileID   = $this->runner->getProfileID();

        $this->factory = new DB_MapperFactory($pdo, $this->runID, $profileID);
    }

    public function __destruct()
    {
        // SKULL remove this from real code.
        $this->cleanupUnitOfWork($this->runID);
    }

    public function getMapper(string|object $target): mixed
    {
        return $this->factory->getMapper($this, $target);
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

    //--------------------------------------------------------------
    // SKULL this function should NOT EXIST. It is only for test runs.

    /**
     * Dynamic Cleanup: Wipes all records matching the test's run_id across all tables.
     */
    private function cleanupUnitOfWork(int $runId): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $sql = match ($driver)
        {
            'sqlite' => "SELECT name FROM sqlite_master
                     WHERE type = 'table' AND name NOT LIKE 'sqlite_%'",

            'mysql'  => "SELECT table_name AS name FROM information_schema.tables
                     WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'",

            default  => throw new \RuntimeException("Unsupported PDO driver: {$driver}"),
        };

        $tablesStmt = $this->pdo->query($sql);
        $tables     = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table)
        {
            // Do NOT delete the meta-data.
            if ($table === 'geni_runs'
                || $table === 'geni_clients')
            {
                continue;
            }

            // Delete records belonging strictly to this run_id
            $sql  = "DELETE FROM `{$table}` WHERE run_id = :rid";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':rid' => $runId]);
        }
    }
}
