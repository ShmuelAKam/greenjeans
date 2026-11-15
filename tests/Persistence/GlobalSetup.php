<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

$GLOBALS ['PACKAGE_ROOT']  = dirname(__DIR__, 2);
$GLOBALS ['TEST_API_ROOT'] = realpath($GLOBALS ['PACKAGE_ROOT'] . '/tests/Api');

require_once $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';

use PDO;
use RuntimeException;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
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

$GLOBALS['startRunTime'] = time();

//--------------------------------------------------------------
// Get DB connection param from config file.

$configFile = $GLOBALS ['PACKAGE_ROOT'] . '/db.local.php';

if (!file_exists($configFile)) {
    throw new RuntimeException ("Missing configuration file: $configFile. Copy $configFile.example and fill in credentials.");
}

$dbConfig = require $configFile;
$username = $dbConfig['db_user'];

//--------------------------------------------------------------

$DBType = 'SQLite';
// $DBType = 'MySQL';

/**
 * @var \PDO|null $sharedPDO
 */
$sharedPDO = null;

switch ($DBType)
{
    case 'MySQL':
        $sharedPDO = setupMySQL($dbConfig);
        break;

    case 'SQLite':
        $sharedPDO = setupSQLite($dbConfig['db_user']);
        break;

    default:
        echo "Unsupported DB type.\r\n";
        exit(1);
}

//--------------------------------------------------------------
// Create the persistor (Root object)
//--------------------------------------------------------------

/** @var \ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor $persistor */
global $persistor;

//TODO Maybe throw exception if failed to created DB_Persistor and catch it here.
$persistor = new DB_Persistor($sharedPDO, $username);

// if ($persistor === null)
// {
//     echo "FAILED to create DB_Persistor.\r\n";
//     exit(1);
// }

//--------------------------------------------------------------
// Seed factories
//--------------------------------------------------------------

FactoryBase::setRunType('Normal');

global $dateFactory, $locationFactory;
$dateFactory     = new FactoryDates();
$locationFactory = new FactoryLocations();

global $commentMapFactory,  $dateMapFactory,     $eventMapFactory,  $locationMapFactory,
$nameMapFactory,     $phoneMapFactory,    $tagMapFactory,    $sizeMapFactory,
$docMapFactory,      $photoMapFactory,    $albumMapFactory,  $profileMapFactory,
$projectMapFactory,  $relationFactory,    $revisionMapFactory, $videoMapFactory,
$surnameMapFactory,  $unionMapFactory,    $userMapFactory;

$commentMapFactory  = new FactoryMapperComments();
$dateMapFactory     = new FactoryMapperDates();
$eventMapFactory    = new FactoryMapperEvents();
$locationMapFactory = new FactoryMapperLocations();
$nameMapFactory     = new FactoryMapperNames();
$phoneMapFactory    = new FactoryMapperPhoneNumbers();
$tagMapFactory      = new FactoryMapperTags();
$sizeMapFactory     = new FactoryMapperSizeMaps();

$docMapFactory      = new FactoryMapperDocuments();
$photoMapFactory    = new FactoryMapperPhotos();
$albumMapFactory    = new FactoryMapperPhotoAlbums();
$profileMapFactory  = new FactoryMapperProfiles();
$projectMapFactory  = new FactoryMapperProjects();
$relationFactory    = new FactoryMapperRelations();
$revisionMapFactory = new FactoryMapperRevisions();
$videoMapFactory    = new FactoryMapperVideos();
$surnameMapFactory  = new FactoryMapperSurnames();
$unionMapFactory    = new FactoryMapperUnions();
$userMapFactory     = new FactoryMapperUsers();

FactoryMapperDocuments::setup();

//--------------------------------------------------------------
// Global set-up ENDS here. From here onwards phpunit is running the tests.
//--------------------------------------------------------------


//--------------------------------------------------------------
// Database Helper Functions
//--------------------------------------------------------------

/**
 * Connect to MySQL DB.
 * @param array<string> $dbConfigParam
 * @return PDO
 */
function setupMySQL(array $dbConfigParam): PDO
{
    $dbUser = $dbConfigParam['db_user'];
    $dbPass = $dbConfigParam['db_pass'];
    $dbHost = $dbConfigParam['db_host'];
    $dbName = $dbConfigParam['db_name'];

    return new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
}

function setupSQLite(string $username): PDO
{
    $sqlitePDO = new PDO('sqlite::memory:');
    $sqlitePDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    initializeSQLiteSchema($sqlitePDO);

    $sql = "INSERT INTO geni_clients (profile_id, username, guid, name) VALUES ('test-id-1', '$username', 'test-guid-xyz', 'Test Client')";
    $sqlitePDO->exec($sql);

    return $sqlitePDO;
}

function initializeSQLiteSchema(PDO $pdo): void
{
    // Updated path to project root resources/sql/
    $schemaPath = $GLOBALS ['PACKAGE_ROOT'] . '/resources/sql/sqliteschema.sql';

    if (!file_exists($schemaPath))
    {
        throw new \RuntimeException("Schema file not found at path: {$schemaPath}");
    }

    $schema = file_get_contents($schemaPath);
    $pdo->exec($schema);
}

//--------------------------------------------------------------

register_shutdown_function(function () {
    $totalRunTime = time() - $GLOBALS['startRunTime'];
    echo "Total run time: {$totalRunTime} seconds (" . ($totalRunTime / 60) . " minutes)\r\n";
});
