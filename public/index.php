<?php if (PHP_SAPI !== 'cli'): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Page</title>
</head>
<?php endif; ?>
<?php
/**
 * The purpose of this script is to authenticate with the Geni's Server. It then
 * saves the authentication parameters in a file, for retaining the parameters
 * for future use (the session lasts for 24 hours, but can be refreshed before
 * then, to extend it again).
 *
 * This script works as a standalone script to write the auth file. OR alternately
 * it can be CALLED by a bootstrap file. In that case this script will return the
 * API_WrapperServer object that it creasted.
 *
 * There is NO need to change this script. The connection parameters are set in
 * config.geni.local.php in the project root directory.
 */

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Core\API_WrapperServer;

$GLOBALS ['PACKAGE_ROOT'] = dirname(__DIR__);
require_once  $GLOBALS ['PACKAGE_ROOT'] . '/vendor/autoload.php';
$configFile = $GLOBALS ['PACKAGE_ROOT'] . '/config.geni.local.php';

/**
 * 1. Get Server connection credentials.
 */

if (!file_exists($configFile))
{
    if (PHP_SAPI !== 'cli')
    {
        echo "<br>Missing configuration file: $configFile. Copy $configFile.example and fill in credentials.<br>";
    }

    throw new \RuntimeException ("Missing configuration file: $configFile. Copy $configFile.example and fill in credentials.");
}

$serverConfig = require $configFile;

$storeFile      = $GLOBALS ['PACKAGE_ROOT'] . '/SessionAuth.json';

$appNumber      = (int) $serverConfig['app_num'];
$appID          = $serverConfig['app_id'];
$appSecret      = $serverConfig['app_secret'];
$appRedirectURL = $serverConfig['app_redirect'];

if (PHP_SAPI !== 'cli')
{
    session_start();
}

// Get connection to Server. Log output set to 'silent'.
// Initialize the client. The wrapper self-registers for access without global var.

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
    // No session file. Need to authenticate.

    if (PHP_SAPI === 'cli')
    {
        // CLI mode: print the URL. The user then must open the link and authorize
        // the app. In the meantime the script goes into a wait loop, until the
        // Apache receives the redirect and writes the $storeFile file is written.

        $geniOauthURL = $API_Wrap->makeAuthorizationUrl();
        echo "\nOpen this URL in your browser to authorize:\n$geniOauthURL\n\nWaiting for callback";

        while (!$API_Wrap->retrieve($storeFile)) {
            echo '.';
            sleep(1);
        }
        echo "\nAuthorized!\n";
    }
    elseif (empty($_REQUEST["code"]))
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

    $operation = 'started';
    $msg = 'Session started - ' . date(DATE_RFC2822);
}
else
{
    // Found an auth file from previous authentication.

    $operation = 'restored';

    if ($API_Wrap->checkRefresh())
    {
        // Check if towards end of session, and refesh it if needed.
        $API_Wrap->store($storeFile);
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

if (PHP_SAPI === 'cli')
{
    echo "Hello $name. Session $operation - " . date(DATE_RFC2822)
       . ". Expires in $seconds seconds (" . round(($seconds / 3600), 1) . " hours).\n";

    // Entire process complete. Return server-wrapper to calling script.
    return $API_Wrap;
}

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