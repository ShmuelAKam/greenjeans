<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryVideos;

/**
 * Factory for creating test API_Video objects and parameter arrays
 * for DB_VideoMapper unit tests without making remote API calls.
 */
class FactoryMapperVideos extends FactoryVideos
{
    /**
     * @var array <string, array <mixed>>
     */
    public const VIDEO_LIST = [
        //                    Title             Description       Filename        Attribution     Date          Number of
        //                                                                                                      Tags  Comments
        'ALL fields'     => [ 'Home Movie 1999', 'Birthday Party', 'party.mp4',    'Uncle Sam',    null,         3,    2 ],
        'title only'     => [ 'Graduation',      null,             'grad.mov',     'Batman',       null,         2,    0 ],
        'description'    => [ 'Concert',         'Live in NYC',    'concert.mkv',  'X Files',      null,         0,    1 ],
        'no filename'    => [ 'Interview',       'Studio Session', null,           'Dreyfus',      null,         4,    3 ],
        'historic video' => [ 'Moon Landing',    'Apollo 11',      'apollo.mp4',   'NASA',         [20, 7, 1969], 5,    2 ]
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build an API_Video object directly from array parameters.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param array  <strng, int> $date
     * @param int $numTags
     * @param int $numComments
     * @param string $doc_id
     * @param bool $full
     * @return \ShmuelAKam\Greenjeans\Api\API_Video
     */
    public function makeMapVideo(
        string  $title = '',
        ?string $description = null,
        ?string $fileName = null,
        ?string $attribution = null,
        ?array  $date = null,
        int     $numTags = 0,
        int     $numComments = 0,
        ?string $doc_id = null,
        bool    $full = true,
    ): \ShmuelAKam\Greenjeans\Api\API_Video {
        $videoParam = $this->makeParamArray(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            $numTags,
            $numComments,
            doc_id: $doc_id,
            full: $full,
        );

        return API_Video::newFromArray($videoParam, fromDB: false);
    }

    /**
     * Create raw parameter array representation of an API_Video.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param array  <string, int> $date
     * @param int    $numTags
     * @param int    $numComments
     * @param string $doc_id
     * @param bool   $full
     * @return array <string, string|array <string, string>>
     */
    public function makeParamArray(
        string  $title = '',
        ?string $description = null,
        ?string $fileName = null,
        ?string $attribution = null,
        ?array  $date = null,
        int     $numTags = 0,
        int     $numComments = 0,
        ?string $doc_id = null,
        bool    $full = true,
    ): array {
        global $locationFactory;

        static $HALF_HOUR = 1800;

        $videoParam ['id'] = ($doc_id === null || $doc_id === '')
            ? FactoryBase::generatePrefixedId('video')
            : $doc_id;

        $videoParam ['guid']       = FactoryBase::generateGuidFromId($videoParam['id']);
        $videoParam ['created_at'] = (string) (time() - $HALF_HOUR);
        $videoParam ['updated_at'] = (string) time();

        $videoParam ['attribution']  = $attribution;
        $videoParam ['content_type'] = 'video/mp4';
        $videoParam ['url']          = $this->makeURL($videoParam['id']);

        $videoParam ['title'] = $title;

        if ($description !== null)
        {
            $videoParam ['description'] = $description;
        }

        if ($fileName !== null)
        {
            $videoParam ['file_name'] = $fileName;
        }

        if ($full)
        {
            $videoParam ['tags']     = $this->makeTagList($numTags);
            $videoParam ['comments'] = $this->makeCommentList($numComments);

            $videoParam ['date'] = $date;

            $location                = $locationFactory->makeRandomLocation();
            $videoParam ['location'] = $location->makeParamArray();

            if ($fileName !== null && $fileName !== '')
            {
                $videoParam ['sizes'] = $this->makeSizeMapArray($videoParam['id'], $fileName);
            }
        }

        return $videoParam;
    }

    /**
     * @param int $size
     * @return array <string>
     */
    protected function makeTagList(int $size): array
    {
        return FactoryBase::makeIDList('profile', $size);
    }

    /**
     * @param int $size
     * @return array <API_Comment>
     */
    protected function makeCommentList(int $size): array
    {
        $currentComments = FactoryDocuments::TEST_DOC_COMMENTS;
        $videoComments   = [];

        for ($i = 0; $i < $size; $i++)
        {
            $randomKey                   = array_rand($currentComments);
            $commentParam                = $currentComments[$randomKey];
            $commentParam ['created_at'] = (string) time();

            unset($currentComments[$randomKey]);

            $videoComments[] = API_Comment::newFromArray($commentParam);
        }

        return $videoComments;
    }

    protected function makeURL(string $fileId): string
    {
        return 'https://www.geni.com/api/video/' . $fileId;
    }

    /**
     * @param string $fileId
     * @param string $fileName
     * @return array <string, string>
     */
    public function makeSizeMapArray(string $fileId, string $fileName): array
    {
        return [
            'large'  => $this->makeMediaURL($fileId, $fileName, 'large'),
            'medium' => $this->makeMediaURL($fileId, $fileName, 'medium'),
            'origin' => $this->makeMediaURL($fileId, $fileName, 'origin'),
            'print'  => $this->makeMediaURL($fileId, $fileName, 'print'),
            'small'  => $this->makeMediaURL($fileId, $fileName, 'small'),
            'thumb'  => $this->makeMediaURL($fileId, $fileName, 'thumb'),
            'thumb2' => $this->makeMediaURL($fileId, $fileName, 'thumb2'),
        ];
    }
}
