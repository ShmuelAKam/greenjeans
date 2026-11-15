<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Core;

use ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker;

/**
 * This Wrapper class replaces the Geni Server with a Mocker.
 * @internal
 */
class API_WrapperMocker extends API_WrapperTesting
{
    private ?string $forcedHash = null;

    /**
     * Constructor.
     */
    public function __construct(bool $silent = true, bool   $hideHTTP = true)
    {
        parent::__construct();

        $this->silent   = $silent;
        $this->hideHTTP = $hideHTTP;

        // Reset the mockers and objects created in previous tests.
        API_BaseMocker::reset();
    }

    //------------------------------------------------------

    /**
     * Basic GET request.
     * Wrap HTTP GET request (Sent to Server or Sandbox)
     * @param string  $API_URL  The API call, NOT including the server URL. For example '/profile...'.
     * @param bool    $silent   When set, do NOT log API call.
     *
     * @return array <string, int|string|array <string, int|string>>
     *      Array of results (as is) returned by server. No processing.
     *      Empty array when call FAILS.
     */
    public function callAPI(string $API_URL, bool $silent = false): array
    {
        $this->logCall($API_URL, 'GET', $silent);

        $params = [];

        // Isolate the query string from the path
        if (strpos($API_URL, '?') !== false)
        {
            $queryString = parse_url($API_URL, PHP_URL_QUERY);
            parse_str($queryString, $params);
        }

        return $this->loadFixture($API_URL, $params);
    }

    /**
     * POST Request.
     * @param string $API_URL  The API call, NOT including the server URL. For example '/profile...'.
     * @param array <string, int|string|array <string, int|string>> $params   Call parameters.
     * @param bool   $silent   When set, do NOT log API call.
     *
     * @return array <string, int|string|array <string, int|string>>
     *      Array of results (as is) returned by server. No processing.
     *      Empty array when call FAILS.
     */
    public function callAPI_POST(string $API_URL, ?array $params = [], bool $silent = false): array
    {
        $this->logCall($API_URL, 'POST', $silent);

        return $this->loadFixture($API_URL, $params);
    }

    /**
     * POST Request with binary file.
     * @param string $API_URL    The API call, NOT including the server URL. For example '/profile...'.
     * @param array <string, int|string|array <string, int|string>> $params
     *      "Regular" parameters of the POST call.
     * @param string $fileBinary Binary "blob" as read from file with
     *                           file_get_contents for example.
     * @param string $fileName
     * @param string $fileType   File type encoding so that Server knows how to process it.
     * @param bool   $silent     When set, do NOT log API call.
     *
     * @return array <string, int|string|array <string, int|string>>
     *      Array of results (as is) returned by server. No processing.
     *      Empty array when call FAILS.
     */
    public function callAPI_POST_WithBinary(
        string $API_URL,
        array  $params,
        string $fileBinary,
        string $fileName,
        string $fileType,
        bool   $silent = false,
    ): array {
        $this->logCall($API_URL, 'POST', $silent);

        // We use the basename of the file so the mock is tied to a specific file name

        return $this->loadFixture($API_URL, $params, basename($fileName));
    }

    /**
     * Log the "API" call to the mocked server.
     * The logged URL does NOT include the server URL.
     * @param string $API_URL
     * @param string $CallType GET /POST
     * @param bool   $silent
     * @see \ShmuelAKam\Greenjeans\Api\Core\API_Wrapper::logAPICall
     */
    protected function logCall(string $API_URL, string $CallType, bool $silent): void
    {
        self::$numCalls++;

        if (!$silent && !$this->isSilent())
        {
            $this->logAPICall($API_URL, $CallType);
        }
    }

    //------------------------------------------------------

    /**
     * The "Engine": Converts request details into a library lookup
     * @param string $API_URL Full URL of API call.
     * @param array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param string $suffix  Additional information (like specific test-name).
     * @return array <string, mixed|array <string, mixed>>
     */
    private function loadFixture(string $API_URL, ?array $API_Params = null, string $suffix = ''): array
    {
        // 1. If params are NULL (common in GET), ensure we have an empty array
        $API_Params = $API_Params ?? [];

        // 2. Resolve Path (Calculates the hash based on URL + Params)
        $path = ($this->forcedHash !== null)
            ? $this->resolveForcedPath()
            : $this->getFixturePath($API_URL, $API_Params, $suffix);

        // 3. Load Template
        $fixture = [];
        if (file_exists($path))
        {
            $fixture = json_decode(file_get_contents($path), true) ?: [];
            // self::logFileUse ($path, FALSE);
        }

        // 4. Send to Engine
        $API_Reply = API_BaseMocker::createDynamicFixture($API_URL, $API_Params, $fixture, $suffix);

        // 5. The user metadata functions are a special case, because their data is JSON coded.

        if (API_BaseMocker::isSpecificTest('ShmuelAKam\Greenjeans\Tests\Api\API_UserTest', 'testUpdateMetadata')
            || API_BaseMocker::isSpecificTest('ShmuelAKam\Greenjeans\Tests\Api\API_UserTest', 'testGetMetadata'))
        {
            // Not all of the API calls in these tests include 'data', e.g. get 'user'.

            if (isset($API_Reply ['data']))
            {
                $API_Reply ['data'] = json_decode($API_Reply ['data'], true);
            }
        }

        return $API_Reply;
    }

    /**
     * Finds the physical file path for a hash that was manually set by the test.
     */
    private function resolveForcedPath(): string
    {
        $hash             = $this->forcedHash;
        $this->forcedHash = null;

        // We use a RecursiveDirectoryIterator to find the hash
        // anywhere inside our 12 main folders.

        $it = new \RecursiveDirectoryIterator(self::getMockingBaseDir());

        foreach (new \RecursiveIteratorIterator($it) as $file)
        {
            if ($file->getBasename('.json') === $hash)
            {
                return $file->getPathname();
            }
        }

        throw new \Exception("Geni Mock Error: Forced Hash [$hash] not found in subdirectories.");
    }

    //------------------------------------------------------

    /**
     * This bypasses the URL hashing logic.
     */
    public function setNextHash(string $hash): void
    {
        $this->forcedHash = $hash;
    }

    /**
     * Clears the override so the mock returns to normal URL-hashing.
     */
    public function clearNextHash(): void
    {
        $this->forcedHash = null;
    }
}
