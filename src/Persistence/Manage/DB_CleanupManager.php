<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use PDO;

/**
 * DB_CleanupManager
 * Handles the removal of old synchronization data.
 */
class DB_CleanupManager
{
    public function __construct(private PDO $pdo) {}

    /**
     * Delete a specific run by ID.
     * This triggers ON DELETE CASCADE across the entire schema.
     */
    public function deleteRun(int $runId): int
    {
        $sql  = "DELETE FROM geni_runs WHERE run_id = :runId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['runId' => $runId]);

        return $stmt->rowCount();
    }

    /**
     * Keep only the most recent X runs and delete the rest.
     * Useful for maintaining a rolling cache of data.
     */
    public function keepLastRuns(int $keepCount = 5): int
    {
        $keepCount = max(0, $keepCount);

        // Find the IDs of runs to delete (everything except the top $keepCount)

        // MySQL allows offset only if limit is supplied.
        // 2147483647 (max 32-bit int, ~2.1 billion) is fully supported by both SQLite
        // and MySQL without overflow.
        $limit = 2147483647;

        $sql = "SELECT run_id FROM geni_runs ORDER BY created_at DESC
            LIMIT {$limit} OFFSET :keep";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':keep', $keepCount, PDO::PARAM_INT);
        $stmt->execute();
        $idsToDelete = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($idsToDelete))
        {
            return 0;
        }

        // Perform the delete
        $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
        $sql          = "DELETE FROM geni_runs WHERE run_id IN ($placeholders)";
        $delStmt      = $this->pdo->prepare($sql);
        $delStmt->execute($idsToDelete);

        return $delStmt->rowCount();
    }

    /**
     * Delete runs older than a specific date.
     */
    public function deleteRunsOlderThan(string $interval = '30 days'): int
    {
        // Normalize in case someone passes '30 DAY' or '30 days'
        $cleanedInterval = strtolower(trim($interval));
        if (!str_ends_with($cleanedInterval, 's') && !str_ends_with($cleanedInterval, 'month'))
        {
            $cleanedInterval .= 's';
        }

        $cutoff = (new \DateTime())->modify("-{$cleanedInterval}")->format('Y-m-d H:i:s');

        $sql  = "DELETE FROM geni_runs WHERE created_at < :cutoff";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cutoff' => $cutoff]);

        return $stmt->rowCount();
    }
}

// =================================================================
// USAGE EXAMPLE
// =================================================================

/**
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geni_cache", "root", "password");
    $cleaner = new DB_CleanupManager($pdo);

    // Scenario A: Keep only the 3 most recent syncs
    $deletedCount = $cleaner->keepLastRuns(3);
    echo "🧹 Cleaned up $deletedCount old runs and all their associated data.\n";

    // Scenario B: Wipe everything older than a week
    // $deletedCount = $cleaner->deleteRunsOlderThan('7 DAY');

} catch (Exception $e) {
    echo "❌ Cleanup failed: " . $e->getMessage();
}
*/
