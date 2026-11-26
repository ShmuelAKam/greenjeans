<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use PDO;

/**
 * Manages the run_id table, which represents "runs", which are a "unit of work"
 * created for each run of the script. Creates and completes runs, and also has
 * a function to get the profile-ID of the user running the script.
 *
 * This class is NOT accessed by the user, as it is wrapped by DB_Persistor,
 * which is the main interface to the Persistence layer.
 * @internal
 */
class DB_RunMapper
{
    /**
     * @var \PDO This is the global PDO object used throughout the package.
     */
    private PDO     $pdo;

    /**
     * @var int run_ID defined at the beginning of the run. Is used throughout
     *          the persistence layer to define a "unit of work".
     */
    private int     $runID;

    /**
     * @var int     USER ID of the client running the script.
     */
    private int     $clientID;


    /**
     * @var int     Epoch time. Kept for maintanence and to calculate duration.
     */
    private int     $startTime;

    /**
     * @var bool    Run has ended.
     */
    private bool    $complete;

    /**
     * @var string|null This profileID of the user running the script, is needed
     *                  by the DB_UserMapper.
     */
    private ?string $profileID = null;

    //--------------------------------------------------------------

    /**
     * Constructor.
     * @param PDO  $pdo      Global PDO used throughout the persistence layer.
     * @param int  $clientID ID of the client from the geni_clients table.
     */
    public function __construct(PDO $pdo, int $clientID)
    {
        $this->pdo       = $pdo;
        $this->clientID  = $clientID;

        $this->runID     = $this->create();

        $this->profileID = $this->findProfileID();
    }

    public function __destruct()
    {
        $this->complete();
    }

    //--------------------------------------------------------------

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
     * Delete run.
     * This triggers ON DELETE CASCADE across the entire schema.
     */
    public function delete(): int
    {
        $sql  = "DELETE FROM geni_runs WHERE run_id = :runId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['runId' => $this->runId]);

        return $stmt->rowCount();
    }

    //--------------------------------------------------------------

    /**
     * Gets the Profile-ID of the Client.
     * This is done using a join on the geni_runs and the geni_clients tables.
     */
    private function findProfileID(): string
    {
        $sql = "SELECT g.*, c.profile_id
                FROM geni_runs g
                JOIN geni_clients c ON g.client_id = c.client_id
                WHERE g.run_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ $this->getRunID() ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $profileID = -1;
        if ($row)
        {
            $profileID = $row ['profile_id'];
        }

        return $profileID;
    }

    //--------------------------------------------------------------

    public function getProfileID(): string
    {
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
