<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Page</title>
</head>
<?php
/**
 * The purpose of this script is to authenticate with the Geni's Server. It then
 * saves the authentication parameters in a file, that can be used by the CLI
 * test application. A bit awkward, but it works.
 *
 * There is NO need to change this script. The connection parameters are set in
 * geni.local.php in the project root directory.
 */

$GLOBALS ['PACKAGE_ROOT'] = dirname(__DIR__);
require_once  $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';
$configFile = $GLOBALS ['PACKAGE_ROOT'] . '/geni.local.php';

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Core\API_WrapperServer;

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

session_start();

// Get connection to Server. Log output set to 'silent'.
// Initialize the client. The wrapper self-registers for access without global var..

$API_Wrap = new API_WrapperServer(
    $appNumber,
    $appID,
    $appSecret,
    $appRedirectURL,
    silent: true,
    useSandbox: false,
);

/**
 * 2. Check for existing session
 */

if (!$API_Wrap->retrieve($storeFile))
{
    // No seesion file. Need to authenticate.

    if (empty($_REQUEST["code"]))
    {
        /**
         * 3. Create URL for auth request. Display page with request.
         *    Redirect back to this page.
         */
        $geniOauthURL = $API_Wrap->makeAuthorizationUrl();

        echo("<script>top.location.href='" . $geniOauthURL . "'</script>");
    }
    else
    {
        /**
         * 4. This page has been redirected to. Code received.
         */
        $accessCode = $_REQUEST["code"];

        /**
         * 5. Request auth parameters.
         */
        $API_Wrap->requestAccessToken($accessCode);

        /**
         * 6. Optionally store session auth parameters to file, so retrieve() works next time,
         *    and auth step is skipped.
         */
        $API_Wrap->store($storeFile);
    }

    $msg = 'Session started - ' . date(DATE_RFC2822);
}
else
{
    // Found an auth file from previous authentication.

    $operation = 'restored';

    if ($API_Wrap->checkRefresh())
    {
        // Check if towards end of session, and refesh it if needed.

        $operation = 'refreshed';
    }
}

/**
 * ----------------------------------------
 * 7. Auth process complete. Make API calls...
 *    or load your script.
 */
$profile = API_Profile::getProfileSelf(true);
$name    = $profile->getName();
$seconds = (int) $API_Wrap->getExpiresIn();

$msg = "Session $operation - " . date(DATE_RFC2822)
       . ".<br>Expires in $seconds seconds. (" . round(($seconds / 3600), 1) . " hours).";

?>
<body>
    <br><br>
    <h2><a href='index.php'>Back to <b>index.php</b></a></h2>
    <br><br><br><br>
    <h2>Hello <?= $name ?>.<br><br>
    <?= $msg ?></h2>
</body>
</html>
