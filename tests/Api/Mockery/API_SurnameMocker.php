<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

class API_SurnameMocker extends API_BaseMocker
{
    private const PRESET_TEST_NAMES = [
        'surname-799'  => 'andersen',
        'surname-1003' => 'dash',
        'surname-2503' => 'potter',
        'surname-4237' => 'kentner',
        'surname-5555' => 'wait',
        'surname-100'  => 'surname-not-exist',
    ];

    //======================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    //======================================================
    // API calls.

    /**
     * GET surname
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        return $this->getFile('get');
    }

    /**
     * GET surname/followers
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function followers(array $API_Params, array $urlActions = []): array
    {
        return $this->getFile('followers');
    }

    /**
     * GET surname/profiles
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function profiles(array $API_Params, array $urlActions = []): array
    {
        return $this->getFile('profiles');
    }

    //------------------------------------------------------

    /**
     * @param string $call
     * @return array <string, mixed|array <string, mixed>>|array{} Fixture file contents.
     */
    protected function getFile(string $call): array
    {
        $fixtureValues = [];
        $surname       = $this->getSubject();
        $listSuffix    = '';

        if ($call !== 'get')
        {
            $listSuffix = '-' . $call;
        }

        if (isset(self::PRESET_TEST_NAMES [$surname]))
        {
            $jsonFile = self::PRESET_TEST_NAMES [$surname] . $listSuffix . '.json';

            $fixtureValues = $this->loadFixtureDirectly('surname', $call, $jsonFile);
        }

        return $fixtureValues;
    }

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array{}
     */
    public function createObjectByID(string $ID): array
    {
        return [];
    }
}
