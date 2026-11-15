<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

class API_DefaultMocker extends API_BaseMocker
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
     * Generic 'get'.
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        return $fixtureValues;
    }

    /**
     * Handler for "everything else".
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function catch_all(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        return $fixtureValues;
    }

    //------------------------------------------------------

    /**
     * Create set of test-objects, based on IDs, and class.
     * This function creates array 'objects' from the array of IDs.
     * @param string $ID
     * @return array{}
     */
    public function createObjectByID(string $ID): array
    {
        return [];
    }
}
