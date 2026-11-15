<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

class API_UserMocker extends API_BaseMocker
{
    private string $metadata;

    //======================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->metadata = '';
    }

    //======================================================
    // API calls.

    /**
     * GET user
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->loadFixtureDirectly('user', 'get', 'shmuel.json');

        return $fixtureValues;
    }

    /**
     * GET user/add
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add(array $API_Params, array $urlActions = []): array
    {
        $name = $API_Params ['firstName'] . ' ' . $API_Params ['lastName'];
        $type = 'basic';

        $fixture = [
            'account_type' => $type,
            'name'         => $name,
        ];

        return $fixture;
    }

    //------------------------------------------------------

    /**
     * GET user/metadata
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function metadata(array $API_Params, array $urlActions = []): array
    {
        return [ 'data' => $this->metadata ];
    }

    /**
     * POST user/update_metadata
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function update_metadata(array $API_Params, array $urlActions = []): array
    {
        $this->metadata = $API_Params ['data'];

        return [ 'data' => $this->metadata ];
    }

    //------------------------------------------------------

    /**
     * GET user/followed_documents
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function followed_documents(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'followed_documents');
    }

    /**
     * GET user/followed_profiles
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function followed_profiles(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'followed_profiles');
    }

    /**
     * GET user/followed_projects
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function followed_projects(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'followed_projects');
    }

    /**
     * GET user/followed_surnames
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function followed_surnames(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'followed_surnames');
    }

    /**
     * GET user/managed_profiles
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function managed_profiles(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'managed_profiles');
    }

    /**
     * GET user/max_family
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function max_family(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'max_family');
    }

    /**
     * GET user/my_albums
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function my_albums(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'my_albums');
    }

    /**
     * GET user/my_labels
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function my_labels(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'my_labels');
    }

    /**
     * GET user/uploaded_documents
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function uploaded_documents(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'uploaded_documents');
    }

    /**
     * GET user/uploaded_photos
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function uploaded_photos(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'uploaded_photos');
    }

    /**
     * GET user/uploaded_videos
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function uploaded_videos(array $API_Params, array $urlActions = []): array
    {
        return $this->getUserList($API_Params, 'uploaded_videos');
    }

    //------------------------------------------------------

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  string $method Name of list (method).
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function getUserList(array $API_Params, string $method): array
    {
        $fileName    = str_replace('_', '-', $method);
        $pageNum     = $API_Params ['page'] ?? 1;
        $fixtureName = "$fileName-$pageNum.json";

        $fixtureValues = $this->loadFixtureDirectly('user', $fileName, $fixtureName);

        return $fixtureValues;
    }

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array <string, mixed|array <string, mixed>>
     */
    public function createObjectByID(string $ID): array
    {
        return [];
    }
}
