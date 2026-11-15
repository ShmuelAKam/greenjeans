<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;

class FactoryDocuments extends FactoryBase
{
    // See END of class, for class constants.

    public function __construct()
    {
        parent::__construct(ShredderDocuments::class);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    //======================================================

    /**
     * Create document using parameter values.
     * The basic function.
     * @param string   $title
     * @param string   $description
     * @param string   $file
     * @param string   $file_name
     * @param string   $labels
     * @param string   $text
     * @param API_Date $date
     *
     * @return API_Document
     */
    public function makeDocument(
        string   $title,
        string   $description = null,
        string   $file = null,
        string   $file_name = null,
        string   $labels = null,
        string   $text = null,
        API_Date $date = null,
    ): API_Document {
        global $dateFactory;

        if ($date == null)
        {
            $date = $dateFactory->makeRandomDate();
        }

        $newDoc = API_Document::add(
            $title,
            'text/plain',
            $date,
            $description,
            $file,
            $file_name,
            $labels,
            null,
            $text,
        );

        if ($newDoc != null)
        {
            $this->logCreate($newDoc->getID());
        }

        return $newDoc;
    }

    /**
     * Create a document based on "randomly" selected preset values. NOT really random.
     *
     * @return API_Document
     */
    public function makeRandomDocument(): API_Document
    {
        if ($this->getTotalCreated() % 2 == 0)
        {
            $source = self::TEST_DOC_FIELDS;
        }
        else
        {
            $source = self::TEST_DOCS;
        }

        $randomKey = array_rand($source);
        $values    = $source [$randomKey];

        $newDoc = $this->makeDocument(... $values);

        return $newDoc;
    }

    /**
     * Create API_Document for document created previously from existing FILE.
     * @param string $title
     * @param string $contentType
     * @param string $file
     * @param string $file_name
     * @return API_Document
     */
    public function makeDocumentFromFile(
        string $title,
        string $contentType,
        string $file,
        string $file_name,
    ): API_Document {
        global $dateFactory;

        $date = $dateFactory->makeRandomDate();

        // Only the title, file & file_name parameters are used to CREATE the document.
        // The other values in $fileParam are used by the TESTS.

        $newDoc = API_Document::add(
            title: $title,
            content_type: $contentType,
            date: $date,
            file: $file,
            file_name: $file_name,
        );

        $this->logCreate($newDoc->getID());

        return $newDoc;
    }

    /**
     * Create an array of new document objects.
     * @param int $num Number of documents to create.
     * @return array <\ShmuelAKam\Greenjeans\Api\API_Document> created new documents.
     */
    public function makeDocuments(int $num): array
    {
        $documents = [];

        for ($ind = 0; $ind < $num; $ind++)
        {
            $documents[] = $this->makeRandomDocument();
        }

        return $documents;
    }

    //======================================================
    // Class constants for various tests and data providers.

    /**
     * All calls to ADD must include either file+file_name or text.
     * @var array <string, array <string|NULL>> Used by API_DocumentTest
     */
    public const TEST_DOC_FIELDS = [
        'ALL fields'    => [ 'Title', 'Description', 'File_name', 'File',   'Labels', 'Text' ],
        'title'         => [ 'Title', null,          'File_name', 'File',   null,     null   ],
        'description'   => [ 'Title', 'Description', 'File_name', 'File',   null,     null   ],
        'file'          => [ 'Title', null,          'File_name', 'File',   null,     null   ],
        'labels'        => [ 'Title', null,          'File_name', 'File',   'Labels', null   ],

        'title 2'       => [ 'Title', null,          null,        null,     null,     'Text' ],
        'description 2' => [ 'Title', 'Description', null,        null,     null,     'Text' ],
        'labels 2'      => [ 'Title', null,          null,        null,     'Labels', 'Text' ],
        'text'          => [ 'Title', null,          null,        null,     null,     'Text' ],
    ];

    /**
     * @var array <string, array <string|NULL>> Used by API_DocumentTest
     *      This table contains combinations that should FAIL. File & File_name MUST come
     *      together.
     */
    public const TEST_DOC_BAD_FIELDS = [
        'file_name' => [ 'Title', null,          'File_name', null,       null,     null   ],
        'file'      => [ 'Title', null,          null,        'File',     null,     null   ],
    ];

    /**
     * This array contains the SAME basic data as the array above - TEST_DOC_FIELDS
     * Only the ORDER of the entries is reversed, and the KEYS are changed. This is
     * to ensure that the add object and update, do not use the same data.
     */
    /** @var array <string, array <string|NULL|BOOL>> Used by API_ProfileTest & API_DocumentTest */
    public const TEST_DOC_UPDATE_FIELDS = [
        //                                      labels, string/array
        'Update  1' => [ 'Title', null,          false, false ],
        'Update  2' => [ 'Title', 'Description', false, false ],
        'Update  3' => [ 'Title', null,          true,  false ],
        'Update  4' => [ 'Title', 'Description', true,  false ],
        'Update  5' => [ 'Title', 'Description', true,  false ],
        'Update  6' => [ 'Title', 'Description', true,  false ],
        'Update  7' => [ 'Title', null,          false, true  ],
        'Update  8' => [ 'Title', 'Description', false, true  ],
        'Update  9' => [ 'Title', null,          true,  true  ],
        'Update 10' => [ 'Title', 'Description', true,  true  ],
        'Update 11' => [ 'Title', 'Description', true,  true  ],
        'Update 12' => [ 'Title', 'Description', true,  true  ],
    ];

    /** @var array <string, array <string|NULL>> Used by API_ProfileTest & API_DocumentTest */
    public const TEST_DOCS = [
            //                   Title               Description        File  File   Labels
            // Text                                                           name
        'Magna Carta'       => [ 'Magna Carta',     '"Great Charter"',  null, null, 'England,King',
            'John by the grace of God King of England, lord of Ireland and duke of Aquitaine sends greetings to all to whom the present letters come. We have inspected the great charter of the lord Henry, late King of England, our father, concerning the liberties of England in these words:' ],

        'US Constitution'   => [ 'US Constitution', 'Non-equal rights', null, null, 'United States',
            "We the People of the United States, in Order to form a more perfect Union, establish Justice, "
            . "insure domestic Tranquility,..." ],

        'Declaration of'    => [ 'Declaration of Independence',   'From King George', null, null, 'USA, Bla Bla',
        'We hold these truths to be self-evident, that all men are created equal, that they are endowed by their Creator with certain unalienable Rights, that among these are Life, Liberty and the pursuit of Happiness' ],

        'Poisoning Pigeons' => [ 'Poisoning Pigeons in the Park', 'RIP Tom Lehrer',   null, null, 'Satire,Cyanide',
            "Spring is here, a-suh-puh-ring is here\n"
            . "Life is skittles and life is beer\n"
            . "I think the loveliest time of the year is the spring\n"
            . "I do, don't you? (- Course you do)\n"
            . "But there's one thing that makes spring complete for me\n"
            . "And makes every Sunday a treat for me." ],

        'Road Not Taken'    => [ 'The Road Not Taken', 'Robert Frost', null, null, 'Peotry,Irony,Woods',
            "Two roads diverged in a yellow wood,\n"
            . "And sorry I could not travel both\n"
            . "And be one traveler, long I stood\n"
            . "And looked down one as far as I could\n"
            . "To where it bent in the undergrowth;" ],
    ];

    /** @var array <array <string, string>> Used by API_ProfileTest & API_DocumentTest */
    public const TEST_DOC_COMMENTS = [
        [ 'title' => 'Boring!',       'comment' => 'How absolutely boring' ],
        [ 'title' => 'Why?',          'comment' => 'Who wrote this crap.'  ],
        [ 'title' => 'Seriously',     'comment' => 'Whoa. Seriously?'      ],
        [ 'title' => 'Not really',    'comment' => 'Fascinating'           ],
        [ 'title' => 'Not news',      'comment' => 'NYT Best-seller list.' ],
        [ 'title' => 'What a bother', 'comment' => "No, just No. Don't bother." ],
        [ 'title' => 'Mind boggling', 'comment' => 'Behold this most profound document. It shall boggle your mind.' ]
    ];

    // Array of existing files to MAKE documents from, and some of their EXPECTED values.

    // Only the title, file & file_name parameters are used to CREATE the document.
    // The other values are used by the TESTS.

    /** @var array <string, array<int|string>> Used by API_DocumentTest */
    public const DOCS_TO_CREATE_FROM = [
        //                     FileName,        Title, PageCount, ContentType,   Text
        'test-Doc-1.pdf' => [ 'test-Doc-1.pdf', 'First',   4, 'application/pdf', '1234'         ],
        'test-Doc-2.pdf' => [ 'test-Doc-2.pdf', 'Second',  5, 'application/pdf', '12345'        ],
        'test-Doc-3.pdf' => [ 'test-Doc-3.pdf', 'Third',  10, 'application/pdf', 'ABCDEFGHIJ'   ],
        'test-Doc-4.pdf' => [ 'test-Doc-4.pdf', 'Forth ',  5, 'application/pdf', '12345'        ],
    ];

    /** @var array <string, array <string>> Used by API_DocumentTest::provideUploadedDocument */
    public const DOCUMENTS_UPLOADED_FROM_URL = [
        //                    Name              ID                   GUID,
            // City,          Country,  Date
        'test-Doc-1.pdf' => [ 'test-Doc-1.pdf', 'document-44888697', '6000000222479984825',
            'Tel-Aviv',    'Israel', "1-1-1901",
            // Source-URL                                      Attribution
            'http://kam-motion.com/Test/files/test-Doc-1.pdf', ''  ],

        'test-Doc-2.pdf' => [ 'test-Doc-2.pdf', 'document-44888731', '6000000222480027828',
            'Jerusalem',   'Israel', "",
            'http://kam-motion.com/Test/files/test-Doc-2.pdf', ''  ],

        'test-Doc-3.pdf' => [ 'test-Doc-3.pdf', 'document-44888736', '6000000222479901879',
            'Rehovot',     'Israel', "20-6-1971",
            'http://kam-motion.com/Test/files/test-Doc-3.pdf', ''  ],

            'test-Doc-4.pdf' => [ 'test-Doc-4.pdf', 'document-47801298', '6000000226472095823',
            'Tal-Menashe', 'Israel', "27-11-2025",
            'http://kam-motion.com/Test/files/test-Doc-4.pdf', ''  ],
    ];

    /**
     * @var array <string, array <string|array <string>>>
     *      Used by API_API_DocumentTest::testGetProjects.
     * Array of doc-IDs of documents created for this test.
     */
    public const DOCUMENTS_UPLOADED_PROJECTS = [
        //  Name              ID                    Projects[]
        'test-Doc-1.pdf' => [ 'document-44888697',  [ 'project-4506705', 'project-4511662' ]  ],
        'test-Doc-2.pdf' => [ 'document-44888731',  [ 'project-4511662' ]                     ],
        'test-Doc-3.pdf' => [ 'document-44888736',  [ 'project-4506705' ]                     ],
        'test-Doc-4.pdf' => [ 'document-47801298',  []                                        ],
    ];

    /**
     * @var array <string, array <string>>  Used by the API_ProjectMocker & API_DocumentMocker
     *      classes. The same data as DOCUMENTS_UPLOADED_PROJECTS, only with the data and keys
     *      reversed.
     */
    public const PROJECT_DOCUMENTS = [
        'project-4511662' => [ 'document-44888697', 'document-44888731',  ],
        'project-4506705' => [ 'document-44888697', 'document-44888736',  ],
    ];

    /** @var array <int, array <array <string>>> Used by API_API_DocumentTest::testGetProjects_DynamicAdd */
    public const PROJECTS_FOR_DOCUMENTS = [
        1 => [ [ 'project-4506705', 'project-4511662' ] ],
        2 => [ [ 'project-4511662' ]                    ],
        3 => [ [ 'project-4506705' ]                    ],
        4 => [ []                                       ],
    ];
}
