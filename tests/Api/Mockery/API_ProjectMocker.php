<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProjects;

/**
 * API_Project - Describes a specific project.
 */
class API_ProjectMocker extends API_BaseMocker
{
    protected const EXISTING_TEST_PROJECTS = [
        'project-4506705' => [
            'id'          => 'project-4506705',
            'url'         => 'https://www.geni.com/api/project-4506705',
            'name'        => "Shmuel's Test-SDK 1",
            'description' => "This is yet another temporary project, for running tests on. Please IGNORE. Please do NOT follow these projects. It messes up my test code. :-p",
            'created_at'  => '1750946650',
            'updated_at'  => '1767283549'
        ],

        'project-4511662' => [
            'id'          => 'project-4511662',
            'url'         => 'https://www.geni.com/api/project-4511662',
            'name'        => "Shmuel's Test-SDK 2",
            'description' => "This is yet another temporary project, for running tests on. Please IGNORE. Please do NOT follow these projects. It messes up my test code. :-p",
            'created_at'  => '1764291012',
            'updated_at'  => '1767128252'
        ],

        'project-4513134' => [
            'created_at'  => '1768858738',
            'description' => "This is a temporary project, for running tests on. Please IGNORE.\r\n\r\nPlease do NOT follow these projects. It messes up my test code. :-p AND there's nothing to see.",
            'id'          => 'project-4513134',
            'name'        => "Shmuel's Test-API Project",
            'updated_at'  => '1780015873',
            'url'         => 'https://www.geni.com/api/project-4513134',
        ],

        'project-4513135' => [
            'created_at'  => '1768859208',
            'description' => 'This project is EMPTY.',
            'id'          => 'project-4513135',
            'name'        => "Shmuel's Test-API Project 2",
            'updated_at'  => '1768859209',
            'url'         => 'https://www.geni.com/api/project-4513135',
        ],
    ];

    protected const BIBLICAL_PROJ_JSON = 'biblical-project.json';

    protected const BIBLE_PROJECT_FOLLOWERS = [     // 100 followers (fraction of list).
        'profile-9288190',     'profile-34677249384', 'profile-34716553537', 'profile-34670595203',
        'profile-34697767042', 'profile-34724267108', 'profile-34711950529', 'profile-34678120794',
        'profile-34736845972', 'profile-15474770',    'profile-34674826737', 'profile-34775935368',
        'profile-34708236654', 'profile-34647058140', 'profile-26678733',    'profile-34820012555',
        'profile-34680190117', 'profile-34788352678', 'profile-34807018200', 'profile-34821539571',
        'profile-34822125855', 'profile-34679542040', 'profile-34821436818', 'profile-34667477456',
        'profile-34768517874', 'profile-34812754997', 'profile-34797263235', 'profile-34640615008',
        'profile-34758418784', 'profile-34833960444', 'profile-79943469',    'profile-34752016179',
        'profile-15259358',    'profile-34836964425', 'profile-34824077676', 'profile-34837828124',
        'profile-34832343226', 'profile-34763030006', 'profile-34766473965', 'profile-34703602457',
        'profile-34827390923', 'profile-34840058525', 'profile-34726953464', 'profile-34836439627',
        'profile-34661829401', 'profile-34847540689', 'profile-34849173049', 'profile-34641425995',
        'profile-34851355594', 'profile-34826596524', 'profile-34694807415', 'profile-6784339',
        'profile-97386318',    'profile-66385455',    'profile-27588722',    'profile-62455171',
        'profile-99978240',    'profile-102657766',   'profile-31534',       'profile-4250602',
        'profile-4324869',     'profile-35904898',    'profile-8629489',     'profile-7247739',
        'profile-85248228',    'profile-6316852',     'profile-57078415',    'profile-109566201',
        'profile-1758833',     'profile-12150414',    'profile-18665326',    'profile-28045697',
        'profile-44569516',    'profile-51993956',    'profile-88960949',    'profile-2001083',
        'profile-61510009',    'profile-14547449',    'profile-19444179',    'profile-31265445',
        'profile-15970911',    'profile-69367177',    'profile-10819770',    'profile-72392116',
        'profile-111904835',   'profile-6811798',     'profile-38266096',    'profile-100802478',
        'profile-22416006',    'profile-109709604',   'profile-94744838',    'profile-111897642',
        'profile-81452532',    'profile-92813223',    'profile-64969972',    'profile-35068810',
        'profile-10823075',    'profile-71684074',    'profile-77675574',    'profile-661649'
    ];
    protected const BIBLE_PROJECT_PROFILES = [     // 100 profiles. (fraction of list).
        'profile-9097864',     'profile-9097877',     'profile-9098006',     'profile-9496446',
        'profile-9098002',     'profile-9097984',     'profile-9098007',     'profile-9097988',
        'profile-9098021',     'profile-9098026',     'profile-9098078',     'profile-9098067',
        'profile-4094221',     'profile-4094194',     'profile-4094333',     'profile-12257413',
        'profile-12257421',    'profile-9102546',     'profile-10799430',    'profile-9098528',
        'profile-4094322',     'profile-4128660',     'profile-16791832',    'profile-10799421',
        'profile-4128710',     'profile-4128729',     'profile-4128644',     'profile-4128774',
        'profile-4128794',     'profile-4128823',     'profile-4128843',     'profile-4128903',
        'profile-4128925',     'profile-4128949',     'profile-3917271',     'profile-60941057',
        'profile-11658194',    'profile-10778381',    'profile-10778709',    'profile-3917277',
        'profile-3917285',     'profile-3917293',     'profile-3917300',     'profile-3917310',
        'profile-4129043',     'profile-4094860',     'profile-4094989',     'profile-4095031',
        'profile-4095543',     'profile-53597431',    'profile-71640569',    'profile-16964921',
        'profile-12325480',    'profile-25667291',    'profile-25667270',    'profile-21346575',
        'profile-25667239',    'profile-20460265',    'profile-34844635361', 'profile-37968734',
        'profile-34673839864', 'profile-34673839860', 'profile-34673839855', 'profile-34673839822',
        'profile-12260988',    'profile-28735202',    'profile-119037274',   'profile-34838100834',
        'profile-37971581',    'profile-37971582',    'profile-37971510',    'profile-37971511',
        'profile-94352616',    'profile-34838088242', 'profile-63935151',    'profile-12258480',
        'profile-37971541',    'profile-37971542',    'profile-50340793',    'profile-50340794',
        'profile-37971716',    'profile-50340797',    'profile-50340798',    'profile-50340799',
        'profile-50340800',    'profile-37971721',    'profile-37971707',    'profile-50340788',
        'profile-50340789',    'profile-37971710',    'profile-50340791',    'profile-37971713',
        'profile-63712915',    'profile-109089911',   'profile-37970555',    'profile-109089885',
        'profile-109089820',   'profile-34837684858', 'profile-37968981',    'profile-37968980'
    ];

    protected const TEST_BIBLE_PROJECT_PROFILES = [
        'profile-34838408195', 'profile-34838408159', 'profile-34838408177',
    ];

    protected const NO_PROJECT_COLLABORATORS = [];
    protected const NO_PROJECT_FOLLOWERS     = [];
    protected const NO_PROJECT_PROFILES      = [];

    //======================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    //=====================================================
    // API Calls.

    /**
     * GET project-1/ - Get project information from Server.
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        if (API_BaseMocker::isSpecificTest('ShmuelAKam\Greenjeans\Tests\Api\API_ProjectTest', 'test__get_NotExist')
            || API_BaseMocker::isSpecificTest('ShmuelAKam\Greenjeans\Tests\Api\API_ProjectTest', 'testVerifyProjectExists_NOT'))
        {
            return [];
        }

        //------------------------------

        $existingSubjectObj = $this->getSubjectObject();

        if (isset($existingSubjectObj['id']) && $existingSubjectObj['id'] == $this->getSubject())
        {
            // Subject object already defined.

            $fixtureValues = $existingSubjectObj;
        }
        else
        {
            $fixtureValues = $this->createObjectByID($this->getSubject());

            if ($fixtureValues == [])
            {
                $fixtureValues = $this->createObject($API_Params);
            }
        }

        if (!isset($fixtureValues ['updated_at']))
        {
            $fixtureValues ['updated_at'] = time();
        }

        $this->setSubjectObject($fixtureValues);

        //------------------------------
        // Handle special cases of get request - get only specific field(s).

        if (isset($API_Params ['fields']))
        {
            $fieldsReply = [];

            $fieldList = explode(',', $API_Params ['fields']);

            foreach ($fieldList as $field)
            {
                $fieldsReply [$field] = $fixtureValues [$field];
            }

            $this->setSubjectObject($fixtureValues);

            return $fieldsReply;
        }

        return $fixtureValues;
    }

    /**
     * POST project/add_documents - Add Documents to Project.
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_documents(array $API_Params, array $urlActions = []): array
    {
        $documentList = [];
        $projectID    = $this->getSubject();

        if (isset($API_Params ['ids']))
        {
            $documentIDs = explode(',', $API_Params ['ids']);

            // Check if project has other documents pre-listed, if yes, add them to
            // list of documents to have in this list.

            if (isset(FactoryDocuments::PROJECT_DOCUMENTS [$projectID]))
            {
                $mergedIDs   = array_merge($documentIDs, FactoryDocuments::PROJECT_DOCUMENTS [$projectID]);
                $documentIDs = array_unique($mergedIDs);
            }

            $list = $this->updateList($projectID, 'documents', $API_Params, 'document', $documentIDs);

            // Mirror data by adding project to lists of document.

            $docParams = [ 'fields' => 'id' ];
            foreach ($documentIDs as $docID)
            {
                $this->updateList($docID, 'projects', $docParams, 'project', [ $projectID ]);
            }

            $documentList = $list->generatePageReply($API_Params);
        }

        return $documentList;
    }

    /**
     * POST project/add_profiles - Add profiles to Project.
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_profiles(array $API_Params, array $urlActions = []): array
    {
        $projectID = $this->getSubject();

        if (isset($API_Params ['profile_ids']))
        {
            $profileIDs = explode(',', $API_Params ['profile_ids']);

            $this->updateList($projectID, 'profiles', $API_Params, 'profile', $profileIDs);
        }

        $list     = $this->getList($projectID, 'profiles');
        $profiles = $list->generatePageReply($API_Params);

        return $profiles;
    }

    /**
     * GET project-1/collaborators
     * Return list of Collaborators of the Project (or just their IDs).
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function collaborators(array $API_Params, array $urlActions = []): array
    {
        /**
         * Look-up table that translates projectIDs, directly to JSON files for
         * this API call.
         * @var array <string, string> $collaboratorFiles
         */
        static $collaboratorFiles = [
            FactoryProjects::PROJECT_IDS ['Bible_Tree']    => 'bible-page-1.json',
            FactoryProjects::PROJECT_IDS ['Test_Project']  => 'me-only.json',
            FactoryProjects::PROJECT_IDS ['Empty_Project'] => 'me-only.json',
            FactoryProjects::PROJECT_IDS ['TEST_Biblical'] => 'me-only.json',
        ];

        $projectID = $this->getSubject();

        $collabJSON = [];
        if (isset($collaboratorFiles [$projectID]))
        {
            $collabJSON = $collaboratorFiles [$projectID];

            if ($projectID === FactoryProjects::PROJECT_IDS ['Bible_Tree'])
            {
                if (isset($API_Params ['page']) && $API_Params ['page'] == 2)
                {
                    $collabJSON = 'bible-page-2.json';
                }
            }
        }

        $collaborators = $this->loadFixtureDirectly('project', 'collaborators', $collabJSON);

        return $collaborators;
    }

    /**
     * GET project-1/followers
     * Return list of Profiles (of users) that FOLLOW the project (or just their IDs).
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function followers(array $API_Params, array $urlActions = []): array
    {
        static $PROJECT_FILES = [
            'project-38'       => '',                   // Bible_Tree - Special case. See below'
            'project-4513134'  => 'me-only.json',       // Test_Project
            'project-4513135'  => 'no-followers.json',  // Empty_Project
            'project-10000001' => 'empty.json',         // NON_Exist project.
            'project-4506705'  => 'me-only.json',       // TEST_Biblical
        ];

        $projectID     = $this->getSubject();
        $followersJSON = $PROJECT_FILES [$projectID] ?? 'empty.json';

        if ($projectID === FactoryProjects::PROJECT_IDS ['Bible_Tree'])
        {
            $pageNum       = $API_Params ['page'] ?? '1';
            $followersJSON = "project-38-page-{$pageNum}.json";
        }

        $followers = $this->loadFixtureDirectly('project', 'followers', $followersJSON);

        return $followers;
    }

    /**
     * GET project-1/profiles
     * Return list of Profiles in the Project (or just their IDs).
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function profiles(array $API_Params, array $urlActions = []): array
    {
        $projectID    = $this->getSubject();
        $profilesJSON = 'me-only.json';

        if ($projectID === FactoryProjects::PROJECT_IDS ['Bible_Tree'])
        {
            $pageNum = $API_Params ['page'] ?? '1';

            $profilesJSON = "project-38-page-{$pageNum}.json";
        }
        elseif ($projectID === FactoryProjects::PROJECT_IDS ['TEST_Biblical'])
        {
            $profilesJSON = 'project-4506705-page-1.json';
        }

        $profiles = $this->loadFixtureDirectly('project', 'profiles', $profilesJSON);

        return $profiles;
    }

    //=====================================================
    // Helper functions.

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * Projects can't be created at runtime, so this does nothing.
     * @return array{}
     */
    public function createObject(array $API_Params): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * Create a project 'object' from the static array, or for the Biblical peoject.
     * This function must be public because it is used by API_DocumentMocker::projects.
     * @param string $ID
     * @return array <string, mixed|array <string, mixed>>
     */
    public function createObjectByID(string $ID): array
    {
        $proj = [];

        if (isset(self::EXISTING_TEST_PROJECTS [$ID]))
        {
            $proj = self::EXISTING_TEST_PROJECTS [$ID];
        }
        elseif ($ID == 'project-38')
        {
            // Load Biblical project from file.

            $proj = $this->loadFixture(self::BIBLICAL_PROJ_JSON, false);
        }
        else
        {
            // There is NO API to create projects.
            // So this is not relevant for projects, because all projects
            // are preset. We have a specific list of projects to which we
            // can add documents.
        }

        return $proj;
    }

    /**
     * Get values of specified project.
     * @param string $ID
     * @return array <string, string>
     */
    public static function getExistingProject(string $ID): array
    {
        return self::EXISTING_TEST_PROJECTS [$ID] ?? [];
    }
}
