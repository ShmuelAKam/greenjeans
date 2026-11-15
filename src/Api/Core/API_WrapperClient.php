<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

/**
 * Class to handle Client-flow access to Geni Server.
 * This class handles ONLY the authentication, while all of the actual Server
 * calls are handled by the base class.
 *
 * See example script testClient.php after class, for initizaling a CLI script.
 * @see API_Wrapper
 */
class API_WrapperClient extends API_Wrapper
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
        int     $number,
        string  $ID,
        string  $secret,
        bool    $silent = true,
        bool    $hideHTTP = true,
        bool    $useSandbox = false,
    ) {
        parent::__construct($number, $ID, $secret, $silent, $hideHTTP, $useSandbox);
    }

    /**
     * Create URL for auth request.
     * @return string URL.
     */
    public function makeAuthorizationUrl(): string
    {
        $base = $this->isOnSandbox() ? "https://sandbox.geni.com" : "https://www.geni.com";

        return $base . '/platform/oauth/authorize'
            . '?client_id=' . $this->getAppID()
            . '&response_type=code';
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
            echo "Client Authentication Failed!\r\n\r\n";
            exit(-1);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refreshToken(): void
    {
        // 1. Check if we even have a refresh token stored
        $refreshToken = $this->AuthTokenValues['refresh_token'] ?? null;

        if (!$refreshToken)
        {
            // In a CLI app, this is where you'd usually trigger the browser login flow
            throw new \Exception("No refresh token found in AuthTokenValues. Manual re-auth required.");
        }

        // 2. Build the request URL
        $refreshRequestURL = '/oauth/request_token'
            . '?client_id=' . $this->getAppID()
            . '&client_secret=' . $this->getAppSecret()
            . '&refresh_token=' . $refreshToken
            . '&grant_type=refresh_token';

        // 3. Execute the internal authentication call
        $authTokens = $this->callAPI_Auth($refreshRequestURL);

        // 4. Update state
        if (isset($authTokens['access_token']))
        {
            $this->AuthTokenValues = $authTokens;
            $this->AccessToken     = $authTokens['access_token'];
        }
        else
        {
            throw new \Exception("Geni API rejected the refresh token.");
        }
    }
}
