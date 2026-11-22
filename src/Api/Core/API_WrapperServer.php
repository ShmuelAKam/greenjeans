<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

/**
 * Class to handle Server-flow access to Geni Server.
 * This class handles ONLY the authentication, while all of the actual Server
 * calls are handled by the base class.
 *
 * See example script Api/Auth/testServer.php for initizaling a CLI script.
 * @see API_Wrapper
 */
class API_WrapperServer extends API_Wrapper
{
    /**
     * URL that is sent TO the Server. Geni returns the Auth code to this URL.
     * This value was also set, by the user, when the application was registered.
     * @var string
     */
    protected string $AppRedirectURL;

    //======================================================

    /**
     * Constructor. Sets object values, but does NOT authenticate.
     *
     * The first three parameters are the hard-coded application parameters
     * given by Geni when registering an application.
     * @param int    $number     Application number. Set by Geni.
     * @param string $ID         ID. Hash value.     Set by Geni.
     * @param string $secret     Authentication Secret. Set by Geni.
     * @param string $redirectURL URL that is sent TO the Server, to send reply to.
     *                           See comment for $AppRedirectURL above.
     * @param bool   $silent     See comment for API_Wrapper::silent.
     * @param bool   $hideHTTP   See comment for API_Wrapper::hideHTTP.
     * @param bool   $useSandbox See comment for API_Wrapper::onSandbox.
     */
    public function __construct(
        int    $number,
        string $ID,
        string $secret,
        string $redirectURL,
        bool   $silent = false,
        bool   $hideHTTP = false,
        bool   $useSandbox = false,
    ) {
        parent::__construct($number, $ID, $secret, $silent, $hideHTTP, $useSandbox);

        $this->AppRedirectURL = $redirectURL;
    }

    /**
     * Create URL for auth request.
     * @return string URL.
     */
    public function makeAuthorizationUrl(): string
    {
        $base = $this->isOnSandbox() ? "https://sandbox.geni.com" : "https://www.geni.com";

        return "$base/platform/oauth/authorize"
            . '?client_id=' . $this->getAppID()
            . '&redirect_uri=' . urlencode($this->getAppRedirectURL());
    }

    /**
     * {@inheritDoc}
     */
    public function requestAccessToken(string $accessCode = null): void
    {
        $this->setAccessCode($accessCode);

        $tokenRequestURL = '/oauth/request_token'
            . '?client_id=' . $this->getAppID()
            . '&client_secret=' . $this->getAppSecret()
            . '&redirect_uri=' . urlencode($this->getAppRedirectURL())
            . '&code=' . $this->getAccessCode();

        $authTokens = $this->callAPI_Auth($tokenRequestURL);

        if (array_key_exists('status', $authTokens)
            && strcmp($authTokens ['status'], 'unauthorized') == 0)
        {
            echo "FAILED to authenticate!\r\n\r\n";
            exit(-1);
        }

        if (isset($authTokens['access_token']))
        {
            $this->AuthTokenValues = $authTokens;
            $this->AccessToken     = $authTokens ['access_token'];
        }
        else
        {
            echo "Server Authentication Failed!\r\n\r\n";
            exit(-1);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refreshToken(): void
    {
        // Build the request URL
        $refreshRequestURL = 'platform/oauth/request_token'
            . '?client_id=' . $this->getAppID()
            . '&client_secret=' . $this->getAppSecret()
            . '&redirect_uri=' . urlencode($this->getAppRedirectURL())
            . '&refresh_token=' . $this->getRefreshToken()
            . '&grant_type=refresh_token';

        // Execute the internal authentication call
        $this->AuthTokenValues = $this->callAPI_Auth($refreshRequestURL);
        $this->AccessToken     = $this->AuthTokenValues ['access_token'];
    }

    /**
     * Getter for URL of the page to redirect back to.
     * @return string
     */
    private function getAppRedirectURL()
    {
        return $this->AppRedirectURL;
    }
}
