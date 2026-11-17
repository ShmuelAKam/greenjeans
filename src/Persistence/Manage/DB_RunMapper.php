<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use PDO;

class DB_RunMapper
{
    private PDO    $pdo;
    private int    $runID;
    private int    $clientID;
    private int    $startTime;
    private bool   $complete;
    private ?string $profileID = null;

    public function __construct(PDO $pdo, int $clientID)
    {
        $this->pdo      = $pdo;
        $this->clientID = $clientID;

        $this->runID = $this->create();
    }

    public function __destruct()
    {
        $this->complete();
    }

    /**
     * CREATE: Persists a new run.
     */
    private function create(): int
    {
        $this->startTime = time();
        $this->complete  = false;

        $sql  = "INSERT INTO geni_runs (client_id, start_time) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);

        $success = $stmt->execute([ $this->getClientID(), $this->startTime ]);

        $runID = -1;
        if ($success)
        {
            $runID = (int) $this->pdo->lastInsertId();
        }

        return $runID;
    }

    /**
     * COMPLETE: Closes run, logging duration and memory usage.
     */
    public function complete(): bool
    {
        return $this->endRun();
    }

    /**
     * FAIL: Closes run, logging duration and memory usage.
     */
    public function fail(): bool
    {
        return $this->endRun(failed: true);
    }

    private function endRun(bool $failed = false): bool
    {
        if ($this->complete)
        {
            // Run already eneded.
            return true;
        }

        $this->complete = true;

        $durationS     = max(0, time() - $this->startTime);
        $peakBytes     = memory_get_peak_usage(true);
        $memoryAllocMB = (int) round($peakBytes / 1024 / 1024);

        $sql = "UPDATE geni_runs
                SET duration_s = ?, memory_alloc_mb = ?, run_fail = ?, run_complete = TRUE
                WHERE run_id = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $durationS,
            $memoryAllocMB,
            (int) $failed,
            $this->getRunId()
        ]);
    }

    /**
     * Gets the Profile-ID of the Client.
     * This is done using a join on the geni_runs and the geni_clients tables.
     */
    public function getProfileID(): string
    {
        $sql = "SELECT g.*, c.profile_id
                FROM geni_runs g
                JOIN geni_clients c ON g.client_id = c.client_id
                WHERE g.run_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ $this->getRunID() ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row)
        {
            $this->profileID = $row ['profile_id'];
        }

        return $this->profileID;
    }

    public function getRunID(): int
    {
        return $this->runID;
    }

    public function getClientID(): int
    {
        return $this->clientID;
    }
}
