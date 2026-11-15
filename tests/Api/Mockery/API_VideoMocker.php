<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

class API_VideoMocker extends API_MediaMocker
{
    /**
     * @var array <string, string|TRUE>
     */
    private const VIDEOS_ON_SERVER = [
        'video-154860' => 'Countdown-05.mp4', // 6000000226284945836. Going Going Gone
        'video-154862' => 'Countdown-10.mp4', // 6000000226284855895. 'Title'
        'video-154864' => 'Countdown-05.wmv', // 6000000226284733942. 'Title'
        'video-142576' => 'Countdown-05.wmv', // 6000000222761121839. Alles Kaputen
        'video-142577' => true,               // 6000000222761042928. The Final Countdown
        'video-143010' => true,               // 6000000222772306833. Going Going Gone

    ];

    //======================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setObjectType('video');
    }

    //======================================================
    // API calls.
    // All of the other API calls are implemented in the API_MediaMocker base class.

    /**
     * POST /video/add
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add(array $API_Params, array $urlActions = []): array
    {
        $VALID_FIELDS = [ 'date', 'description', 'file', 'title' ];

        return parent::_add($API_Params, $VALID_FIELDS);
    }

    //------------------------------------------------------

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_MediaMocker::getFileList()
     * @return array <string, string|TRUE>
     */
    protected function getFileList(): array
    {
        return self::VIDEOS_ON_SERVER;
    }
}
