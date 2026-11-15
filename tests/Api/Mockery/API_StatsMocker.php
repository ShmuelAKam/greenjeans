<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

class API_StatsMocker extends API_BaseMocker
{
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
     * GET stats
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->loadFixtureDirectly('stats', 'get', 'stats.json');

        return $fixtureValues;
    }

    /**
     * GET stats/world-family-tree
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function world_family_tree(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->loadFixtureDirectly('stats', 'world-family-tree', 'world-family-tree.json');

        return $fixtureValues;
    }

    //------------------------------------------------------

    /**
     * {@inheritDoc}
     * NO need for this method. No objects are created.
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array{}
     */
    public function createObjectByID(string $ID): array
    {
        return [];
    }
}
