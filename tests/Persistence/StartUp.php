<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

$GLOBALS ['PACKAGE_ROOT']  = dirname(__DIR__, 2);
$GLOBALS ['TEST_API_ROOT'] = realpath($GLOBALS ['PACKAGE_ROOT'] . '/tests/Api');

require_once $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';

use PDO;
use RuntimeException;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;

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
// Create the persistor, which is the ROOT object.
//--------------------------------------------------------------

/** @var \ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor $persistor */
global $persistor;

$persistor = new DB_Persistor($sharedPDO, $username);

if ($persistor == null)
{
    echo "FAILED to create DB_Persistor.\r\n";
    die();
}

//--------------------------------------------------------------
// Global set-up ENDS here. Your start-up code goes here.
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

/**
 * Full set up of the SQLite database and the PDO.
 * @return PDO
 */
function setupSQLite(string $username): PDO
{
    // 1. Initialize shared SQLite in-memory DB connection for the test suite run

    $sqlitePDO = new PDO('sqlite::memory:');
    $sqlitePDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create DB.

    initializeSQLiteSchema($sqlitePDO);

    // 3. Insert the client. client_id auto-generates, but id and guid are required.
    $sql = "INSERT INTO geni_clients (profile_id, username, guid, name) VALUES ('test-id-1', '$username', 'test-guid-xyz', 'Test Client')";
    $sqlitePDO->exec($sql);

    return $sqlitePDO;
}

/**
 * Loads and executes the consolidated SQLite schema file.
 */
function initializeSQLiteSchema(PDO $pdo): void
{
    $schemaPath = $GLOBALS ['PACKAGE_ROOT'] . '/resources/sql/sqliteschema.sql';

    if (!file_exists($schemaPath))
    {
        throw new \RuntimeException("Schema file not found at path: {$schemaPath}");
    }

    $schema = file_get_contents($schemaPath);
    $pdo->exec($schema);
}

//--------------------------------------------------------------
/**
 * This code runs after ALL tests and any coverage generation
 */
register_shutdown_function(function () {
    $totalRunTime = time() - $GLOBALS['startRunTime'];
    echo "Total run time: {$totalRunTime} seconds (" . ($totalRunTime / 60) . " minutes)\r\n";
});
