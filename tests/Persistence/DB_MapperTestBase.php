<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use PDO;
use PHPUnit\Framework\TestCase;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryLocations;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperComments;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperDates;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperDocuments;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperEvents;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperLocations;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperNames;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperPhoneNumbers;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperPhotoAlbums;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperPhotos;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperProfiles;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperProjects;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperRelations;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperRevisions;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperSizeMaps;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperSurnames;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperTags;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperUnions;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperUsers;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperVideos;

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

        global $commentMapFactory,  $dateMapFactory,   $eventMapFactory,  $locationMapFactory,
        $nameMapFactory,     $phoneMapFactory,  $tagMapFactory,    $sizeMapFactory;

        /**
         * These are factories that create objects WITHOUT calling the Geni server.
         *
         * @var FactoryMapperComments     $commentMapFactory
         * @var FactoryMapperDates        $dateMapFactory
         * @var FactoryMapperEvents       $eventMapFactory
         * @var FactoryMapperLocations    $locationMapFactory
         * @var FactoryMapperNames        $nameMapFactory
         * @var FactoryMapperPhoneNumbers $phoneMapFactory
         * @var FactoryMapperTags         $tagMapFactory
         * @var FactoryMapperSizeMaps     $sizeMapFactory
         */

        $commentMapFactory  = $GLOBALS ['commentMapFactory'];
        $dateMapFactory     = $GLOBALS ['dateMapFactory'];
        $eventMapFactory    = $GLOBALS ['eventMapFactory'];
        $locationMapFactory = $GLOBALS ['locationMapFactory'];
        $nameMapFactory     = $GLOBALS ['nameMapFactory'];
        $phoneMapFactory    = $GLOBALS ['phoneMapFactory'];
        $tagMapFactory      = $GLOBALS ['tagMapFactory'];
        $sizeMapFactory     = $GLOBALS ['sizeMapFactory'];

        global $docMapFactory,      $photoMapFactory,  $albumMapFactory,     $profileMapFactory,
        $projectMapFactory,  $relationFactory,  $revisionMapFactory,  $videoMapFactory,
        $surnameMapFactory,  $unionMapFactory,  $userMapFactory;

        /**
         * @var FactoryMapperDocuments    $docMapFactory
         * @var FactoryMapperPhotos       $photoMapFactory
         * @var FactoryMapperVideos       $videoMapFactory
         * @var FactoryMapperPhotoAlbums  $albumMapFactory
         * @var FactoryMapperProfiles     $profileMapFactory
         * @var FactoryMapperProjects     $projectMapFactory
         * @var FactoryMapperRelations    $relationFactory
         * @var FactoryMapperRevisions    $revisionMapFactory
         * @var FactoryMapperSurnames     $surnameMapFactory
         * @var FactoryMapperUnions       $unionMapFactory
         * @var FactoryMapperUsers        $userMapFactory
         */
        $docMapFactory      = $GLOBALS ['docMapFactory'];
        $photoMapFactory    = $GLOBALS ['photoMapFactory'];
        $albumMapFactory    = $GLOBALS ['albumMapFactory'];
        $profileMapFactory  = $GLOBALS ['profileMapFactory'];
        $projectMapFactory  = $GLOBALS ['projectMapFactory'];
        $relationFactory    = $GLOBALS ['relationFactory'];
        $revisionMapFactory = $GLOBALS ['revisionMapFactory'];
        $videoMapFactory    = $GLOBALS ['videoMapFactory'];
        $surnameMapFactory  = $GLOBALS ['surnameMapFactory'];
        $unionMapFactory    = $GLOBALS ['unionMapFactory'];
        $userMapFactory     = $GLOBALS ['userMapFactory'];
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
