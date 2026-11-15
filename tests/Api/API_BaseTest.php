<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use PHPUnit\Framework\TestCase;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryEvents;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryLocations;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotos;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProfiles;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryUnions;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryVideos;
use ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker;

/**
 * Base class for all API test cases.
 * For now it only adds a call to reset the mocking engine (if relevant), before each test.
 */
class API_BaseTest extends TestCase
{
    /** @var bool When set, the mocking engine is reset before every test. */
    private static bool $mockingRun = false;

    /**
     * @var string The profile-ID of the user running the script. This is needed
     * for some tests. When running in mocking mode, there is no way to get the
     * "real" profile-ID (because we don't call the Server). Instead we use MY
     * profile-ID, as there are fixtures for it.
     */
    private static string $runnerProfileID;

    /**
     * Prepares the environment before running each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restoreGlobals();

        // Reset the mocking engine.
        if (self::$mockingRun)
        {
            API_BaseMocker::reset();
        }
    }

    // =====================================================
    // Test providers.

    /**
     * Provider for tests that do NOT need specific data, and just need
     * to be called a few times. The 'parameter' value is called $ignore.
     * @return array <array <int>>
     */
    public static function provideRunCount(): array
    {
        return [
            [  1 ],
            [  2 ],
            [  3 ],
            [  4 ],
            [  5 ]
        ];
    }

    /**
     * @return array <string, array <bool>>
     */
    public static function provideTwoFlags(): array
    {
        return [
            'T-T' => [ true,     true   ],
            'T-F' => [ true,     false  ],
            'F-T' => [ false,    true   ],
            'F-F' => [ false,    false  ]
        ];
    }

    /**
     * @return array <string, array <bool>>
     */
    public static function provideFourFlags(): array
    {
        return [
            'T-T-T-T' => [   true,   true,     true,    true   ],
            'T-T-T-F' => [   true,   true,     true,    false  ],
            'T-T-F-T' => [   true,   true,     false,   true   ],
            'T-T-F-F' => [   true,   true,     false,   false  ],

            'T-F-T-T' => [   true,   false,    true,    true   ],
            'T-F-T-F' => [   true,   false,    true,    false  ],
            'T-F-F-T' => [   true,   false,    false,   true   ],
            'T-F-F-F' => [   true,   false,    false,   false  ],

            'F-T-T-T' => [   false,  true,     true,    true   ],
            'F-T-T-F' => [   false,  true,     true,    false  ],
            'F-T-F-T' => [   false,  true,     false,   true   ],
            'F-T-F-F' => [   false,  true,     false,   false  ],

            'F-F-T-T' => [   false,  false,    true,    true   ],
            'F-F-T-F' => [   false,  false,    true,    false  ],
            'F-F-F-T' => [   false,  false,    false,   true   ],
            'F-F-F-F' => [   false,  false,    false,   false  ],
        ];
    }

    /**
     * Provider for tests that do NOT need specific data, other than the SIZE
     * of data-set to create.
     *
     * @return array <array <int>>
     */
    public static function provideSetSize(): array
    {
        return [
            [  1 ],
            [  2 ],
            [  5 ],
            [  7 ],
            [ 11 ]
        ];
    }

    /**
     * Provider for tests that need to create large data-sets.
     *
     * @return array <array <int>>
     */
    public static function provideLargeSetSize(): array
    {
        return [
            [ 15 ],
            [ 20 ],
            [ 30 ]
        ];
    }

    // =====================================================
    // Helper functions.

    /**
     * Take provider array and multiply it with flags.
     * This function is recursive, adding one set of flags each time around.
     * Example, for 3 flags, the flags start with TTT, and continue to TTF,
     * TFT... (in binary values from 7 to 0).
     *
     * @param  array <mixed> $provided
     * @param  int           $numFlagsToAdd
     * @return array <string, array <mixed>>
     */
    protected static function flagify(array $provided, int $numFlagsToAdd = 2): array
    {
        $flagged = [];
        foreach ($provided as $provKey => $prov)
        {
            $keyT = $provKey . '-T';
            $keyF = $provKey . '-F';

            $flagged [$keyT] = [... $prov, true];
            $flagged [$keyF] = [... $prov, false];
        }

        $numFlagsToAdd--;

        if ($numFlagsToAdd > 0)
        {
            $flagged = self::flagify($flagged, $numFlagsToAdd);
        }

        return $flagged;
    }

    // -----------------------------------------------------

    protected static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    protected static function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    // =====================================================
    // Custom assert functions.

    // =====================================================

    /**
     * Allows the start-up script to set the flag on start.
     */
    public static function setMockingRun(): void
    {
        self::$mockingRun = true;
    }

    /**
     * Set a value for the profile-ID of the user running the script.
     * @see API_BaseTest::$runnerProfileID
     */
    public static function setRunningProfileID(): void
    {
        if (self::$mockingRun)
        {
            // There is NO server to query for "real" ID. Use my own ID
            // because there are fixtures for it.

            self::$runnerProfileID = 'profile-9288190';
        }
        else
        {
            // Call the server to find user's ID.

            $userProfile = API_Profile::getProfileSelf(brief: true);

            self::$runnerProfileID = $userProfile->getID();
        }
    }

    /**
     * Return profile-ID of the user running the script.
     * @see API_BaseTest::$runnerProfileID
     */
    public static function getRunningProfileID(): string
    {
        return self::$runnerProfileID;
    }

    /**
     * For some fragged reason phpunit "looses" the global variables at the
     * beginning of every test. This function enables them to be found again.
     * I do NOT want to recreate the factories every time, because some of
     * them keep internal counters.
     */
    private function restoreGlobals(): void
    {
        /**
         * These are global factory objects to create test objects used by the
         * various test classes.
         *
         * @var FactoryDates     $dateFactory
         * @var FactoryDocuments $documentFactory
         * @var FactoryEvents    $eventFactory
         * @var FactoryLocations $locationFactory
         * @var FactoryPhotos    $photoFactory
         * @var FactoryProfiles  $profileFactory
         * @var FactoryUnions    $unionFactory
         * @var FactoryVideos    $videoFactory
         */

        global $dateFactory,    $documentFactory, $eventFactory, $locationFactory, $photoFactory,
        $profileFactory, $unionFactory,    $videoFactory;

        $dateFactory     = $GLOBALS['dateFactory'    ];
        $documentFactory = $GLOBALS['documentFactory'];
        $eventFactory    = $GLOBALS['eventFactory'   ];
        $locationFactory = $GLOBALS['locationFactory'];
        $photoFactory    = $GLOBALS['photoFactory'   ];
        $profileFactory  = $GLOBALS['profileFactory' ];
        $unionFactory    = $GLOBALS['unionFactory'   ];
        $videoFactory    = $GLOBALS['videoFactory'   ];
    }
}
