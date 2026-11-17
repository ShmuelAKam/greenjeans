<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Core;

use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * The Recorder Wrapper
 * Used to capture live Geni API responses and save them as fixtures.
 */
class API_WrapperRecorder extends API_WrapperTesting
{
    /**
     * This is the Wrapper that does all of the actual work sent to this wrapper,
     * after the response is recorded.
     *
     * Making it a data-member instead of using inheritence makes the Recorder
     * more flexible (the Server/Oath type is independent of the Recorder), and
     * makes the inheritence tree of all the Wrappers much cleaner.
     * @var API_Wrapper
     */
    private API_Wrapper $server;

    //------------------------------------------------------

    public function __construct(API_Wrapper $actualServer)
    {
        // The call to the constructor will cause THIS Wrapper to be registered as the
        // global instance, instead of the $actualServer that was created first. This
        // is what we want, so that all calls to getInstance return the Recorder object.

        parent::__construct();

        if (!is_dir(self::getMockingBaseDir()))
        {
            mkdir(self::getMockingBaseDir(), 0777, true);
        }

        // This wrapper does all the work.
        $this->server = $actualServer;

        // Defaults to no output from API calls to the screen.

        $this->server->setHideHTTP(false);
        $this->server->setSilent(false);
    }

    //------------------------------------------------------
    // Implement all of the Setter & Getter functions as pass-through to the "real" wrapper.

    public function setSilent(bool $silent): void
    {
        $this->server->setSilent($silent);
    }

    public function setHideHTTP(bool $hideHTTP): void
    {
        $this->server->setHideHTTP($hideHTTP);
    }

    public function isSilent(): bool
    {
        return $this->server->isSilent();
    }

    public function isHideHTTP(): bool
    {
        return $this->server->isHideHTTP();
    }

    public function isOnSandbox(): bool
    {
        return $this->server->isOnSandbox();
    }

    //------------------------------------------------------
    // API Call functions. These call the server, get the response and record it.
    // They then return it to the caller. This makes the whole process transparent.

    /**
     * Basic GET request.
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Api\Core\API_Wrapper::callAPI()
     */
    public function callAPI(string $API_URL, bool $silent = false): array
    {
        $serverReply = $this->server->callAPI($API_URL);

        $this->recordResponse($API_URL, null, '', $serverReply);

        return $serverReply;
    }

    /**
     * POST Request.
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Api\Core\API_Wrapper::callAPI_POST()
     */
    public function callAPI_POST(string $API_URL, ?array $callParam = [], bool $silent = false): array
    {
        $serverReply = $this->server->callAPI_POST($API_URL, $callParam);

        $this->recordResponse($API_URL, $callParam, '', $serverReply);

        return $serverReply;
    }

    /**
     * POST Request with binary file.
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Api\Core\API_Wrapper::callAPI_POST_WithBinary()
     */
    public function callAPI_POST_WithBinary(
        string $API_URL,
        array  $callParam,
        string $fileBinary,
        string $fileName,
        string $fileType,
        bool   $silent = false,
    ): array {
        $serverReply = $this->server->callAPI_POST_WithBinary($API_URL, $callParam, $fileBinary, $fileName, $fileType, $silent);

        // We use the filename as a suffix to differentiate uploads of different files
        $this->recordResponse($API_URL, $callParam, basename($fileName), $serverReply);

        return $serverReply;
    }

    //------------------------------------------------------

    /**
     * Save the responce as a JSON file, using the elements of the $API_URL for the path.
     * @param string $API_URL  The API call, NOT including the server URL. For example '/profile...'.
     * @param array  <string, string> $callParam Parameters of API call.
     * @param string $suffix  Additional information (like specific test-name).
     * @param array  <string, string|array <string, mixed>> $serverReply
     */
    private function recordResponse(string $API_URL, ?array $callParam, string $suffix, array $serverReply): void
    {
        $fullPath  = $this->getFixturePath($API_URL, $callParam, $suffix);
        $directory = dirname($fullPath);

        // The 'true' parameter allows for recursive directory creation
        if (!is_dir($directory))
        {
            mkdir($directory, 0777, true);
        }

        file_put_contents($fullPath, json_encode($serverReply, JSON_PRETTY_PRINT));
        //API_WrapperTesting::logFileUse ($fullPath, TRUE);
    }
}
