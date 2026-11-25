<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

$GLOBALS ['PACKAGE_ROOT']       = dirname(__DIR__, 2);
$GLOBALS ['TEST_API_ROOT']      = __DIR__;
$GLOBALS ['TEST_FILES_ROOT']    = $GLOBALS ['TEST_API_ROOT'] . '/files/';
$GLOBALS ['TEST_FIXTURES_ROOT'] = $GLOBALS ['TEST_API_ROOT'] . '/fixtures/';

require_once $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';

use RuntimeException;
use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Api\Core\API_WrapperServer;
use ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperMocker;
use ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperRecorder;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

$GLOBALS ['startRunTime'] = time();

serverTestsSetup();

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

$runType      = strtolower(getenv('RUN_TYPE') ?: 'Normal');
$silentString = getenv('SILENT') ?: 'SILENT';
$silent       = ($silentString === 'SILENT');

$GLOBALS ['logSilent']   = $silent;
$GLOBALS ['shreddingON'] = true;
$GLOBALS ['runType']     = $runType;

switch ($runType)
{
    case 'normal':
        /** @var \ShmuelAKam\Greenjeans\Api\Core\API_WrapperServer $regular
         * See comment above.
         */
        $regular = serverSetup();
        break;

    case 'recording':
        $regular = serverSetup();
        /** @var \ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperRecorder $recorder
         * See comment above.
         */
        $recorder = new API_WrapperRecorder($regular);
        break;

    case 'mocking':
        verifyMockers();
        /** @var \ShmuelAKam\Greenjeans\Tests\Api\Core\API_WrapperMocker   $mocker
         * See comment above.
         */
        $mocker                  = new API_WrapperMocker(silent: false);
        $GLOBALS ['shreddingON'] = false;
        API_BaseTest::setMockingRun();
        break;

    default:
        echo "Invalid run type: $runType.\r\n";
        echo "Use Normal, Recording or Mocking.\r\n";
        echo "Exiting.\r\n";
        die();
}

FactoryBase::setRunType($runType);
API_BaseTest::setRunningProfileID();

// Initialize some static variables that require some run-time operations.

TestProfiles::setup();
TestData::setup();

//--------------------------------------------------------------
// Global set-up ENDS here. From here onwards phpunit is running the tests.
//--------------------------------------------------------------

//--------------------------------------------------------------
// Helper functions.

function serverTestsSetup(): void
{
    //--------------------------------------------------------------
    // Get Test Server parameters from config file.

    $configFile = $GLOBALS ['PACKAGE_ROOT'] . '/config.test.server.php';

    if (file_exists($configFile))
    {
        $testConfig = require $configFile;

        if (isset($testConfig ['test_server'])
            && $testConfig ['test_server'] != 'http://your_server_url/')
        {

            $GLOBALS ['API_TEST_SERVER'] = $testConfig['test_server'];
        }
        else
        {
            throw new RuntimeException("Configuration file not set: $configFile. Fill it in.");
        }
    }
    else
    {
        throw new RuntimeException("Missing configuration file: $configFile. Fill it in.");
    }
}

/**
 * @param bool $silent
 * @param bool $hideHTTP
 * @return API_WrapperServer
 */
function serverSetup(bool $silent = false, bool $hideHTTP = true): API_WrapperServer
{
    // Call the authentication script.
    // It returns a configured API_WrapperServer object.

    return require $GLOBALS['PACKAGE_ROOT'] . '/public/index.php';
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
    $documentFactory = FactoryBase::getFactory(API_Document::class);
    $photoFactory    = FactoryBase::getFactory(API_Photo::class);
    $profileFactory  = FactoryBase::getFactory(API_Profile::class);
    $videoFactory    = FactoryBase::getFactory(API_Video::class);

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
