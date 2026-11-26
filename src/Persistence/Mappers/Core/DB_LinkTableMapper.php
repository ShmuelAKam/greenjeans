<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Core;

use PDO;
use PDOStatement;

/**
 * This class handles ALL link tables by creating cached statements for all
 * link-table queries. These statements are identified by a key made from the
 * type of query, table details, and order specifications. It has CRUD functions
 * for table operations with deterministic read ordering.
 *
 * While this class is derived from DB_BaseMapper, it does NOT make much use of
 * it, except for the executeWithBinds function, and the getters  for runID and
 * PDO. This is because the baseclass handles only a single table.
 *
 * @see DB_LinkTableMapper::getCachedStmt
 * @see DB_BaseMapper::executeWithBinds
 * @author shmuel
 */
class DB_LinkTableMapper extends DB_BaseMapper
{
    /**
     * @var array<string, PDOStatement> Internal cache for various queries to
     * the link tables, that are created at runtime.
     * @see DB_LinkTableMapper::getCachedStmt
     */
    private array $linkStmts = [];

    //------------------------------------------------------

    /**
     * Constructor.
     * @param PDO $pdo     Global PDO used throughout the persistence layer.
     * @param int $run_id  defined at the beginning of the run. Is used throughout
     *                     the persistence layer to define a "unit of work".
     */
    public function __construct(PDO $pdo, int $run_id)
    {
        // This object does not use a single table name or column,
        // so leave those parameters blank.

        parent::__construct($pdo, $run_id, '', '');
    }

    /**
     * Internal helper to create and cache statements by their structural fingerprint.
     *
     * @param 'insert'|'select'|'row'|'clear'|'count' $type The SQL operation type.
     * @param string  $table     Table name
     * @param string  $c1        Key   column to operate by.
     * @param string  $c2        Value column to insert or select.
     * @param string  $orderBy   Sort  column, for sequences.
     * @param string  $orderDir  Sort  order: 'DESC' or 'ASC'
     * @return PDOStatement
     */
    private function getCachedStmt(
        string  $type,
        string  $table,
        string  $c1,
        string  $c2 = '',
        ?string $orderBy = null,
        string  $orderDir = 'ASC',
    ): PDOStatement {

        $dirKey = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $key    = "{$type}_{$table}_{$c1}_{$c2}";

        if ($orderBy !== null)
        {
            // Add sequence parameters to key when defined.
            $key .= "_{$orderBy}_{$dirKey}";
        }

        if (!isset($this->linkStmts[$key]))
        {
            // Relevant statement NOT found. Create it.

            $orderClause = "";
            if ($orderBy !== null)
            {
                $orderClause = " ORDER BY `{$orderBy}` {$dirKey}";
            }

            $sql = match ($type)
            {
                // Get SQL statement for current parameters. This is an internal
                // function, so no 'SQL insertion' is possible.

                'insert' => "INSERT INTO `$table` (`$c1`, `$c2`, run_id) VALUES (:key, :val, :run_id)",
                'select' => "SELECT `$c2` FROM `$table` WHERE `$c1` = :key AND run_id = :run_id {$orderClause}",
                'row'    => "SELECT *     FROM `$table` WHERE `$c1` = :key AND run_id = :run_id {$orderClause}",
                'clear'  => "DELETE       FROM `$table` WHERE `$c1` = :key AND run_id = :run_id",
                'count'  => "SELECT COUNT(*) FROM `$table` WHERE `$c1` = :key AND run_id = :run_id",
            };

            $stmt = $this->getPDO()->prepare($sql);
            $stmt->bindValue(':run_id', $this->getRunID(), PDO::PARAM_INT);

            $this->linkStmts [$key] = $stmt;
        }

        return $this->linkStmts [$key];
    }

    //------------------------------------------------------
    // Crud operations that use cached stmt objects.

    /**
     * CREATE /UPDATE: Set values to link table, with optional delete first.
     *
     * @param string  $table  Name of table.
     * @param string  $keyCol Key   column
     * @param string  $key    Key   value.
     * @param string  $column Value column.
     * @param array<string, mixed>|array <string>|null  $items  Values to enter.
     * @param bool    $clear  Delete existing rows before insert (update).
     */
    public function save(
        string $table,
        string $keyCol,
        string $key,
        string $column,
        ?array $items,
        bool   $clear,
    ): void {
        if ($clear)
        {
            $this->clear($table, $keyCol, $key);
        }

        if (!empty($items))
        {
            $stmt = $this->getCachedStmt('insert', $table, $keyCol, $column);

            // For each row in array of values.
            foreach ($items as $itemID => $item)
            {
                if (is_bool($item))
                {
                    $id = $itemID;
                }
                elseif (is_string($item))
                {
                    $id = $item;
                }
                else
                {
                    // $item is a class object with getID()

                    $id = $item->getID() ?? null;
                }

                if ($id !== null)
                {
                    $this->executeWithBinds($stmt, [
                        ':key' => $key,
                        ':val' => $id,
                    ]);
                }
            }
        }
    }

    /**
     * CREATE /UPDATE: Set values to link table with sequence column. It ALWAYS
     * first clears any previous sequence for the same parameters.
     *
     * @param string  $table  Name of table.
     * @param string  $keyCol Key   column.
     * @param string  $key    Key   value.
     * @param string  $column Value column.
     * @param string  $seqCol Column to insert sequence value into.
     * @param array<string, string>|null  $items  Values to enter.
     */
    public function saveSequence(
        string $table,
        string $keyCol,
        string $key,
        string $column,
        string $seqCol,
        ?array $items,
    ): void {
        $this->clear($table, $keyCol, $key);

        if (!empty($items))
        {
            $sql  = "INSERT INTO `$table` (`$keyCol`, `$column`, `$seqCol`, run_id) VALUES (:key, :val, :seq, :run_id)";
            $stmt = $this->getPDO()->prepare($sql);

            foreach ($items as $itemID => $item)
            {
                if ($item != null)
                {
                    $this->executeWithBinds($stmt, [
                        ':key' => $key,
                        ':val' => $item,
                        ':seq' => $itemID
                    ]);
                }
            }
        }
    }

    /**
     * READ: single column of values from link-table (multiple rows).
     * Automatically defaults to sorting by target column for deterministic retrieval.
     *
     * @param string      $table    Name of table.
     * @param string      $keyCol   Column containing key.
     * @param string      $key      Key value.
     * @param string      $valCol   Column to return.
     * @param string|null $orderBy  Column to sort by (defaults to $valCol).
     * @param string      $orderDir Sort direction ('ASC' or 'DESC').
     * @return array <string> Values that match key.
     */
    public function readCol(
        string  $table,
        string  $keyCol,
        string  $key,
        string  $valCol,
        ?string $orderBy  = null,
        string  $orderDir = 'ASC',
    ): array {
        $sortCol = $orderBy ?? $valCol;
        $stmt    = $this->getCachedStmt('select', $table, $keyCol, $valCol, $sortCol, $orderDir);

        $this->executeWithBinds($stmt, [':key' =>$key]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * READ: get complete rows from link-table (multiple rows).
     *
     * @param string      $table    Name of table.
     * @param string      $keyCol   Column containing key (e.g., 'profile_id').
     * @param string      $key      The actual ID value to look up.
     * @param string|null $orderBy  Optional column to sort rows by.
     * @param string      $orderDir Sort direction ('ASC' or 'DESC').
     * @return array <array<string, string>> Full associative rows that match the key.
     */
    public function readRow(
        string  $table,
        string  $keyCol,
        string  $key,
        ?string $orderBy  = null,
        string  $orderDir = 'ASC',
    ): array {
        $stmt = $this->getCachedStmt('row', $table, $keyCol, '', $orderBy, $orderDir);

        $this->executeWithBinds($stmt, [':key' =>$key]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * DELETE: Remove key-specific rows from link-table.
     *
     * @param string $table  Name of table.
     * @param string $keyCol Column containing key.
     * @param string $key    Key value.
     */
    public function clear(string $table, string $keyCol, string $key): void
    {
        $stmt = $this->getCachedStmt('clear', $table, $keyCol);

        $this->executeWithBinds($stmt, [':key' =>$key]);
    }
}
