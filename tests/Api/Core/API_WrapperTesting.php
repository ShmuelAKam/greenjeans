<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Core;

use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * This wrapper class serves as the "base" class for the Recorder & Mocker classes.
 *
 * It was created to keep their shared code outside of the generic base API_Wrapper.
 * The reason it needs to extend API_Wrapper is to plug into the getInstance mechanism
 * used throughout the package.
 * @see API_WrapperMocker
 * @see API_WrapperRecorder
 */
abstract class API_WrapperTesting extends API_Wrapper
{
    protected const FOLDERS = [
        'document', 'photo',  'photo-album', 'profile', 'project',
        'revision', 'stats',  'surname',     'union',   'user',
        'video',    'misc'
    ];

    //------------------------------------------------------

    /**
     * Constructor.
     */
    public function __construct()
    {
        // This object does NOT need the base class, so it does not call its constructor.

        self::setInstance($this);
    }

    //------------------------------------------------------

    /**
     * Standardized path cleaner.
     * Staticaly available to resolve URLs into class-tree paths.
     * @param  string|array<string> $url
     * @return string
     */
    protected static function getRelativeDirectory(string|array $url): string
    {
        // 1. Normalize input (Handle Array vs String)
        $urlInput = is_array($url) ? (string) reset($url) : (string) $url;
        $pathPart = strtok($urlInput, '?');

        // 2. Explode into segments
        $trimmed  = trim((string) $pathPart, '/');
        $segments = ($trimmed === '') ? [] : explode('/', $trimmed);

        $root   = 'misc';
        $action = '';

        // 3. Extract Root (Resource)
        if (!empty($segments))
        {
            $rawRoot = (string) array_shift($segments);
            // profile-123 -> profile
            $root = preg_replace('/-[g0-9]+$/i', '', $rawRoot);
        }

        // 4. Extract Action
        if (!empty($segments))
        {
            $rawAction = (string) array_shift($segments);
            // Only capture if it's not a pure numeric ID
            if (!preg_match('/^[g0-9]+$/', $rawAction))
            {
                $action = preg_replace('/-[g0-9]+$/i', '', $rawAction);
            }
        }

        // 5. Whitelist Validation
        $folder = strtolower($root);
        if ($folder === '' || !in_array($folder, self::FOLDERS))
        {
            $folder = 'misc';
        }

        // 6. Return Clean String (No trailing slash)
        return ($action !== '')
        ? $folder . DIRECTORY_SEPARATOR . $action
        : $folder;
    }

    /**
     * @param string|array<string> $url
     * @param array<string> $params
     * @param string $suffix  Additional information (like specific test-name).
     * @return string
     */
    protected function generateStableHash(string|array $url, ?array $params = null, string $suffix = ''): string
    {
        $urlInput = is_array($url) ? (string) reset($url) : (string) $url;

        $sigParams = $params;
        if (is_array($sigParams))
        {
            // 1. Remove volatile fields
            unset($sigParams['created_at'], $sigParams['updated_at'], $sigParams['timestamp']);

            // 2. Structural Masking: Keep the Keys, Kill the Values
            array_walk_recursive($sigParams, function (&$value) {
                $value = 'MOCKED_VALUE';
            });

            // 3. Sort keys so ['a','b'] and ['b','a'] match
            ksort($sigParams);
        }

        // 4. Mask the URL (ID neutralization stays)
        $anonUrl  = preg_replace('/[a-z_-]*[0-9]{4,}/i', 'VAL-ID', $urlInput);
        $cleanUrl = preg_replace('/access_token=[^&]+/', 'token=MOCK', $anonUrl);

        // 5. Generate Structural Hash
        $sig = $cleanUrl . (empty($sigParams) ? '' : serialize($sigParams)) . $suffix;

        return md5($sig);
    }

    /**
     * The Shared Map: Both Recorder and Mock use this to stay in sync.
     * Generates an ID-agnostic file path based on the URL and parameters.
     * @param string|array<string> $url
     * @param array<string> $params
     * @param string $suffix  Additional information (like specific test-name).
     * @return string
     */
    protected function getFixturePath(string|array $url, ?array $params = null, string $suffix = ''): string
    {
        // Routing Logic
        $subDir = self::getRelativeDirectory($url);

        // Fingerprinting Logic
        $hash = $this->generateStableHash($url, $params, $suffix);

        // Path Anchoring
        $base = rtrim(self::getMockingBaseDir(), DIRECTORY_SEPARATOR);

        return $base . DIRECTORY_SEPARATOR . ltrim($subDir, '\\/') . DIRECTORY_SEPARATOR . $hash . '.json';
    }

    /**
     * Empty implementation of abstract function from base class.
     */
    public function requestAccessToken(string $accessCode = null): void {}

    public function refreshToken(): void {}

    public function isOnSandbox(): bool
    {
        return false;
    }

    public static function getMockingBaseDir(): string
    {
        return $GLOBALS ['TEST_FIXTURES_ROOT'];
    }

    //------------------------------------------------------

    /**
     * Create list of the JSON files recorded or used by the mocking engine.
     * The three calls to this function are commented out. Uncomment to enable.
     * @param string $fileName   Name of mock-fixture file.
     * @param bool   $recording  Recording or using the file.
     */
    public static function logFileUse(string $fileName, bool $recording): void
    {
        $logName = self::getMockingBaseDir();

        if ($recording)
        {
            $logName .= '/mockerRecord.txt';
        }
        else
        {
            $logName .= '/mockerUsed.txt';
        }

        $fileName = "$fileName\r\n";

        file_put_contents($logName, $fileName, FILE_APPEND);
    }
}
