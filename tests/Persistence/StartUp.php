<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

$GLOBALS ['PACKAGE_ROOT'] = dirname(__DIR__, 2);

require_once $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';

use PDO;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;

$GLOBALS['startRunTime'] = time();

// The username and passwords should be input paramters to the script,
// or at least hardcoded in some start-up code.

$username = '';
$password = '';

// $DBType = 'SQLite';
$DBType = 'MySQL';

/**
 * @var \PDO|NULL $sharedPDO
 */
$sharedPDO = null;

switch ($DBType)
{
    case 'MySQL':
        // The username and password should be input parameters to the script,
        // or at least hardcoded in some start-up code.

        $sharedPDO = setupMySQL($username, $password);
        break;

    case 'SQLite':
        $sharedPDO = setupSQLite($username);
        break;

    default:
        echo "Unsupported DB type.\r\n";
        die();
}


//--------------------------------------------------------------

// Create the persistor, which is the ROOT object.

/**
 * @var \ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor $persistor
 */

$persistor = new DB_Persistor($sharedPDO, $username);

if ($persistor == null)
{
    echo "FAILED to create DB_Persistor.\r\n";
    die();
}

//--------------------------------------------------------------
// End of general set-up. Your start-up code goes here.
//--------------------------------------------------------------

/**
function setupMySQL (string $username, string $password): PDO
{
    return new PDO ('mysql:host=localhost;dbname=geni_db;charset=utf8mb4', $username, $password);
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

/**
 * This code runs after ALL tests and any coverage generation
 */
register_shutdown_function(function () {
    $totalRunTime = time() - $GLOBALS['startRunTime'];
    echo "Total run time: $totalRunTime seconds (" . $totalRunTime / 60 . " minutes)\r\n";
});
