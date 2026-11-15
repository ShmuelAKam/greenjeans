<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use PDO;

/**
 * Dumps a SQLite table's contents as a formatted text table to the shell.
 *
 * @param PDO    $pdo       Active SQLite PDO connection
 * @param string $tableName Table name to dump
 * @param string $id        Optional ID/ROWID to filter by
 * @param string $idColumn  Column name for the filter (defaults to 'rowid')
 * @return void
 */
function dumpTable(PDO $pdo, string $tableName, string $id = '', string $idColumn = 'rowid'): void
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
        echo "No data found in table `$tableName`.\n";
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
