<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotos;

/**
 * Factory for creating test API_Photo objects and parameter arrays
 * for DB_PhotoMapper unit tests without making remote API calls.
 */
class FactoryMapperPhotos extends FactoryPhotos
{
    public const PHOTO_LIST = [
        //                    Title            Description      Filename        Attribution     Album      Date     Number of
        //                                                                                                          Tags  Comments
        'ALL fields'     => [ 'Family Picnic', 'Summer 2026',   'picnic.jpg',   'Uncle Sam', 'album-3210', null,     3,    2 ],
        'title only'     => [ 'Vacation',      null,            'vacation.png', 'Batman',    'Imagine',    null,     2,    0 ],
        'description'    => [ 'Sunset',        'At the beach',  'sunset.jpg',   'X Files',   'P!nk',       null,     0,    1 ],
        'no filename'    => [ 'Portrait',      'Studio shot',   null,           'Dreyfus',   'The Wall',   null,     4,    3 ],
        'historic photo' => [ 'Declaration',   'Signing event', 'history.jpg',  'Thomas J',  '', [17, 9, 1787], 5, 2 ]
    ];

    //------------------------------------------------------

    public function __construct()
    {
        parent::__construct();
    }

    //======================================================

    /**
     * Build an API_Photo object directly from array parameters.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param array<int> $date
     * @param int $numTags
     * @param int $numComments
     * @param string $albumId
     * @param bool $full
     * @return API_Photo
     */
    public function makeMapPhoto(
        string  $title = '',
        ?string $description = null,
        ?string $fileName = null,
        ?string $attribution = null,
        ?array  $date = null,
        int     $numTags = 0,
        int     $numComments = 0,
        string  $albumId = 'album-default-123',
        bool    $full = true,
    ): API_Photo {
        $photoParam = $this->makeParamArray(
            $title,
            $description,
            $fileName,
            $attribution,
            $date,
            $numTags,
            $numComments,
            $albumId,
            full: $full,
        );

        return API_Photo::newFromArray($photoParam, fromDB: false);
    }

    /**
     * Generate an array of API_Photo objects belonging to a specific album.
     *
     * @return array<API_Photo>
     */
    public function makePhotoList(string $albumId, int $count = 3): array
    {
        $list = [];

        for ($i = 0; $i < $count; $i++)
        {
            $photo = $this->makeMapPhoto(
                title: 'Album Photo ' . ($i + 1),
                description: 'Photo description ' . ($i + 1),
                fileName: 'photo_' . ($i + 1) . '.jpg',
                numTags: 2,
                numComments: 1,
                albumId: $albumId,
                full: true,
            );

            $list[] = $photo;
        }

        return $list;
    }

    /**
     * Create raw parameter array representation of an API_Photo.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $attribution
     * @param array <int> $date
     * @param int $numTags
     * @param int $numComments
     * @param string $albumId
     * @param string $doc_id
     * @param bool $full
     * @return array <string, string|array <string|int|API_Comment>|array <string,string>>
     */
    public function makeParamArray(
        string  $title = '',
        ?string $description = null,
        ?string $fileName = null,
        ?string $attribution = null,
        ?array  $date = null,
        int     $numTags = 0,
        int     $numComments = 0,
        string  $albumId = '',
        ?string $doc_id = null,
        bool    $full = true,
    ): array {
        global $locationFactory;

        static $HALF_HOUR = 1800;

        $photoParam ['id'] = ($doc_id === null) ? FactoryBase::generatePrefixedId('photo') : $doc_id;

        $photoParam ['guid']       = FactoryBase::generateGuidFromId($photoParam['id']);
        $photoParam ['album_id']   = $albumId;
        $photoParam ['created_at'] = (string) (time() - $HALF_HOUR);
        $photoParam ['updated_at'] = (string) time();

        $photoParam ['attribution']  = $attribution;
        $photoParam ['content_type'] = 'image/jpeg';
        $photoParam ['url']          = $this->makeURL($photoParam['id']);

        $photoParam ['title'] = $title;

        if ($description !== null)
        {
            $photoParam ['description'] = $description;
        }

        if ($fileName !== null)
        {
            $photoParam ['file_name'] = $fileName;
        }

        if ($full)
        {
            $photoParam ['tags']     = $this->makeTagList($numTags);
            $photoParam ['comments'] = $this->makeCommentList($numComments);

            if ($date != null)
            {
                $photoParam ['date'] = $date;
            }

            $location                = $locationFactory->makeRandomLocation();
            $photoParam ['location'] = $location->makeParamArray();

            if ($fileName !== null && $fileName !== '')
            {
                $photoParam ['sizes'] = $this->makeSizeMapArray($photoParam['id'], $fileName);
            }
        }

        return $photoParam;
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
        $photoComments   = [];

        for ($i = 0; $i < $size; $i++)
        {
            $randomKey                   = array_rand($currentComments);
            $commentParam                = $currentComments[$randomKey];
            $commentParam ['created_at'] = (string) time();

            unset($currentComments[$randomKey]);

            $photoComments[] = API_Comment::newFromArray($commentParam);
        }

        return $photoComments;
    }

    protected function makeURL(string $fileId): string
    {
        return 'https://www.geni.com/api/photo/' . $fileId;
    }

    /**
     * Generate size map array containing all image variation URLs for API_PhotoImageSizeMap.
     *
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
