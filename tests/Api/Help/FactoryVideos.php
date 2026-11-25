<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;

class FactoryVideos extends FactoryBase
{
    // See END of class, for class constants.

    public function __construct()
    {
        parent::__construct(ShredderVideos::class);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    // ======================================================

    /**
     * Create video using parameter values.
     * The basic function.
     *
     * @param string $title
     * @param string $dateJSON
     * @param string $description
     * @param string $fileName
     * @param string $fileType
     *
     * @return API_Video
     */
    public function makeVideo(
        string  $title,
        ?string $dateJSON = null,
        ?string $description = null,
        ?string $fileName = null,
        ?string $fileType = null,
    ): ?API_Video {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        if ($dateJSON != null)
        {
            // Date in test variable is string stored in JSON format.
            $dateObj = API_Date::fromJSON($dateJSON);
        }
        else
        {
            $dateObj = $dateFactory->makeRandomDate();
        }

        if ($fileName != null)
        {
            $fullFileName = $this->fullFileName($fileName);

            $fileBinary = file_get_contents($fullFileName);
        }
        else
        {
            $fileBinary = null;
            $fileType   = null;
        }

        // -------------------------------------------------------

        $newVideo = API_Video::add($title, $dateObj, $description, $fileBinary, $fileName, $fileType);

        if ($newVideo != null)
        {
            $this->logCreate($newVideo->getID());
        }

        return $newVideo;
    }

    /**
     * Create a video based on 'randomly' selected present values.
     * NOT really random.
     *
     * @return API_Video
     */
    public function makeRandomVideo(): ?API_Video
    {
        $index  = array_rand(self::TEST_VIDEO_FIELDS);
        $values = self::TEST_VIDEO_FIELDS[$index];

        return $this->makeVideo(...$values);
    }

    //======================================================
    // Class constans for various tests and data providers.

    /**
     * @var array<string, array <string|NULL>> TEST_VIDEO_FIELDS
     *      Used by API_VideoTest
     */
    public const TEST_VIDEO_FIELDS = [
        //                      Title   Date    Description     FileName            FileType
        'ALL MP4'         => [ 'Title', null,   'Description',  'Countdown-05.mp4', 'video/mp4' ],
        'Title MP4'       => [ 'Title', null,   null,           'Countdown-10.mp4', 'video/mp4' ],
        'Description MP4' => [ 'Title', null,   'Description',  'Countdown-10.mp4', 'video/mp4' ],
        'ALL WMV'         => [ 'Title', null,   'Description',  'Countdown-05.wmv', 'video/wmv' ],
        'Title WMV'       => [ 'Title', null,   null,           'Countdown-05.wmv', 'video/wmv' ]
    ];

    /**
     * This array contains the SAME basic data as the TEST_VIDEO_FIELDS array above.
     * Only the ORDER of the entries is reversed, and the KEYS are changed. This is
     * to ensure that the add object and update, do not use the same data.
     *
     * It is not possible to update the 'content field' - File.
     *
     * @var array<string, array <string|NULL>> TEST_VIDEO_UPDATE_FIELDS
     *      Used by API_ProfileTest & API_VideoTest
     */
    public const TEST_VIDEO_UPDATE_FIELDS = [ // Date
        'Update 1' => [ 'Title',    null,           null ],
        'Update 2' => [ 'Title',    'Description',  null ],
        'Update 3' => [ 'Title',    'Description',  null ],
        'Update 4' => [ 'Title',    null,           '1984-01-01' ],
        'Update 5' => [ 'Title',    'Description',  '1999-09-09' ],
        'Update 6' => [ 'Title',    'Description',  '2001-02-03' ],
    ];

    /** @var array <array <string>> TEST_VIDEO_COMMENTS
     *      Used by API_ProfileTest & API_VideoTest */
    public const TEST_VIDEO_COMMENTS = [
        [
            'Boring!',
            'How absolutely boring'
        ],
        [
            'Why?',
            'Who wrote this crap.'
        ],
        [
            'Seriously',
            'Whoa. Seriously?'
        ],
        [
            'Not really',
            'Fascinating'
        ],
        [
            'Not news',
            'NYT Best-seller list.'
        ],
        [
            'What a bother',
            'No, just No. Don\'t bother.'
        ],
        [
            'Mind boggling',
            'Behold this most profound video. It shall boggle your mind.'
        ]
    ];

    /**
     * These files EXIST on the Geni Server.
     * @var array <string, array <string|NULL>> VIDEOS_UPLOADED_FROM_URL
     *      Used by provider API_VideoTest::provideUploadedVideo
     */
    public const VIDEOS_UPLOADED_FROM_URL = [
        // Title ID GUID Content Type
        // City Country Attribution Date Created Updated
        'Countdown-05.mp4' => [
            'Going Going Gone',
            'video-143010',
            '6000000222772306833',
            'video/mp4',
            'Jerusalem',
            'Israel',
            'S.A.K',
            '1971-06-20',
            '1765799749',
            '1765801548'
        ],
        'Countdown-10.mp4' => [
            'The Final Countdown',
            'video-142577',
            '6000000222761042928',
            'video/mp4',
            'Tel Aviv',
            'Israel',
            'M.I.K',
            null,
            '1765738557',
            '1765799335'
        ],
        'Countdown-05.wmv' => [
            'Alles Kaputen',
            'video-142576',
            '6000000222761121839',
            'video/wmv',
            'Washington',
            'United States',
            null,
            '1995-04-07',
            '1765738393',
            '1765783996'
        ]
    ];
}
