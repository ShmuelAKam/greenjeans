<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use PDO;
use PHPUnit\Framework\TestCase;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryLocations;

/**
 * DB_MapperTestBase
 * Abstract base providing the infrastructure and Unit-of-Work isolation
 * for all DB_Mapper tests using SQLite memory database.
 */
abstract class DB_MapperTestBase extends TestCase
{
    protected PDO          $pdo;
    protected int          $runID;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->restoreGlobals();

        $persistor   = DB_Persistor::getInstance();
        $this->runID = $persistor->getRunID();
        $this->pdo   = $persistor->getPDO();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    //--------------------------------------------------------------

    /**
     * For some fragged reason phpunit "looses" the global variables at the
     * beginning of every test. This function enables them to be found again.
     * I do NOT want to recreate the factories every time, because some of
     * them keep internal counters.
     */
    private function restoreGlobals(): void
    {
        global $dateFactory, $locationFactory;

        /**
         * These are global factory objects to create test objects used by the
         * various test classes. These specific factories do NOT call the API,
         * because dates and locations are always parts of larger objects that
         * have their own API.
         *
         * @var FactoryDates     $dateFactory
         * @var FactoryLocations $locationFactory
         */
        $dateFactory     = $GLOBALS ['dateFactory'];
        $locationFactory = $GLOBALS ['locationFactory'];
    }

    //--------------------------------------------------------------

    protected function objectsErrMsg(mixed $expected, mixed $actual, string $fieldName = ''): string
    {
        return "$fieldName does not match:\r\n"
               . "Expected:\r\n" . print_r($expected, true)
               . "Actual:\r\n" . print_r($actual, true);
    }

    protected function getPDO(): PDO
    {
        return $this->pdo;
    }

    protected function getRunID(): int
    {
        return $this->runID;
    }

    protected function getMapper(string|object $target): mixed
    {
        return DB_Persistor::getInstance()->getMapper($target);
    }

    /**
     * Dumps a SQLite table's contents as a formatted text table to the shell.
     *
     * @param PDO    $pdo       Active SQLite PDO connection
     * @param string $tableName Table name to dump
     * @param string $id        Optional ID/ROWID to filter by
     * @param string $idColumn  Column name for the filter (defaults to 'rowid')
     * @return void
     */
    public static function dumpTable(PDO $pdo, string $tableName, string $id = '', string $idColumn = 'rowid'): void
    {
        // Build query using double quotes for SQLite identifier escaping
        if ($id !== '')
        {
            $sql = "SELECT * FROM `$tableName` WHERE `$idColumn` = :id";

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
