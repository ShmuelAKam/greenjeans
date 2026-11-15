<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

/**
 * Class to handle Desktop-flow access to Geni Server.
 * This class handles ONLY the authentication, while all of the actual Server
 * calls are handled by the base class.
 *
 * See example script testDesktop.php after class, for initizaling a CLI script.
 * @see API_Wrapper
 */
class API_WrapperDesktop extends API_Wrapper
{
    /**
     * Constructor. Sets object values, but does NOT authenticate.
     *
     * The first three parameters are the hard-coded application parameters
     * given by Geni when registering an application.
     * @param int    $number     Application number. Set by Geni.
     * @param string $ID         ID. Hash value.     Set by Geni.
     * @param string $secret     Authentication Secret. Set by Geni.
     *
     * @param bool   $silent     See comment for API_Wrapper::silent.
     * @param bool   $hideHTTP   See comment for API_Wrapper::hideHTTP.
     * @param bool   $useSandbox See comment for API_Wrapper::onSandbox.
     */
    public function __construct(
        int    $number,
        string $ID,
        string $secret,
        bool   $silent = false,
        bool   $hideHTTP = false,
        bool   $useSandbox = false,
    ) {
        parent::__construct($number, $ID, $secret, $silent, $hideHTTP, $useSandbox);
    }

    /**
     * Desktop-specific: Automate the browser opening.
     */
    public function launchBrowser(): void
    {
        $base = $this->isOnSandbox() ? "https://sandbox.geni.com" : "https://www.geni.com";

        $authUrl = $base . '/platform/oauth/authorize'
            . '?client_id=' . $this->getAppID()
                   . '&response_type=code';

        if (PHP_OS_FAMILY === 'Windows')
        {
            exec("start \"\" \"$authUrl\"");
        }
        elseif (PHP_OS_FAMILY === 'Darwin')
        {
            exec("open \"$authUrl\"");
        }
        elseif (PHP_OS_FAMILY === 'Linux')
        {
            exec("xdg-open \"$authUrl\" > /dev/NULL 2>&1");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function requestAccessToken(string $accessCode = null): void
    {
        if ($accessCode != null)
        {
            $this->setAccessCode($accessCode);
        }

        $tokenRequestURL = '/oauth/request_token'
            . '?client_id=' . $this->getAppID()
            . '&client_secret=' . $this->getAppSecret()
            . '&code=' . $this->getAccessCode();

        $authTokens = $this->callAPI_Auth($tokenRequestURL);

        if (isset($authTokens['access_token']))
        {
            $this->AuthTokenValues = $authTokens;
            $this->AccessToken     = $authTokens ['access_token'];
        }
        else
        {
            echo "Desktop Authentication Failed!\r\n\r\n";
            exit(-1);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refreshToken(): void
    {
        // Build the request URL without redirect_uri
        $refreshRequestURL = 'platform/oauth/request_token'
            . '?client_id=' . $this->getAppID()
            . '&client_secret=' . $this->getAppSecret()
            . '&refresh_token=' . $this->getRefreshToken()
            . '&grant_type=refresh_token';

        // Execute the internal authentication call
        $this->AuthTokenValues = $this->callAPI_Auth($refreshRequestURL);
        $this->AccessToken     = $this->AuthTokenValues['access_token'];
    }
}
