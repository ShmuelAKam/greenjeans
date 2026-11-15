<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

$GLOBALS ['PACKAGE_ROOT']       = dirname(__DIR__, 2);
$GLOBALS ['TEST_API_ROOT']      = __DIR__;
$GLOBALS ['TEST_FILES_ROOT']    = $GLOBALS ['TEST_API_ROOT'] . '/files/';
$GLOBALS ['TEST_FIXTURES_ROOT'] = $GLOBALS ['TEST_API_ROOT'] . '/fixtures/';

require_once $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';

use ShmuelAKam\Greenjeans\Api\Core\API_WrapperServer;
use ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperMocker;
use ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperRecorder;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryEvents;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryLocations;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotos;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProfiles;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryUnions;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryVideos;
use RuntimeException;

$GLOBALS ['startRunTime'] = time();

//--------------------------------------------------------------
// Get Server connection parameters from config file.

$configFile = $GLOBALS ['PACKAGE_ROOT'] . '/geni.local.php';

if (!file_exists($configFile)) {
    throw new RuntimeException ("Missing configuration file: $configFile. Copy $configFile.example and fill in credentials.");
}

$serverConfig = require $configFile;

//--------------------------------------------------------------


/**
 * There are three set-up options here:
 *
 * 1.  Normal operation - Only the API_WrapperServer is needed. Created by setup.
 *                        This global object wraps the server connection, and is
 *                        used throughtout the entire application. The object is
 *                        accessed by calling API_Wrapper::getInstance()
 *
 * 2.  Recording        - BOTH the API_WrapperServer and the API_WrapperRecorder
 *                        are needed. The Recorder takes the regular wrapper as
 *                        a parameter. In this way all calls to the Server must
 *                        first pass through the recorder.
 *
 * 3.  Mocking          - ONLY the API_WrapperMocker is needed. It doesn't talk
 *                        with the Geni Server at all.
 *
 * In all cases the relevant constructors register the wrapper for the getInstance,
 * so these local variables can be ignored.
 */

$runType = 'Normal';
// $runType = 'Recording';
// $runType = 'Mocking';

$GLOBALS ['shreddingON'] = true;
$GLOBALS ['runType']     = $runType;

switch ($runType)
{
    case 'Normal':
        /** @var \ShmuelAKam\Greenjeans\Api\Core\API_WrapperServer $regular See comment above. */
        $regular = serverSetup($serverConfig);
        break;

    case 'Recording':
        $regular = serverSetup();
        /** @var \ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperRecorder $recorder See comment above. */
        $recorder = new API_WrapperRecorder($regular);
        break;

    case 'Mocking':
        verifyMockers();
        /** @var \ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperMocker   $mocker   See comment above. */
        $mocker                  = new API_WrapperMocker(silent: false);
        $GLOBALS ['shreddingON'] = false;
        API_BaseTest::setMockingRun();
        break;

    default:
        echo "Invalid run type: $runType. Exiting.";
        die();
}

FactoryBase::setRunType($runType);
API_BaseTest::setRunningProfileID();

/**
 * These are global factory objects to create test objects used by the
 * various test classes.
 *
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates     $dateFactory
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments $documentFactory
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryEvents    $eventFactory
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryLocations $locationFactory
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotos    $photoFactory
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProfiles  $profileFactory
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryUnions    $unionFactory
 * @var \ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryVideos    $videoFactory
 */

$dateFactory     = new FactoryDates();
$documentFactory = new FactoryDocuments();
$eventFactory    = new FactoryEvents();
$locationFactory = new FactoryLocations();
$photoFactory    = new FactoryPhotos();
$profileFactory  = new FactoryProfiles();
$unionFactory    = new FactoryUnions();
$videoFactory    = new FactoryVideos();

// Force these variables into the $GLOBALS array explicitly at the end of bootstrap.

$GLOBALS ['dateFactory']     = $dateFactory;
$GLOBALS ['documentFactory'] = $documentFactory;
$GLOBALS ['eventFactory'   ] = $eventFactory;
$GLOBALS ['locationFactory'] = $locationFactory;
$GLOBALS ['photoFactory'   ] = $photoFactory;
$GLOBALS ['profileFactory' ] = $profileFactory;
$GLOBALS ['unionFactory'   ] = $unionFactory;
$GLOBALS ['videoFactory'   ] = $videoFactory;

// Initialize some static variables that require some run-time operations.

TestProfiles::setup();
TestData::setup();

//--------------------------------------------------------------
// Global set-up ENDS here. From here onwards phpunit is running the tests.
//--------------------------------------------------------------

/**
 * Perform set-up for MY application.
 *
 * @param bool $silent
 * @param bool $hideHTTP
 * @return API_WrapperServer
 */
function serverSetup(array $serverConfig, bool $silent = false, bool $hideHTTP = true): API_WrapperServer
{
    $appNumber      = $serverConfig['app_num'];
    $appID          = $serverConfig['app_id'];
    $appSecret      = $serverConfig['app_secret'];
    $appRedirectURL = $serverConfig['app_redirect'];
    $storeFile      = $GLOBALS ['PACKAGE_ROOT'] . '/SessionAuth.json';

    $API_Server = new API_WrapperServer(
        $appNumber,
        $appID,
        $appSecret,
        $appRedirectURL,
        silent: $silent,
        hideHTTP: $hideHTTP,
    );

    $haveAuth = $API_Server->retrieve($storeFile);

    if (!$haveAuth)
    {
        echo "Auth failed. Exiting.\n";
        exit(-1);
    }

    return $API_Server;
}

function verifyMockers(): void
{
    if (!is_dir($GLOBALS ['TEST_FIXTURES_ROOT']))
    {
        // Directory tree of fixtures does NOT existt.
        // There's nothing to mock with. Exit.

        echo "Could not find mocker files.\r\n";
        echo "The directory tree should be in:\r\n";
        echo $GLOBALS ['TEST_FIXTURES_ROOT'] . "\r\n";
        die();
    }
}

/**
 * This code runs after ALL tests and any coverage generation
 */
register_shutdown_function(function () {
    global $documentFactory, $photoFactory, $profileFactory, $videoFactory;

    // Delete all of the objects created on the server.
    // DO NOT run in mocking mode, or it will NOT delete
    // the server, but it will delete the LISTS of objects to delete.

    if ($GLOBALS ['runType'] != 'Mocking' && $GLOBALS ['shreddingON'])
    {
        $documentFactory->shredAll(silent: true);
        $photoFactory->shredAll(silent: true);
        $profileFactory->shredAll(silent: true);
        $videoFactory->shredAll(silent: true);
    }

    $totalRunTime = time() - $GLOBALS ['startRunTime'];
    echo "Total run time: $totalRunTime seconds (" . $totalRunTime / 60 . " minutes)\r\n";
});
