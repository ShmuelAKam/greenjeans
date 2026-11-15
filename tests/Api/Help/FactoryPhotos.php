<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;

class FactoryPhotos extends FactoryBase
{
    // See END of class, for class constants.

    private const SDK_ALBUM_ID = 'album-4144485';

    private const SDK_ALBUM_GUID = '6000000222544428842';

    public function __construct()
    {
        parent::__construct(ShredderPhotos::class);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    // ======================================================

    /**
     * Create photo using parameter values.
     * The basic function.
     *
     * @param string $title
     * @param string $album_id
     * @param string $dateJSON
     * @param string $description
     * @param string $fileName
     * @param string $fileType
     *
     * @return API_Photo
     */
    public function makePhoto(
        string  $title,
        ?string $album_id = null,
        ?string $dateJSON = null,
        ?string $description = null,
        ?string $fileName = null,
        ?string $fileType = null,
    ): ?API_Photo {
        global $dateFactory;

        if ($album_id == null)
        {
            $album_id = $this->getAlbumID();
        }

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

        // In rare situations the add photo FAILS. If so, retry.

        $MAX_TRIES = 3;
        for ($i = 0; $i < $MAX_TRIES; $i++)
        {
            $newPhoto = API_Photo::add($title, $album_id, $dateObj, $description, $fileBinary, $fileName, $fileType);

            if ($newPhoto != null)
            {
                $this->logCreate($newPhoto->getID());
                break;
            }
        }

        return $newPhoto;
    }

    /**
     * Create a photo based on 'randomly' selected present values.
     * NOT really random.
     *
     * @return API_Photo
     */
    public function makeRandomPhoto(?string $album_id = null): ?API_Photo
    {
        if ($this->getTotalCreated() % 2 == 0)
        {
            $source = self::TEST_PHOTO_FIELDS;
        }
        else
        {
            $source = self::TEST_PHOTOS;
        }

        $index  = array_rand($source);
        $values = $source[$index];

        // Title Album, Date Description FileName FileType
        $newPhoto = $this->makePhoto($values[0], $album_id, $values[1], $values[2], $values[3], $values[4]);

        return $newPhoto;
    }

    public function getAlbumID(): string
    {
        return self::SDK_ALBUM_ID;
    }

    public function getAlbumGUID(): string
    {
        return self::SDK_ALBUM_GUID;
    }

    //======================================================
    // Class constans for various tests and data providers.

    /**
     * All calls to ADD must include either file+file_name or text.
     *
     * @var array <string, array <string|NULL>> Used by API_PhotoTest
     */
    public const TEST_PHOTO_FIELDS = [
        //                      Title   Date  Description    FileName          FileType
        'PNG Test'        => [ 'Title', null, 'Description', 'test.png',       'image/png'  ],
        'ALL JPG'         => [ 'Title', null, 'Description', 'File-SDK-1.jpg', 'image/jpeg' ],
        'Title JPG'       => [ 'Title', null, null,          'File-SDK-2.jpg', 'image/jpeg' ],
        'Description JPG' => [ 'Title', null, 'Description', 'File-SDK-3.jpg', 'image/jpeg' ],
        'ALL PNG'         => [ 'Title', null, 'Description', 'File-SDK-4.png', 'image/png'  ],
        'Title PNG'       => [ 'Title', null, null,          'File-SDK-5.png', 'image/png'  ],
        'Description PNG' => [ 'Title', null, 'Description', 'File-SDK-6.png', 'image/png'  ],
        // 'ALL BMP'      => [ 'Title', NULL, 'Description', 'File-SDK-8.bmp', 'image/bmp'  ],
        'ALL GIF'         => [ 'Title', null, 'Description', 'File-SDK-9.gif', 'image/gif'  ]
    ];

    /** @var array <string, array <string>> Used by API_ProfileTest & API_PhotoTest */
    public const TEST_PHOTOS = [
        // Artist Title Date Description FileName
        'Leonardo' => [
            'Mona Lisa',
            '1504',
            'Silly half smile',
            'File-SDK-Mona-Lisa.jpg',
            'image/jpeg'
        ],
        'Donatello' => [
            'Marble David',
            '1409',
            'Totally Stoned',
            'File-SDK-Donatello-David.jpg',
            'image/jpeg'
        ],
        'Raphael' => [
            'Schoool of Athens',
            '1509',
            '"Knowledge"',
            'File-SDK-Raphael-Athens.jpg',
            'image/jpeg'
        ],
        'Michelangelo' => [
            'Creation of Adam',
            '1508',
            '6th Day of Creation',
            'File-SDK-Michelangelo-Creation.jpg',
            'image/jpeg'
        ]
    ];

    /**
     * This array contains the SAME basic data as the TEST_PHOTO_FIELDS array above.
     * Only the ORDER of the entries is reversed, and the KEYS are changed. This is
     * to ensure that the add object and update, do not use the same data.
     *
     * It is not possible to update the 'content field' - File.
     * @var array <string, array <string|NULL>> Used by API_ProfileTest & API_PhotoTest
     */
    public const TEST_PHOTO_UPDATE_FIELDS
        = [
            'Update 1' => [ 'Title', null,          null         ],
            'Update 2' => [ null,    'Description', null         ],
            'Update 3' => [ 'Title', 'Description', null         ],
            'Update 4' => [ 'Title', null,          '1984-01-01' ],
            'Update 5' => [ null,    'Description', '1999-09-09' ],
            'Update 6' => [ 'Title', 'Description', '2001-02-03' ],
        ];

    /** @var array <array <string>> Used by API_ProfileTest & API_PhotoTest */
    public const TEST_PHOTO_COMMENTS = [
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
            'Behold this most profound photo. It shall boggle your mind.'
        ]
    ];

    /**
     * These files EXIST on the Geni Server.
     *
     * @var array <string, array<string|NULL>> Used by provider API_PhotoTest::provideUploadedPhoto
     */
    public const PHOTOS_UPLOADED_FROM_URL = [
        // Title ID GUID Content Type
        // City Country Attribution Date Created Updated
        'File-SDK-1.jpg' => [
            'File-SDK-1.jpg',
            'photo-51190389',
            '6000000222544428843',
            'image/jpeg',
            'Kiryat Ono',
            'Israel',
            'S.A.K',
            '1971-06-20',
            '1764625636',
            '1782028918'
        ],
        'File-SDK-2.jpg' => [
            'File-SDK-2.jpg',
            'photo-51190390',
            '6000000222544428844',
            'image/jpeg',
            'Rehovot',
            'Israel',
            'M.I.K',
            '1972-04-01',
            '1764625637',
            '1764632644'
        ],
        'File-SDK-3.jpg' => [
            'File-SDK-3.jpg',
            'photo-51190391',
            '6000000222544428845',
            'image/jpeg',
            'Yonkers',
            'United States',
            'L.L.K',
            '1995-07-04',
            '1764625637',
            '1764632831'
        ],
        'File-SDK-4.png' => [
            'File-SDK-4.png',
            'photo-51190392',
            '6000000222544428846',
            'image/png',
            'Atlantic City',
            'United States',
            'Uncle Sam',
            '2001-01-01',
            '1764625637',
            '1764634043'
        ],
        'File-SDK-5.png' => [
            'File-SDK-5.png',
            'photo-51190393',
            '6000000222544428847',
            'image/png',
            'Boston',
            'United States',
            '"Bostoner"',
            '1976-08',
            '1764625638',
            '1764634776'
        ],

        'File-SDK-6.png' => [
            'File-SDK-6.png',
            'photo-51190394',
            '6000000222544428848',
            'image/png',
            'Paris',
            'France',
            null,
            '1966-06-06',
            '1764625638',
            '1765697757'
        ],
        'File-SDK-9.gif' => [
            '9. Number 9',
            'photo-52067799',
            '6000000222544428842',
            'image/gif',
            '',
            '',
            'Shimi',
            null,
            '1781726860',
            '1782322502'
        ],

        'File-SDK-Donatello-David.jpg' => [
            'File-SDK-Donatello-David.jpg',
            'photo-51190395',
            '6000000222544428849',
            'image/jpeg',
            'Florence',
            'Italy',
            'Donatello',
            '1409',
            '1764625638',
            '1764630996'
        ],
        'File-SDK-Michelangelo-Creation.jpg' => [
            'File-SDK-Michelangelo-Creation.jpg',
            'photo-51190396',
            '6000000222544428850',
            'image/jpeg',
            'Rome',
            'Italy',
            'Michelangelo',
            '1510',
            '1764625639',
            '1765407946'
        ],
        'File-SDK-Mona-Lisa.jpg' => [
            'File-SDK-Mona-Lisa.jpg',
            'photo-51190397',
            '6000000222544428851',
            'image/jpeg',
            'Florence',
            'Italy',
            'Leonardo',
            '1503',
            '1764625640',
            '1765407934'
        ],
        'File-SDK-Raphael-Athens.jpg' => [
            'File-SDK-Raphael-Athens.jpg',
            'photo-51190400',
            '6000000222544428852',
            'image/jpeg',
            'Athens',
            'Greece',
            'Raphael',
            '-320',
            '1764625655',
            '1764628330'
        ]
    ];

    /**
     * @var array <string, array <string, string|array <string, string|TRUE>>>
     */
    public const PHOTOS_ON_GENI__NOT_USED = [
        'photo-51190389' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'S.A.K',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625636',
            'guid'          => '6000000222544428843',
            'id'            => 'photo-51190389',
            'title'         => 'File-SDK-1.jpg',
            'updated_at'    => '1782028918',
            'url'           => 'https://www.geni.com/api/photo-51190389',
            'date'          => '1971-06-20',
            'location' => [
                'country' => 'Israel',
                'city'    => 'Kiryat Ono'
            ],
            'tags' => [
                'profile-34838408195' => true
            ]
        ],
        'photo-51190390' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'M.I.K',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625637',
            'guid'          => '6000000222544428844',
            'id'            => 'photo-51190390',
            'title'         => 'File-SDK-2.jpg',
            'updated_at'    => '1764632644',
            'url'           => 'https://www.geni.com/api/photo-51190390',
            'date'          => '1972-04-01',
            'location' => [
                'country' => 'Israel',
                'city'    => 'Rehovot'
            ],
        ],
        'photo-51190391' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'L.L.K',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625637',
            'guid'          => '6000000222544428845',
            'id'            => 'photo-51190391',
            'title'         => 'File-SDK-3.jpg',
            'updated_at'    => '1764632831',
            'url'           => 'https://www.geni.com/api/photo-51190391',
            'date'          => '1995-07-04',
            'location' => [
                'country'  => 'United States',
                'city'     => 'Yonkers'
            ],
        ],
        'photo-51190392' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'Uncle Sam',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625637',
            'guid'          => '6000000222544428846',
            'id'            => 'photo-51190392',
            'title'         => 'File-SDK-4.png',
            'updated_at'    => '1764634043',
            'url'           => 'https://www.geni.com/api/photo-51190392',
            'date'          => '2001-01-01',
            'location' => [
                'country'  => 'United States',
                'city'     => 'Atlantic City'
            ],
        ],
        'photo-51190393' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => '\"Bostoner\"',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625638',
            'guid'          => '6000000222544428847',
            'id'            => 'photo-51190393',
            'title'         => 'File-SDK-5.png',
            'updated_at'    => '1764634776',
            'url'           => 'https://www.geni.com/api/photo-51190393',
            'date'          => '1976-08',
            'location' => [
                'country'  => 'United States',
                'city'     => 'Boston'
            ],
        ],
        'photo-51190394' => [
            'album_id'      => '6000000222544428842',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625638',
            'guid'          => '6000000222544428848',
            'id'            => 'photo-51190394',
            'title'         => 'File-SDK-6.png',
            'updated_at'    => '1765697757',
            'url'           => 'https://www.geni.com/api/photo-51190394',
            'date'          => '1966-06-06',
            'location' => [
                'country'  => 'France',
                'city'     => 'Paris'
            ],
        ],
        'photo-51190395' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'Donatello',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625638',
            'guid'          => '6000000222544428849',
            'id'            => 'photo-51190395',
            'title'         => 'File-SDK-Donatello-David.jpg',
            'updated_at'    => '1764630996',
            'url'           => 'https://www.geni.com/api/photo-51190395',
            'date'          => '1409',
            'location'  => [
                'country'  => 'Italy',
                'city'     => 'Florence'
            ],
        ],
        'photo-51190396' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'Michelangelo',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625639',
            'guid'          => '6000000222544428850',
            'id'            => 'photo-51190396',
            'title'         => 'File-SDK-Michelangelo-Creation.jpg',
            'updated_at'    => '1765407946',
            'url'           => 'https://www.geni.com/api/photo-51190396',
            'date'          => '1510',
            'location' => [
                'country' => 'Italy',
                'city'    => 'Rome'
            ],
        ],
        'photo-51190397' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'Leonardo',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625640',
            'guid'          => '6000000222544428851',
            'id'            => 'photo-51190397',
            'title'         => 'File-SDK-Mona-Lisa.jpg',
            'updated_at'    => '1765407934',
            'url'           => 'https://www.geni.com/api/photo-51190397',
            'date'          => '1503',
            'location' => [
                'country' => 'Italy',
                'city'    => 'Florence'
            ],
        ],
        'photo-51190400' => [
            'album_id'      => '6000000222544428842',
            'attribution'   => 'Raphael',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1764625655',
            'guid'          => '6000000222544428852',
            'id'            => 'photo-51190400',
            'title'         => 'File-SDK-Raphael-Athens.jpg',
            'updated_at'    => '1764628330',
            'url'           => 'https://www.geni.com/api/photo-51190400',
            'date'          => '-320',
            'location' => [
                'country' => 'Greece',
                'city'    => 'Athens'
            ],
        ],
        'photo-52067762' => [
            'album_id'      => '6000000222544428842',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1781726826',
            'description'   => 'Description',
            'guid'          => '6000000226145354855',
            'id'            => 'photo-52067762',
            'title'         => 'Title',
            'updated_at'    => '1781726826',
            'url'           => 'https://www.geni.com/api/photo-52067762',
            'date'          => '1999-03-25',
        ],
        'photo-52067799' => [
            'album_id'      => '6000000222544428842',
            'content_type'  => 'image/jpeg',
            'created_at'    => '1781726860',
            'description'   => 'Description',
            'guid'          => '6000000226145155874',
            'id'            => 'photo-52067799',
            'title'         => 'Title',
            'updated_at'    => '1781726861',
            'url'           => 'https://www.geni.com/api/photo-52067799',
            'date'          => '1906-05-12',
        ]
    ];
}
