<?php

namespace ShmuelAKam\Greenjeans\Api\Core;

/**
 * This is the base class of the code that wraps ALL interactions with the
 * Geni Server. The derived classes handle the authentication for the three
 * types of authetication flow: Server, Client & Desktop.
 *
 * The user application should NOT be accessing the object AT ALL (or for
 * that matter, anything in the ShmuelAKam\Greenjeans\Api\Core namespace). All needed access
 * to the server is already implemented by the API classes.
 * @internal
 * @see API_WrapperServer
 * @see API_WrapperClient
 * @see API_WrapperDesktop
 */
abstract class API_Wrapper
{
    protected const API_SERVER_URL     = "https://www.geni.com/api";
    protected const SANDBOX_SERVER_URL = "https://sandbox.geni.com/api";

    // The three values used to autheticate the application. These are set by
    // Geni when the application is registered.
    // TODO Probably should use some encryption mechanism to hide them.

    protected int    $AppNumber;
    protected string $AppID;
    protected string $AppSecret;

    /**
     * URL of the server being used (Server or Sandbox). Is set in the constructor.
     * @var string
     */
    protected string $ServerURL;

    /**
     * Parameters of the authetication token returned from Geni Server.
     * @var array<string, mixed>
     */
    protected array  $AuthTokenValues = [
        'access_token'  => '',   // Access token to be used with every API request
        'expires_in'    => 0,    // Seconds until the token will expire
        'refresh_token' => ''    // Token that can be used to get a new access token
    ];

    /**
     * Same value as $AuthTokenValues ['access_token']. Saved separately
     * for simpler/faster access.
     * @var string
     */
    protected string $AccessToken = '';

    /**
     * Code sent from Server to enable requesting access.
     * @see API_Wrapper::requestAccessToken
     * @var string
     */
    protected string $AccessCode = "";

    /**
     * Reference to current "singleton" instance.
     * Set in constructor.
     * @see API_Wrapper::getInstance
     * @var API_Wrapper
     */
    protected static ?API_Wrapper $activeInstance = null;

    //------------------------------------------------------

    /**
     * This flag is generally TRUE. Otherwise it causes this class to output every
     * API call made to the server. This is critical for debugging, but otherwise
     * junk. The output includes a timestamp, a running counter, and the call URL.
     *
     * The various call functions also have a $silent parameter. Both of them need
     * to be set for the output to show.
     * This flag has getter and setter functions. So in theory, the user can play
     * with the output at run-time.
     * @var bool
     */
    protected bool   $silent;

    /**
     * When set (together with $silent) outputs HTTP error messages. For example
     * HTTP 429 (Too Many Requests). BOTH flags must be set to output. This flag has
     * getter and setter functions (even though user shouldn't access this class at all).
     * @var bool
     */
    protected bool   $hideHTTP;

    /**
     * This flag is set when the application is running on Geni's sandbox
     * server. This server is currently semi-broken. So it can't be used.
     * @var bool
     */
    private bool   $onSandbox;

    /**
     * The running counter of API calls. Is used solely for the debug output.
     * For the curious, this counter has a static getter. BUT the user shouldn't
     * really be accessing this object at all.
     * @var integer
     */
    protected static int $numCalls = 0;

    //------------------------------------------------------

    /**
     * Request access parameters from Server.
     * @param string $accessCode initial code sent from Server.
     */
    abstract public function requestAccessToken(string $accessCode = null): void;

    /**
     * Refreshes the Access Token using a Refresh Token.
     * @return void
     */
    abstract protected function refreshToken(): void;

    public function checkRefresh(): bool
    {
        static $REFRESH_DELTA = 3600;

        $sessionRefreshed = false;
        if ($this->getExpiresIn() <= $REFRESH_DELTA)
        {
            $this->refreshToken();
            $sessionRefreshed = true;
        }

        return $sessionRefreshed;
    }

    //======================================================

    /**
     * Constructor. Sets object values, but does NOT authenticate.
     *
     * The first three parameters are the hard-coded application parameters
     * given by Geni when registering an application.
     * @param int    $number     Application number. Set by Geni.
     * @param string $ID         ID. Hash value.     Set by Geni.
     * @param string $secret     Authentication Secret. Set by Geni.
     *
     * @param bool   $silent     See comment for $silent    above.
     * @param bool   $hideHTTP   See comment for $hideHTTP  above.
     * @param bool   $useSandbox See comment for $onSandbox above.
     */
    protected function __construct(
        int    $number,
        string $ID,
        string $secret,
        bool   $silent = false,
        bool   $hideHTTP = false,
        bool   $useSandbox = false,
    ) {
        $this->AppNumber = $number;
        $this->AppID     = $ID;
        $this->AppSecret = $secret;

        $this->silent    = $silent;
        $this->hideHTTP  = $hideHTTP;
        $this->onSandbox = $useSandbox;

        $this->AccessCode = "-1";

        if (!$useSandbox)
        {
            $this->ServerURL = API_Wrapper::API_SERVER_URL;
        }
        else
        {
            $this->ServerURL = API_Wrapper::SANDBOX_SERVER_URL;
        }

        // Register this instance as the active one.
        self::setInstance($this);
    }

    /**
     * Returns the currently active instance.
     * Using 'static' ensures the runtime knows if it's a Desktop, Client, or Server instance.
     * * @return static
     */
    public static function getInstance(): API_Wrapper
    {
        if (self::$activeInstance === null)
        {
            throw new \Exception("Geni API Wrapper not initialized.");
        }

        return self::$activeInstance;
    }

    protected static function setInstance(API_Wrapper $active): void
    {
        self::$activeInstance = $active;
    }

    //======================================================

    /**
     * Basic GET request.
     * Wrap HTTP GET request (Sent to Server or Sandbox)
     * @param string  $API_URL  The API call, NOT including the server URL. For example '/profile...'.
     * @param bool    $silent   When set, do NOT log API call.
     *
     * @return array<string, string|array<string, mixed>>  Array of results (as is) returned by server.
     *                No processing. Empty array when call FAILS.
     */
    public function callAPI(string $API_URL, bool $silent = false): array
    {
        $callOptions = [
            'http' => [
                'method' => 'GET',
                'header' => [ 'Authorization: Bearer ' . $this->getAccessToken(),
                              'Content-type: application/x-www-form-urlencoded' ]
            ]
        ];

        $callStream = stream_context_create($callOptions);

        $API_URL = $this->getServerURL() . $API_URL;

        return $this->executeCall($API_URL, $callStream, 'GET ', $silent);
    }

    /**
     * POST Request.
     * @param string                 $API_URL  The API call, NOT including the server URL.
     *                               For example '/profile...'.
     * @param array<string, string|mixed>  $params   Call parameters.
     * @param bool   $silent   When set, do NOT log API call.
     *
     * @return array<string, string|array<string, mixed>>  Array of results (as is) returned by server.
     *                No processing. Empty array when call FAILS.
     */
    public function callAPI_POST(string $API_URL, ?array $params = [], bool $silent = false): array
    {
        if ($params == null)
        {
            $params = [];
        }

        $params ['access_token'] = $this->getAccessToken();

        $postOptions = [
            'http' => [
                'method'  => 'POST',
                'header'  => [ 'Content-type: application/x-www-form-urlencoded' ],
                'content' => http_build_query($params),
            ],
        ];

        $callStream = stream_context_create($postOptions);
        $API_URL    = $this->getServerURL() . $API_URL;

        return $this->executeCall($API_URL, $callStream, 'POST', $silent);
    }

    /**
     * POST Request with binary file.
     * @param string $API_URL    The API call, NOT including the server URL. For example '/profile...'.
     * @param array<string, string|array<string|mixed>>  $params     "Regular" parameters of the POST call.
     * @param string $fileBinary Binary "blob" as read from file with
     *                           file_get_contents for example.
     * @param string $fileName
     * @param string $fileType   File type encoding so that Server knows how to process it.
     * @param bool   $silent     When set, do NOT log API call.
     *
     * @return array<string, string|array<string, mixed>>  Array of results (as is) returned by server.
     *                No processing. Empty array when call FAILS.
     */
    public function callAPI_POST_WithBinary(
        string $API_URL,
        array  $params,
        string $fileBinary,
        string $fileName,
        string $fileType,
        bool   $silent = false,
    ): array {
        $params ['access_token'] = $this->getAccessToken();

        // Build the request.
        $postMultipart = $this->makeMultipart_POST_Request($params, $fileBinary, $fileName, $fileType);

        $callStream = stream_context_create($postMultipart);
        $API_URL    = $this->getServerURL() . $API_URL;

        return $this->executeCall($API_URL, $callStream, 'POST', $silent);
    }

    /**
     * Create array of parameters for POST call that include a binary file.
     * @param array<string, string|array<string, mixed>>  $params "Regular" parameters of the POST call.
     * @param string $fileBinary Binary "blob" as read from file with
     *                           file_get_contents for example.
     * @param string $fileName
     * @param string $fileType   File type encoding so that Server knows how to process it.
     * @return array<string, array<string, mixed>> Array of ALL parameters needed for call. This includes the
     *                           data string with the encoded parameters and binary, as 'content'.
     */
    protected function makeMultipart_POST_Request(
        array  $params,
        string $fileBinary,
        string $fileName,
        string $fileType,
    ): array {
        $boundary = "----------------" . md5((string) mt_rand());
        $data     = '';

        // Parse regular parameters.
        foreach ($params as $key => $val)
        {
            $data .= "--$boundary\r\n";
            $data .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
            $data .= "$val\r\n";
        }

        // Add section for binary file.
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"$fileName\"\r\n";
        $data .= "Content-Type: $fileType\r\n\r\n";
        $data .= $fileBinary . "\r\n";
        $data .= "--$boundary--\r\n";

        // Create complete array for request.
        return [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n"
                . 'Content-Length: ' . strlen($data) . "\r\n",
                'content' => $data,
            ],
        ];
    }

    //======================================================

    /**
     * Save Session Auth values to JSON file.
     * TODO This is probably NOT a good idea. At the least it should be encrypted.
     * @param string $fileName
     * @return bool Success of write.
     */
    public function store(string $fileName): bool
    {
        // Create JSON file containing session tokens.

        $tokes = [
            'access_token'  => $this->AuthTokenValues['access_token'],
            'expires_in'    => $this->AuthTokenValues['expires_in'],
            'refresh_token' => $this->AuthTokenValues['refresh_token'],
            'saved_at'      => time(),
            'date_time'     => date(DATE_RFC2822)
        ];

        $writeFile = file_put_contents($fileName, json_encode($tokes, JSON_PRETTY_PRINT));

        return $writeFile !== false;
    }

    /**
     * Get Session Auth values from previously saved JSON file.
     * @see API_Wrapper::store
     * @param  string $fileName
     * @return bool   false when values have EXPIRED.
     */
    public function retrieve(string $fileName): bool
    {
        if (!file_exists($fileName))
        {
            return false;
        }

        $content    = file_get_contents($fileName);
        $storedAuth = json_decode($content, true);

        if (!is_array($storedAuth))
        {
            return false;
        }

        $this->AuthTokenValues = $storedAuth;
        $this->AccessToken     = $this->AuthTokenValues['access_token'] ?? '';

        // Verify that values have not expired.
        $savedAt     = $this->AuthTokenValues['saved_at'] ?? 0;
        $expiresIn   = $this->AuthTokenValues['expires_in'] ?? 0;
        $timeExpires = $savedAt + $expiresIn;

        return (time() < ($timeExpires));
    }

    //======================================================

    /**
     * Send API call to server, and retry if throttling needed.
     * Retry-throttling is done by temporarily setting an exception handler
     * that checks for the HTTP 429 WARNING. The exception handler throws
     * API_Server_Exception when this happens, which causes the function
     * to sleep, and retry.
     *
     * @param string   $API_URL     API URL to send, does NOT include server path.
     * @param resource|NULL $callStream  stream context, includes all parameters (if any).
     *                              NULL if not needed.
     * @param string   $CallType    GET /POST /Auth. // REMOVE parameter.
     *
     * @return array<string, string|array<string, mixed>> Array of results (as is)
     *                              returned by server. No processing. Empty array when call FAILS.
     */
    private function executeCall(string $API_URL, mixed $callStream, string $CallType, bool $silent = false): array
    {
        self::$numCalls++;
        if (!$silent && !$this->isSilent())
        {
            $this->logAPICall($API_URL, $CallType);
        }

        // Set up mechanism to catch HTTP 429 "too many calls" error.
        // When this error happens we throttle (by sleep()) and retry call.

        /**
         * Bind the specific local instance to the error handler.
         * This bypasses the Global Singleton (Recorder).
         */
        set_error_handler(function (int $errno, string $errstr) {
            return self::handleWarning($errno, $errstr, $this);
        }, E_WARNING);

        $retryCount = 0;
        $retryCall  = false;
        $callReply  = false;

        do
        {
            try
            {
                //--------------------------------------------------
                $callReply = file_get_contents($API_URL, false, $callStream);
                //--------------------------------------------------

                // IF this line reached, error handler was NOT called. This means
                // that the call was successful. No need to retry.
                $retryCall = false;
            }
            catch (API_Server_Exception $e)
            {
                // Too many calls (or gateway error). Throttle by going to sleep, and retry call.
                $retryCall = true;
                $retryCount++;

                // Should experiment with this value. The "window" Geni uses is 5 or 10 seconds.
                // So sleeping for 3 seconds is a lot.
                sleep(3);
            }
        }
        while ($retryCall && $retryCount < 5);

        restore_error_handler();

        if ($callReply !== false)
        {
            $data = json_decode($callReply, true);

            $ReplyArray = is_array($data) ? $data : [];
        }
        else
        {
            // Call FAILED. Return empty array.
            $ReplyArray = [];
        }

        return $ReplyArray;
    }

    /**
     * Make Authentication call to API Server.
     * Send AUTH specific call to Server. Uses slighlty different URL than API calls.
     * @param string  $Auth_URL  The API call, NOT including the server URL.
     * @return array<string, string>  Array of results (as is) returned by server.
     *                           No processing. Empty array when call FAILS.
     */
    protected function callAPI_Auth(string $Auth_URL): ?array
    {
        // Auth calls do NOT have the '/api' part of the server URL.
        $authServer_URL = substr($this->getServerURL(), 0, -4);
        $fullAuth_URL   = $authServer_URL . $Auth_URL;

        return $this->executeCall($fullAuth_URL, null, 'Auth', $this->isSilent());
    }

    /**
     * Internal handler for HTTP warnings received from Server.
     * @param int $errno
     * @param string $errstr
     * @param API_Wrapper $instance The specific instance making the call.
     * @throws API_Server_Exception
     * @return bool
     */
    public static function handleWarning(int $errno, string $errstr, API_Wrapper $instance): bool
    {
        // Use the settings of the API_Wrapper passed in, NOT the global singleton.
        // This makes a difference when Recording mocking fixtures, which uses an
        // API_WrapperRecorder instance that wraps a regular API_Wrapper subtype.

        if (!$instance->isSilent() && !$instance->isHideHTTP())
        {
            $httpErrPos = strpos($errstr, 'HTTP/1');

            if ($httpErrPos !== false)
            {
                $httpError = (int) trim(substr($errstr, $httpErrPos + 9, 3));

                if ($httpError === 429)
                {
                    echo "\t\t--- (429) ---------- THROTTLE ------------------\r\n";
                    // Pass the specific instance into the exception
                    throw new API_Server_Exception(API_Exception::THROTTLE, $instance);
                }
                elseif ($httpError === 502)
                {
                    echo "\t\t--- (502) ---------- Bad Gateway ---------------\r\n";
                    throw new API_Server_Exception(API_Exception::GATEWAY_ERROR, $instance);
                }
                elseif ($httpError === 500)
                {
                    echo "\t\t--- (500) ---------- Internal Server Error -----\r\n";
                    throw new API_Server_Exception(API_Exception::INTERNAL_ERROR, $instance);
                }
                else
                {
                    echo "\t\t--- ($httpError) -------------------------------\r\n";
                }
            }
        }
        return true;
    }

    /**
     * Log the API call to the mocked server.
     * @param string $API_URL
     * @param string $CallType GET /POST
     */
    protected function logAPICall(string $API_URL, string $CallType): void
    {
        echo "\n" . time() . str_pad((string) self::$numCalls, 6, ' ', STR_PAD_LEFT)
             . " API " . str_pad($CallType, 4) . " : $API_URL\n";
    }

    //======================================================
    // Getters.
    // I prefer using getter to access internal variables. This prevents
    // accidental changes to the variables, and makes it super easy to find
    // where it is changed/used.

    protected function getAccessToken(): string
    {
        return $this->AccessToken;
    }

    protected function getAppID(): string
    {
        return $this->AppID;
    }

    protected function getAppSecret(): string
    {
        return $this->AppSecret;
    }

    protected function getRefreshToken(): string
    {
        return $this->AuthTokenValues ['refresh_token'];
    }

    public function getExpiresIn(): string
    {
        return $this->AuthTokenValues ['expires_in'];
    }

    protected function getServerURL(): string
    {
        return $this->ServerURL;
    }

    public static function getNumCalls(): int
    {
        return API_Wrapper::$numCalls;
    }

    //------------------------------------------------------
    // Setters and getter for debug log output.

    public function setSilent(bool $silent): void
    {
        $this->silent = $silent;
    }

    public function setHideHTTP(bool $hideHTTP): void
    {
        $this->hideHTTP = $hideHTTP;
    }

    public function isSilent(): bool
    {
        return $this->silent;
    }

    public function isHideHTTP(): bool
    {
        return $this->hideHTTP;
    }

    public function isOnSandbox(): bool
    {
        return $this->onSandbox;
    }

    //------------------------------------------------------

    protected function getAccessCode(): string
    {
        return $this->AccessCode;
    }

    protected function setAccessCode(string $accessCode): void
    {
        if ($accessCode != null)
        {
            $this->AccessCode = trim($accessCode);
        }
    }
}

/**
 * Exceptions thrown by the error handler, and caught by the executeCall
 * function to catch situations that require sending the API call again.
 * @see API_WrapperWarningHandler
 * @see API_Wrapper::executeCall
 */
class API_Server_Exception extends API_Exception
{
    private API_Wrapper $wrapperInstance;

    /**
     * @param int $errCode The error code (e.g. from your API_Exception enum/values)
     * @param API_Wrapper $instance The specific instance (Inner Server) that triggered the error
     */
    public function __construct(int $errCode, API_Wrapper $instance)
    {
        // Pass the message and code up to the base API_Exception
        parent::__construct('Server sent error', $errCode);

        $this->wrapperInstance = $instance;
    }

    /**
     * @return API_Wrapper Returns the specific instance that failed
     */
    public function getInstance(): API_Wrapper
    {
        return $this->wrapperInstance;
    }
}
