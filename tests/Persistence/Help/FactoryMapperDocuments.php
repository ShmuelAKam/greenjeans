<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryLocations;

/**
 * DO NOT call 'make' functions in the base class . They call the Geni Server which
 * which we don't want. Write make functions that use the API_Document::newFromArray
 * function.
 * @author shmuel
 */
class FactoryMapperDocuments extends FactoryDocuments
{
    public const DOC_LIST = [
        //                    Title    Description    Filename         Text    Attribution  Date      Number of
        //                                                                                    Labels Projects Tags Comments

        'ALL fields'    => [ 'Title', 'Description', 'File_name.pdf', 'Text', 'Uncle Sam',  null,  5,  3,  2,  1  ],
        'title'         => [ 'Title',  null,         'File_name.doc',  '',    'Bow to Zog', null,  4,  0,  2,  0  ],
        'description'   => [ 'Title', 'Description', 'File_name.pdf',  '',    'Batman',     null,  3,  2,  0,  2  ],
        'file'          => [ 'Title',  null,         'File_name.doc',  '',    'X Files',    null,  2,  0,  3,  2  ],
        'labels'        => [ 'Title',  null,         'File_name.pdf',  '',    'Dreyfus',    null,  1,  1,  4,  0  ],

        'title 2'       => [ 'Title',  null,          null,            'Text', null,        null,  0,  1,  4,  1  ],
        'description 2' => [ 'Title', 'Description',  null,            'Text', null,        null,  1,  0,  1,  3  ],
        'labels 2'      => [ 'Title',  null,          null,            'Text', null,        null,  2,  0,  0,  0  ],
        'text'          => [ 'Title',  null,          null,            'Text', null,        null,  3,  5,  0,  4  ],

        //                      Title               Description                        Filename
        //   Text
        //   Attribution        Date []             Number of
        //                                      Labels Projects Tags
        'Magna Carta'       => [ 'Magna Carta',     '"Great Charter"',                'Magna.txt',
            'John by the grace of God King of England, lord of Ireland and duke of Aquitaine sends greetings to all to whom the present letters come. We have inspected the great charter of the lord Henry, late King of England, our father, concerning the liberties of England in these words:',
            'King John',        [ 15, 6, 1215 ],  3,  12,  4,  3 ],

        'US Constitution'   => [ 'US Constitution', 'Non-equal rights',               'US Const.pdf',
            "We the People of the United States, in Order to form a more perfect Union, establish Justice, "
            . "insure domestic Tranquility,...",
            'James Madison',    [ 17, 9, 1787 ],  3,  1,  55,  3 ],

        'Declaration of'    => [ 'Declaration of Independence',   'From King George', 'War.txt',
            'We hold these truths to be self-evident, that all men are created equal, that they are endowed by their Creator with certain unalienable Rights, that among these are Life, Liberty and the pursuit of Happiness',
            'Thomas Jefferson', [ 17, 9, 1787 ],  1,  3,  50,  0 ],

        'Poisoning Pigeons' => [ 'Poisoning Pigeons in the Park', 'RIP Tom Lehrer',   'Fun.txt',
            "Spring is here, a-suh-puh-ring is here\n"
            . "Life is skittles and life is beer\n"
            . "I think the loveliest time of the year is the spring\n"
            . "I do, don't you? (- Course you do)\n"
            . "But there's one thing that makes spring complete for me\n"
            . "And makes every Sunday a treat for me.",
            'Tom Lehrer',       [ 1, 1, 1959 ],  0,  0,  0,  5 ],

        'Road Not Taken'    => [ 'The Road Not Taken', 'Robert Frost',                'Profound.txt',
            "Two roads diverged in a yellow wood,\n"
            . "And sorry I could not travel both\n"
            . "And be one traveler, long I stood\n"
            . "And looked down one as far as I could\n"
            . "To where it bent in the undergrowth;",
            'Robert Frost',     [ 1, 8, 1915 ],  2,  0,  2,  2 ],
    ];

    //------------------------------------------------------

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This function does any final set-up operations on the variables defined
     * below. The function is called in the bootstrap code, in GlobalSetup.php.
     */
    public static function setup(): void {}

    //======================================================

    /**
     * Create document using parameter values.
     * The basic function.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $text
     * @param string $attribution
     * @param array <int> $date
     * @param int    $numLabels
     * @param int    $numProjects
     * @param int    $numTags
     * @return API_Document
     */
    public function makeMapDocument(
        string  $title = '',
        ?string $description = null,
        ?string $fileName = null,
        string  $text = '',
        ?string $attribution = null,
        ?array  $date = null,
        int     $numLabels = 0,
        int     $numProjects = 0,
        int     $numTags = 0,
        int     $numComments = 0,
        bool    $full = true,
    ): API_Document {
        $docParam = $this->makeParamArray(
            $title,
            $description,
            $fileName,
            $text,
            $attribution,
            $date,
            $numLabels,
            $numProjects,
            $numTags,
            $numComments,
            full: $full,
        );

        $newDoc = API_Document::newFromArray($docParam, false);

        return $newDoc;
    }

    /**
     * Create a document based on "randomly" selected preset values. NOT really random.
     *
     * @return API_Document
     */
    public function makeMapRandomDocument(): API_Document
    {
        $randomKey = array_rand(self::DOC_LIST);
        $docParam  = self::DOC_LIST [$randomKey];

        //                    Title    Description    Filename         Text    Attribution  Date      Number of
        //                                                                                    Labels Projects Tags

        // public function makeMapDocument (string $title = '', ?string $description = NULL, ?string $fileName = NULL,
        //     string $text = '',  ?string $attribution = NULL, ?array  $date     = NULL,
        //     int    $numLabels = 0,  int $numProjects = 0, int $numTags = 0,
        //     bool   $full  = TRUE): API_Document

        $newDoc = $this->makeMapDocument(
            $docParam[0],
            $docParam[1],
            $docParam[2],
            $docParam[3],
            $docParam[4],
            $docParam[5],
            $docParam[6],
            $docParam[7],
            $docParam[8],
            full: true,
        );
        return $newDoc;
    }

    /**
     * Create API_Document from parameters of actual files. There is
     * NO contact with Geni Server, so document is not really created.
     * @param string $title
     * @param string $contentType
     * @param string $file
     * @param string $file_name
     * @return API_Document
     */
    public function makeMapDocumentFromFile(
        string $title,
        string $contentType,
        string $file,
        string $file_name,
    ): API_Document {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $date       = $dateFactory->makeRandomDate();
        $fileBase64 = base64_encode($file);

        $docParam ['title']        = $title;
        $docParam ['content_type'] = $contentType;
        $docParam ['date']         = $date->makeParamArray();
        $docParam ['file']         = $fileBase64;
        $docParam ['file_name']    = $file_name;

        $newDoc = API_Document::newFromArray($docParam, fromDB: false);

        return $newDoc;
    }

    //======================================================

    /**
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $text
     * @param string $attribution
     * @param array <int> $date
     * @param int $numLabels
     * @param int $numProjects
     * @param int $numTags
     * @param int $numComments
     * @param bool $full
     * @return array <string, mixed>
     */
    public function makeParamArray(
        string  $title = '',
        ?string $description = null,
        ?string $fileName = null,
        string  $text = '',
        ?string $attribution = null,
        ?array  $date = null,
        int     $numLabels = 0,
        int     $numProjects = 0,
        int     $numTags = 0,
        int     $numComments = 0,
        bool    $full = true,
    ): array {
        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);
        /** @var FactoryLocations $locationFactory */
        $locationFactory = FactoryBase::getFactory(API_Location::class);

        static $HALF_HOUR = 60 * 30; // In seconds.

        // Basics.

        $docParam ['id']         = FactoryBase::generatePrefixedId('document');
        $docParam ['guid']       = FactoryBase::generateGuidFromId($docParam ['id']);
        $docParam ['created_at'] = time() - $HALF_HOUR;
        $docParam ['updated_at'] = time();

        $docParam ['attribution']  = $attribution;
        $docParam ['content_type'] = 'text/plain';
        $docParam ['url']          = $this->makeURL($docParam ['id']);

        //-------------------------------
        // Document specific.

        $docParam ['title'] = $title;

        if ($description != null)
        {
            $docParam ['description'] = $description;
        }

        if ($fileName != null)
        {
            $docParam ['file_name'] = $fileName;
        }

        $docParam ['text']       = $text;
        $docParam ['page_count'] = 1;

        //-------------------------------
        // @return array <string, string|array <string|mixed>>

        if ($full)
        {
            // Lists.

            $docParam ['labels']   = $this->makeLabelList($numLabels);
            $docParam ['projects'] = $this->makeProjectList($numProjects);
            $docParam ['tags']     = $this->makeTagList($numTags);
            $docParam ['comments'] = $this->makeCommentList($numComments);

            //-------------------------------
            // Sub-Objects.

            if ($date == null)
            {
                $dateObj = $dateFactory->makeRandomDate();
                $date    = $dateObj->makeParamArray();
            }

            $docParam ['date'] = $date;

            $location              = $locationFactory->makeRandomLocation();
            $docParam ['location'] = $location->makeParamArray();

            if (isset($fileName) && $fileName != '')
            {
                $docParam ['sizes'] = $this->makeSizeMapArray($docParam ['id'], $fileName);
            }
        }

        return $docParam;
    }

    /**
     * @param int $size
     * @return array <string>
     */
    protected function makeProjectList(int $size): array
    {
        return FactoryBase::makeIDList('project', $size);
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
     * @return array <string>
     */
    protected function makeLabelList(int $size): array
    {
        static $LABELS = [
            // Positive critiques
            'Excellent phrasing',   'Highly persuasive',    'Crystal clear',
            'Well structured',      'Spot on',              'Very engaging',
            // Neutral / Structural critiques
            'Needs proofreading',   'Too dense',            'Highly repetitive',
            'Lacks depth',          'Slightly disorganized', 'Awkward phrasing',
            // Negative / Critical critiques
            'Too vague',            'Inaccurate data',      'Overly wordy',
            'Inconsistent tone',    'Weak argument',        'Unclear focus',
        ];

        $currentLabels = $LABELS;

        $docLabels = [];

        for ($i = 0; $i < $size; $i++)
        {
            // Select random label, and remove it to avoid duplicates.

            $randomKey = array_rand($currentLabels);
            $label     = $currentLabels [$randomKey];
            unset($currentLabels [$randomKey]);

            $docLabels [] = $label;
        }

        return $docLabels;
    }

    /**
     * Generate array of comment objects.
     * @param int $size
     * @return array <API_Comment>
     */
    protected function makeCommentList(int $size): array
    {
        $currentComments = self::TEST_DOC_COMMENTS;

        $docComments = [];

        for ($i = 0; $i < $size; $i++)
        {
            // Select random Comment, and remove it to avoid duplicates.

            $randomKey                   = array_rand($currentComments);
            $commentParam                = $currentComments [$randomKey];
            $commentParam ['created_at'] = (string) time();

            unset($currentComments [$randomKey]);

            $docComments [] = API_Comment::newFromArray($commentParam);
        }

        return $docComments;
    }

    protected function makeURL(string $fileId): string
    {
        return 'https://www.geni.com/api/' . $fileId;
    }

    /**
     * @param string $fileId
     * @param string $fileName
     * @return array <string, string>
     */
    public function makeSizeMapArray(string $fileId, string $fileName): array
    {
        $params = [
            'large'  => $this->makeMediaURL($fileId, $fileName, 'large'),
            'origin' => $this->makeMediaURL($fileId, $fileName, 'origin'),
            'thumb'  => $this->makeMediaURL($fileId, $fileName, 'thumb'),
        ];

        return $params;
    }
}
