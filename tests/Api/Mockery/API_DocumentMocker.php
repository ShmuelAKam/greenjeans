<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

class API_DocumentMocker extends API_MediaMocker
{
    /**
     * These documents already exist on the Server, and are assigned to the
     * projects listed. This table is also used to load the documents from
     * fixture files (the file names are the same as the ID).
     *
     * @var array<string, string>
     */
    private const DOCUMENTS_ON_SERVER = [
        // ID                   Projects[]
        'document-44888697' => 'test-Doc-1.pdf',
        'document-44888731' => 'test-Doc-2.pdf',
        'document-44888736' => 'test-Doc-3.pdf',
        'document-47801298' => 'test-Doc-4.pdf',
    ];

    /**
     * The projects associated with the pre-existing documents.
     * @var array <string, array <string>>
     */
    private const DOCUMENT_PROJECTS = [
        // ID                   Projects[]
        'document-44888697' => [ 'project-4506705', 'project-4511662' ],
        'document-44888731' => [ 'project-4511662' ],
        'document-44888736' => [ 'project-4506705' ],
        'document-47801298' => [ ],
    ];

    /**
     * Maps local file names to their corresponding document ID key on the server.
     *
     * @var array<string, string>
     */
    private const DOCS_TO_CREATE_FROM = [
        'test-Doc-1.pdf' => 'document-44888697',
        'test-Doc-2.pdf' => 'document-44888731',
        'test-Doc-3.pdf' => 'document-44888736',
        'test-Doc-4.pdf' => 'document-47801298',
    ];

    //======================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setObjectType('document');
    }

    //======================================================
    // API calls.

    /**
     * POST /document/add
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add(array $API_Params, array $urlActions = []): array
    {
        $fileName = '';
        if (isset($API_Params['file_name']))
        {
            $fileName = basename($API_Params['file_name']);
        }

        if (isset(self::DOCS_TO_CREATE_FROM[$fileName]))
        {
            $docID = self::DOCS_TO_CREATE_FROM[$fileName];

            // The file being added was created from one of the files
            // created to upload from. Load the equivalent fixture.

            $docFixture = $this->loadFixture($fileName, false);
            $this->setFixture($docFixture);

            $docFixture = $this->createObject(['name' => $fileName]);

            if (isset($API_Params['source_url']))
            {
                $docFixture['source_url'] = $GLOBALS ['API_TEST_SERVER'] . '/Test/files/' . self::DOCUMENTS_ON_SERVER[$docID];
            }

            $this->setTestObject($docFixture, 'document');

            return $docFixture;
        }

        $PARAM_FIELDS = [
            'title',
            'date',
            'description',
            'labels',
            'content_type',
            'source_url',
        ];

        return parent::_add($API_Params, $PARAM_FIELDS);
    }

    /**
     * POST document-123/update
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function update(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = parent::update($API_Params, $urlActions);

        if (isset($API_Params['labels']))
        {
            if (is_array($API_Params['labels']))
            {
                $API_Labels = implode(',', $API_Params['labels']);
            }
            else
            {
                $API_Labels = $API_Params['labels'];
            }

            if (isset($fixtureValues['labels']))
            {
                // Document already has labels. Combine them.
                $API_Labels = $fixtureValues['labels'] . ',' . $API_Labels;
            }

            $fixtureValues['labels'] = $API_Labels;
        }

        // Save updated object
        $this->setSubjectObject($fixtureValues);

        return $fixtureValues;
    }

    /**
     * POST /document-123/projects
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function projects(array $API_Params, array $urlActions = []): array
    {
        $docID = $this->getSubject();

        $projectIDs  = [];
        $projectList = null;

        if (isset(self::DOCUMENT_PROJECTS[$docID]))
        {
            $projectIDs  = self::DOCUMENT_PROJECTS [$docID];
            $projectList = $this->updateList($docID, 'projects', $API_Params, 'project', $projectIDs);
        }
        else
        {
            $projectList = $this->getList($docID, 'projects');

            if ($projectList !== null)
            {
                $projectIDs = $projectList->getListObjects();
            }
        }

        $projMocker = API_BaseMocker::getMockerByClass(API_ProfileMocker::class);

        // Make sure that there are test-objects for all relevant projects.
        foreach ($projectIDs as $projID)
        {
            $projObject = $this->getTestObject($projID);

            if ($projObject === [])
            {
                $projObject = $projMocker->createObjectByID($projID);
                $this->setTestObject($projObject, 'project');
            }
        }

        $projectReply = [];
        if ($projectList !== null)
        {
            $projectList->convertIDs();
            $projectReply = $projectList->generatePageReply($API_Params);
        }

        return $projectReply;
    }

    //------------------------------------------------------

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_MediaMocker::getFileList()
     * @return array<string, string>
     */
    protected function getFileList(): array
    {
        return self::DOCUMENTS_ON_SERVER;
    }
}
