<?php
namespace ShmuelAKam\Greenjeans\Api\Auth;

/**
 * testClient.php - Example script for starting CLI session with Client-flow authentation.
 *
 * There is NO need to change this script. The connection parameters are set in
 * geni.local.php in the project root directory.
 */

$GLOBALS ['PACKAGE_ROOT'] = dirname(__DIR__, 2);
require_once  $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';
$configFile = $GLOBALS ['PACKAGE_ROOT'] . '/geni.local.php';

use Exception;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Core\API_WrapperClient;

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


// Get connection to Server. Log output set to 'silent'.
// Initialize the client. The wrapper self-registers for access without global var..

$API_Wrap = new API_WrapperClient(
    $appNumber,
    $appID,
    $appSecret,
    silent: true
);

/**
 * 2. Check for existing session
 */

if (!$API_Wrap->retrieve($storeFile))
{
    // No seesion file. Need to authenticate.

    /**
     * 3: Generate the URL for the human to visit
     */
    $authUrl = $API_Wrap->makeAuthorizationUrl();

    echo "1. Please open this URL in your browser to authorize the app:\n";
    echo $authUrl . "\n\n";

    /**
     * 4: Manual Input
     * After the user clicks 'Authorize', Geni redirects them to your "Redirect URI".
     * Since it's a CLI app, the user will see a 'Page Not Found' or a blank page,
     * but the CODE will be in the address bar.
     */

    echo "2. After authorizing, you will be redirected to a URL.\n";
    echo "   Copy the 'code' parameter from that URL and paste it here: ";

    $handle     = fopen("php://stdin", "r");
    $accessCode = trim(fgets($handle));

    /**
     * 5: Exchange the code for the token
     */

    try
    {
        $API_Wrap->requestAccessToken($accessCode);

        /**
         * 6. Optionally store session auth parameters to file, so retrieve() works next time.
         */
        $API_Wrap->store($storeFile);

        echo "\nSuccess! Access Token acquired and saved to $storeFile.\n";

    }
    catch (Exception $e)
    {
        die("\nError: " . $e->getMessage() . "\n");
    }
}
else
{
    // Found an auth file from previous authentication.

    $operation = 'restored';

    if ($API_Wrap->checkRefresh())
    {
        // Check if towards end of session, and refesh it if needed.

    echo "Session restored from $storeFile\n";
    }
}

/**
 * ----------------------------------------
 * 7. Auth process complete. Make API calls...
 *    or load your script.
 */

echo "Fetching your Geni profile...\n";

$profile = API_Profile::getProfileSelf(true);

echo "Connected as: " . $profile->getName() . " (ID: " . $profile->getID() . ")\n";

echo "\nTotal API calls made: " . $API_Wrap::getNumCalls() . "\n";
