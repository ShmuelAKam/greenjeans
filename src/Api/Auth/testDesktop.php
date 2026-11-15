<?php
namespace ShmuelAKam\Greenjeans\Api\Auth;

/**
 * testDesktop.php - Example script for starting CLI session with Desktop-flow authentication.
 *
 * Uses the OS-native 'open' command to automate the browser step.
 *
 * There is NO need to change this script. The connection parameters are set in
 * geni.local.php in the project root directory.
 */

$GLOBALS ['PACKAGE_ROOT'] = dirname(__DIR__, 2);
require_once  $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';
$configFile = $GLOBALS ['PACKAGE_ROOT'] . '/geni.local.php';

use Exception;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Core\API_WrapperDesktop;

/**
 * 1. Get Server connection credentials.
 */


if (!file_exists($configFile))
{
    echo "<br>Missing configuration file: $configFile. Copy $configFile.example and fill in credentials.<br>";

    throw new \RuntimeException ("Missing configuration file: $configFile. Copy $configFile.example and fill in credentials.");
}

$serverConfig = require $configFile;

$storeFile      = $GLOBALS ['PACKAGE_ROOT'] . '/SessionAuth.json';

$appNumber      = (int) $serverConfig['app_num'];
$appID          = $serverConfig['app_id'];
$appSecret      = $serverConfig['app_secret'];
$appRedirectURL = $serverConfig['app_redirect'];

// Initialize the Desktop client.

// Get connection to Server. Log output set to 'silent'.
// Initialize the client. The wrapper self-registers for access without global var..

$API_Wrap = new API_WrapperDesktop(
    $appNumber,
    $appID,
    $appSecret,
    silent: true
    useSandbox: false,
);

/**
 * 2. Check for existing session
 */

if (!$API_Wrap->retrieve($storeFile))
{
    echo "--- New Desktop Authentication Session ---\n";

    /**
     * 3: Automate the browser opening
     * Instead of echoing a URL, this triggers 'start', 'open', or 'xdg-open'
     */
    echo "Opening your default browser for Geni authorization...\n";
    $API_Wrap->launchBrowser();

    /**
     * 4: Manual Input
     * Even with Desktop flow, you must capture the code from the redirect URL
     * because the CLI script has no webserver listening for the callback.
     */
    echo "\nAfter you click 'Authorize' in the browser:";
    echo "\n1. Look at the address bar of the page that loads.";
    echo "\n2. Copy the 'code' parameter (e.g., everything after code=).";
    echo "\n3. Paste it here: ";

    $handle     = fopen("php://stdin", "r");
    $accessCode = trim(fgets($handle));

    /**
         * 5. Request auth parameters.
     */
    try
    {
        if (empty($accessCode))
        {
            throw new Exception("No access code entered.");
        }

        $API_Wrap->requestAccessToken($accessCode);

        // Save the session to skip this step next time.
        $API_Wrap->store($storeFile);

        echo "\n✔ Success! Access Token acquired and saved to $storeFile.\n";

    }
    catch (Exception $e)
    {
        die("\nCritical Error: " . $e->getMessage() . "\n");
    }
}
else
{
    echo "✔ Session restored from $storeFile (No browser login needed).\n";
}

/**
 * 6: Perform a test API call
 */
echo "Testing connection to Geni API...\n";

// The static handler in API_Wrapper will manage any 429 Throttle errors here.
$profile = API_Profile::getProfileSelf(true);

if (isset($profile['name']))
{
    echo "--------------------------------------------------\n";
    echo "Connected successfully as: " . $profile->getName() . "\n";
    echo "--------------------------------------------------\n";
}
else
{
    echo "API Call failed. Raw response:\n";
    print_r($profile);
}

echo "Total calls made: " . $API_Wrap::getNumCalls() . "\n";
