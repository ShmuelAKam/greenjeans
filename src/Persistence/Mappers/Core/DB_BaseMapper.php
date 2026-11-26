<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Core;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;

abstract class DB_BaseMapper
{
    private PDO    $pdo;
    private int    $run_id;
    protected string $tableName;
    protected string $idColumn;
    private PDOStatement $queryExists;
    private PDOStatement $queryDeleteById;
    private PDOStatement $querySelect;

    /** @var array <string, \PDOStatement>
     * Cache for all dynamic column queries.
     * The array key format is: {$table}:{$column}
     */
    private array $dynamicStmts = [];

    /**
     * @var array <string, \PDOStatement>
     * Cache specific for the generic countTableRows queries. These use any
     * TABLE-column combinations. So they don't belong in $dynamicStmts. This
     * is also why this table is static (these queries can apply across mappers).
     * The array key format is: {$table}:{$column}
     */
    private static array $countTableStmts = [];

    /**
     * Constructor.
     * @param PDO    $pdo     Global PDO used throughout the persistence layer.
     * @param int    $run_id  defined at the beginning of the run. It is used
     *                        throughout the persistence layer to define a "unit
     *                        of work".
     * @param string $tableName
     * @param string $idColumn
     */
    protected function __construct(PDO $pdo, int $run_id, string $tableName, string $idColumn)
    {
        $this->pdo       = $pdo;
        $this->run_id    = $run_id;
        $this->tableName = $tableName;
        $this->idColumn  = $idColumn;

        if ($tableName != '')
        {
            // 1. Primary EXISTS
            $sql               = "SELECT 1 FROM `$tableName` WHERE `$idColumn` = :val AND run_id = :run_id LIMIT 1";
            $this->queryExists = $pdo->prepare($sql);

            // 2. Primary DELETE
            $sql                   = "DELETE FROM `$tableName` WHERE `$idColumn` = :val AND run_id = :run_id";
            $this->queryDeleteById = $pdo->prepare($sql);

            // 3. Select a full row.
            $sql               = "SELECT * FROM `$tableName` WHERE `$idColumn` = :val AND run_id = :run_id LIMIT 1";
            $this->querySelect = $pdo->prepare($sql);
        }
    }

    /**
     * Primary Shortcut: Check if a record exists by the main ID column.
     */
    protected function exists(mixed $id): bool
    {
        $this->executeWithBinds($this->queryExists, [':val' => $id]);

        return (bool) $this->queryExists->fetchColumn();
    }

    /**
     * Primary Shortcut: Delete a record by the main ID column.
     */
    public function deleteById(mixed $id): void
    {
        if (!$id)
        {
            return;
        }

        $this->executeWithBinds($this->queryDeleteById, [':val' => $id]);
    }

    /**
     * Generic Delete: Delete rows where $column = $value.
     * Useful for cleaning up child collections.
     */
    public function deleteBy(string $column, mixed $value): void
    {
        $sql  = "DELETE FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id";
        $stmt = $this->getDynamicStatement('del', $column, $sql);

        $this->executeWithBinds($stmt, [':val' => $value]);
    }

    /**
     * Generic Count: Count rows where $column = $value.
     * Useful for checking relationship density (e.g., photos in an album).
     */
    public function countBy(string $column, mixed $value): int
    {
        $sql  = "SELECT COUNT(*) FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id";
        $stmt = $this->getPDO()->prepare($sql);

        $this->executeWithBinds($stmt, [':val' => $value]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Counts rows in a generic table and column. Because this function uses
     * generic TABLE and column names, it caches its statements in a dedicates
     * table ($countTableStmts) that uses "{table}:{column}" as its key.
     * @param string $table
     * @param string $column
     * @param mixed  $value
     * @return int
     */
    public function countTableRows(string $table, string $column, mixed $value): int
    {
        $cacheKey = "{$table}:{$column}";

        if (!isset(self::$countTableStmts[$cacheKey]))
        {
            $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val AND run_id = :run_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':run_id', $this->run_id, PDO::PARAM_INT);

            self::$countTableStmts [$cacheKey] = $stmt;
        }

        $stmt = self::$countTableStmts [$cacheKey];
        $stmt->bindValue(':val', $value);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    protected function tableHasRows(string $table, string $column, mixed $value): bool
    {
        return ($this->countTableRows($table, $column, $value) > 0);
    }

    /**
     * Read a single row by the primary ID column.
     * @param int|string $id
     * @return array <string, string>|NULL
     */
    public function readArray(int|string $id): ?array
    {
        $this->executeWithBinds($this->querySelect, [ ':val' => $id ]);

        $row = $this->querySelect->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    //------------------------------------------------------

    /*
     * Returns raw associative arrays.
     */
    /**
     * Generic Read: Fetch all rows where $column =$value.
     * Optional $orderBy parameter ensures deterministic sorting.
     * @param string $column
     * @param mixed $value
     * @param string $orderBy
     * @param string $orderDir
     * @return array <array <string, string>>
     */
    protected function readAllBy(
        string  $column,
        mixed   $value,
        ?string $orderBy = null,
        string  $orderDir = 'ASC',
    ): array {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id";

        if ($orderBy !== null)
        {
            $dir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY `{$orderBy}` {$dir}";
        }

        $cacheKey = 'sel_all_';
        if ($orderBy !== null)
        {
            $cacheKey .= '_' . $orderBy . '_' . $orderDir;
        }

        $stmt = $this->getDynamicStatement($cacheKey, $column, $sql);

        $this->executeWithBinds($stmt, [':val' => $value]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generic Read Column: Fetch a single specified column for all rows where $column =$value.
     * Optional $orderBy parameter ensures deterministic sorting (defaults to$targetCol if not provided).
     * Returns a flat array of column values.
     *
     * @param string      $column    The WHERE clause column name.
     * @param mixed       $value     The value to match against.
     * @param string      $targetCol The column to SELECT and return.
     * @param string|null $orderBy   Optional column to sort by (defaults to$targetCol).
     * @param string      $orderDir  Sort direction ('ASC' or 'DESC').
     * @return array <string|int>     Flat array of scalar column values.
     */
    protected function readColBy(
        string  $column,
        mixed   $value,
        string  $targetCol,
        ?string $orderBy = null,
        string  $orderDir = 'ASC',
    ): array {
        $sortCol = $orderBy ?? $targetCol;
        $dir     = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT `{$targetCol}` FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id ORDER BY `{$sortCol}` {$dir}";

        $cacheKey = "sel_col_{$targetCol}_{$sortCol}_{$dir}";

        $stmt = $this->getDynamicStatement($cacheKey, $column, $sql);

        $this->executeWithBinds($stmt, [':val' => $value]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Rename column names from DB reply to object parameter names.
     * @param array <string, string> $rowArray Array returned from DB read query.
     * @param array <string, string> $columnMap  Map of needed name changes. For example
     * the document ID column in the database is called doc_id. But the
     * object calls it 'id'. So the map is:
     *      [ 'doc_id' => 'id', ... ]
     * @return array <string, string> the row with any required changes in key names.
     */
    protected function mapColumns(array $rowArray, array $columnMap): array
    {
        $mapped = [];

        foreach ($rowArray as $DBName => $DBValue)
        {
            $objectName = $columnMap [$DBName] ?? $DBName;

            $mapped [$objectName] = $DBValue;
        }

        return $mapped;
    }

    //------------------------------------------------------

    /**
     * Internal manager for the dynamic statement cache.
     */
    private function getDynamicStatement(string $prefix, string $column, string $sql): \PDOStatement
    {
        $key = $prefix . '_' . $column;

        if (!isset($this->dynamicStmts[$key]))
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':run_id', $this->getRunID(), PDO::PARAM_INT);

            $this->dynamicStmts[$key] = $stmt;
        }

        return $this->dynamicStmts[$key];
    }

    /**
     * Generic function to execute prepared query after binding all parameters, after
     * inferring their type. Adds the 'run_id' param to all queries.
     *
     * @param PDOStatement $stmt
     * @param array <string, mixed> $params
     * @return bool
     */
    protected function executeWithBinds(PDOStatement $stmt, array $params, bool $bindRunID = true): bool
    {
        if ($bindRunID)
        {
            $params ['run_id'] = $this->getRunID();
        }

        foreach ($params as $key => $value)
        {
            // Default to string
            $type = PDO::PARAM_STR;

            // Infer the type dynamically
            if (is_bool($value))
            {
                $type = PDO::PARAM_BOOL;
            }
            elseif (is_null($value))
            {
                $type = PDO::PARAM_NULL;
            }
            elseif (is_int($value))
            {
                $type = PDO::PARAM_INT;
            }

            // Ensure keys have colons.
            $bindKey = str_starts_with($key, ':') ? $key : ':' . $key;

            $stmt->bindValue($bindKey, $value, $type);
        }

        return $stmt->execute();
    }

    protected function getPDO(): PDO
    {
        return $this->pdo;
    }

    protected function getRunID(): int
    {
        return $this->run_id;
    }

    protected function getMapper(string|object $target): mixed
    {
        return DB_Persistor::getInstance()->getMapper($target);
    }

    //------------------------------------------------------

    public function dumpTable(PDO $pdo, string $tableName, string $id = '', string $idColumn = 'rowid'): void
    {
        // Build query using double quotes for SQLite identifier escaping
        if ($id !== '')
        {
            $sql  = "SELECT * FROM `$tableName` WHERE `$idColumn` = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        }
        else
        {
            $sql  = "SELECT * FROM `$tableName`";
            $stmt = $pdo->query($sql);
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows))
        {
            echo "No data found in table \"{$tableName}\".\n";
            return;
        }

        // Fetch column headers
        $columns = array_keys($rows[0]);

        // Calculate maximum width required for each column
        $colWidths = [];
        foreach ($columns as $col)
        {
            $colWidths[$col] = strlen($col);
        }

        foreach ($rows as $row)
        {
            foreach ($columns as $col)
            {
                $valLen = strlen((string) ($row[$col] ?? 'NULL'));
                if ($valLen > $colWidths[$col])
                {
                    $colWidths[$col] = $valLen;
                }
            }
        }

        // Generate ASCII border line
        $border = '+';
        foreach ($colWidths as $width)
        {
            $border .= str_repeat('-', $width + 2) . '+';
        }
        $border .= "\n";

        $numRows = count($rows);
        echo "\n\nTable: $tableName ($numRows rows):\n";

        // Print Header
        echo $border . '|';
        foreach ($columns as $col)
        {
            echo ' ' . str_pad($col, $colWidths[$col]) . ' |';
        }
        echo "\n" . $border;

        // Print Rows
        foreach ($rows as $row)
        {
            echo '|';
            foreach ($columns as $col)
            {
                $val = $row[$col] ?? 'NULL';
                echo ' ' . str_pad((string) $val, $colWidths[$col]) . ' |';
            }
            echo "\n";
        }

        echo $border;
    }
}
