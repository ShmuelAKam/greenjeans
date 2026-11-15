<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

/**
 * API_Profile - Describes a specific profile.
 * Some of the functions use MY profile-ID, because without a server there is
 * no "real" profile-ID, and because there are fixtures for this ID.
 */
class API_ProfileMocker extends API_BaseMocker
{
    private const PREDEFINED_PROFILES = [
        'profile-53597431' => 'Adam',
        'profile-4095031'  => 'Seth',
        'profile-4094989'  => 'Enosh',
        'profile-4094860'  => 'Cainan',
        'profile-3917277'  => 'Noah',

        'profile-34838408159'  => 'Test Adam',
        'profile-34838408195'  => 'Test Seth',
        'profile-34838408177'  => 'Test Enosh',

        'profile-9288190'  => 'Shmuel',
    ];

    private const GUID_TO_ID = [
        // Real profiles
        'profile-g6000000003538706117'  => 'profile-53597431', // Adam
        'profile-g6000000006375916501'  => 'profile-4095031',  // Seth
        'profile-g6000000000190131271'  => 'profile-4094989',  // Enosh
        'profile-g6000000000190220038'  => 'profile-4094860',  // Cainan
        'profile-g6000000002145556219'  => 'profile-3917277',  // Noah
        // Test profiles
        'profile-g6000000214522348827'  => 'profile-34838408159', // Adam
        'profile-g6000000214522461839'  => 'profile-34838408195', // Seth
        'profile-g6000000214522520831'  => 'profile-34838408177', // Enosh
        // The one and only Real ME.
        'profile-g5098603059080070794'  => 'profile-9288190', // Shmuel
    ];

    private const MODIFIER_TO_FIELD = [
        ''       => 'children',
        'adopt'  => 'adopted_children',
        'foster' => 'foster_children',
    ];

    //=====================================================

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
     * GET profile-1/
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        $profileID = $this->getSubject();

        if ($profileID == 'profile')
        {
            // This is call for get-self profile. So ID doesn't include the profile number.
            // Because this is a Mocker, there's no way to get the "real" profile-ID of "self".
            // Using my own ID, because there are fixtures for it.
            $profileID = 'profile-9288190';
        }
        elseif (substr($profileID, 0, 9) == 'profile-g')
        {
            // This is a GUID.

            if (isset(self::GUID_TO_ID [$profileID]))
            {
                // Profile is one of the presets. Convert it to ID.
                $profileID = self::GUID_TO_ID [$profileID];
            }
            else
            {
                // Search all test-objects for matching GUID.

                $appProfiles = $this->getTestObjectsByType('profile');

                foreach ($appProfiles as $testID => $testProfile)
                {
                    $profileGUID = 'profile-g' . $testProfile ['guid'];

                    if ($profileID == $profileGUID)
                    {
                        // Found object. Use array key as ID.
                        $profileID = $testID;
                        break;
                    }
                }
            }
        }

        // --------------------------------------------------------------
        // Found profile ID, now get object.

        $profile = $this->getTestObject($profileID);

        if ($profile == [])
        {
            $profile = $this->createObjectByID($profileID);

            $this->setTestObject($profile, 'profile');
        }

        // --------------------------------------------------------------
        // Filter profile object to include only specific requested fields.

        if (isset($API_Params ['fields']) && $API_Params ['fields'] != '')
        {
            $profile = $this->getFields($API_Params ['fields'], $profileID, $profile);
        }

        // This is a GET operation (i.e. read-only). Do NOT update the profile object.

        return $profile;
    }

    /**
     * POST profile/add
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add(array $API_Params, array $urlActions = []): array
    {
        $profile = $this->createProfile($API_Params);

        if (API_BaseMocker::isSpecificTest('ShmuelAKam\Greenjeans\Tests\Api\API_ProfileTest', 'testGetCreatedBy_Self'))
        {
            $profile ['created_by'] = $this->getSubject();
        }

        // Persist to static state.
        $this->setSubject($profile ['id']);
        $this->setSubjectObject($profile);

        return $profile;
    }

    /**
     * POST profile-1/add_child
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_child(array $API_Params, array $urlActions = []): array
    {
        $parent    = $this->getSubjectObject();
        $child     = $this->add_profile($API_Params, '');
        $modifier  = $API_Params ['relationship_modifier'] ?? '';
        $fieldName = self::getChildFieldName($modifier);

        /** @var array<string, string|array<string>> $union */
        $union = [];

        if (!empty($parent['unions']))
        {
            // The parrent has unions. Get the first one.

            $unionID = reset($parent ['unions']);

            // Get union object and add partner2.

            $union = $this->getTestObject($unionID);

            // When appending child IDs:
            if (!isset($union[$fieldName]) || !is_array($union[$fieldName]))
            {
                $union[$fieldName] = [];
            }

            $union [$fieldName][] = $child ['id'];
        }
        else
        {
            // NO existing unions.

            $unionID = FactoryBase::generatePrefixedId('union');
            $union   = [
                'id'  => $unionID,
                'url' => 'https://www.geni.com/api/' . $unionID,
            ];

            // When appending child IDs:
            $union [$fieldName] = [];

            if ($modifier != '')
            {
                $union[$fieldName][] = $child['id'];
            }

            $union  ['children'][] = $child ['id'];
            $parent ['unions'][]   = $union ['id'];

            $this->setSubjectObject($parent);
        }

        $parentID = $this->getSubject();
        if (!in_array($parentID, $union ['partners'] ?? []))
        {
            $union ['partners'][] = $parentID;
        }

        $child ['unions'][] = $union ['id'];

        $this->setTestObject($child, 'profile');
        $this->setTestObject($union, 'union');

        return $child;
    }

    /**
     * POST profile-1/add_document
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_document(array $API_Params, array $urlActions = []): array
    {
        $profileID = $this->getSubject();
        $doc       = $this->createDocument($API_Params);

        $this->updateList($profileID, 'documents', $API_Params, 'document', [ $doc ]);

        return $doc;
    }

    /**
     * POST profile-1/add_mugshot
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_mugshot(array $API_Params, array $urlActions = []): array
    {
        $profileID = $this->getSubject();
        $profile   = $this->getSubjectObject();
        $mugshot   = $this->createPhoto($API_Params);
        $mugshotID = $mugshot ['id'];

        // Update profile's list of photos.

        $this->updateList($profileID, 'photos', $API_Params, 'photo', [ $mugshot ]);

        // Cross-update Photo's list of profile tags directly into the shared state.

        $this->updateList($mugshotID, 'tags', $API_Params, 'profile', [ $profile ]);

        return $mugshot;
    }

    /**
     * GET POST profile-1/add_parent
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_parent(array $API_Params, array $urlActions = []): array
    {
        return $this->add_profile($API_Params, 'parents');
    }

    /**
     * GET POST profile-1/add_partner
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_partner(array $API_Params, array $urlActions = []): array
    {
        $partner1 = $this->getSubjectObject();
        $partner2 = $this->add_profile($API_Params, 'partners');

        $marriage = null;
        if (isset($API_Params ['marriage']))
        {
            $marriage = $API_Params ['marriage'];
        }

        $divorce = null;
        if (isset($API_Params ['divorce']))
        {
            $divorce = $API_Params ['divorce'];
        }

        if (!empty($partner1['unions']))
        {
            // There are unions. Get the first one.
            $unionID = reset($partner1['unions']);

            // Get union object and add partner2.

            $union                = $this->getTestObject($unionID);
            $union ['partners'][] = $partner2 ['id'];

            // Add union to partner2. First partner already has it.

            $partner2 ['unions'][] = $unionID;

            $union ['status'] = 'spouse';

            if ($marriage != null)
            {
                $union ['marriage'] = $marriage;
            }

            if ($divorce != null)
            {
                $union ['divorce'] = $divorce;
                $union ['status']  = 'ex_spouse';
            }
        }
        else
        {
            // There are NO unions.

            $union = API_UnionMocker::makeUnionPartner($partner1, $partner2, $marriage, $divorce);

            $partner1 ['unions'][] = $union ['id'];
            $partner2 ['unions'][] = $union ['id'];
        }

        $this->setSubjectObject($partner1);
        $this->setTestObject($partner2, 'profile');
        $this->setTestObject($union, 'union');

        return $partner2;

    }

    /**
     * GET POST profile-1/add_photo
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_photo(array $API_Params, array $urlActions = []): array
    {
        $profileID = $this->getSubject();
        $photo     = $this->createPhoto($API_Params);

        $this->updateList($profileID, 'photos', $API_Params, 'photo', [ $photo ]);

        return $photo;
    }

    /**
     * GET POST profile-1/add_sibling
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_sibling(array $API_Params, array $urlActions = []): array
    {
        return $this->add_profile($API_Params, 'siblings');
    }

    /**
     * GET POST profile-1/add_video
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_video(array $API_Params, array $urlActions = []): array
    {
        $profileID = $this->getSubject();
        $video     = $this->createVideo($API_Params);

        $this->updateList($profileID, 'videos', $API_Params, 'video', [ $video ]);

        return $video;
    }

    /**
     * GET profile-1/compare
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function compare(array $API_Params, array $urlActions = []): array
    {
        static $presetCompares = [
            // Using profile ID.
            'profile-597509' => 'adam.json',
            'profile-587491' => 'seth.json',
            'profile-596113' => 'enosh.json',

            // Using profile GUID.
            'profile-g6000000214522348827' => 'adam.json',
            'profile-g6000000214522520831' => 'seth.json',
            'profile-g6000000214522461839' => 'enosh.json',

            // Test profiles.
            'profile-34838408159' => 'adam.json',
            'profile-34838408177' => 'seth.json',
            'profile-34838408195' => 'enosh.json',
        ];

        $baseID      = $this->getSubject();
        $compareJSON = $presetCompares [$baseID] ?? 'empty.json';

        $compareArray = $this->loadFixtureDirectly('profile', 'compare', $compareJSON);

        return $compareArray;
    }

    /**
     * POST profile-1/delete
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function delete(array $API_Params, array $urlActions = []): array
    {
        // Do NOT actually remove the test object, because on the real server
        // they are not deleted, only flagged as such.

        $deleted = $this->getSubjectObject();

        $deleted ['deleted'] = 1;

        $this->setSubjectObject($deleted);

        //------------------------------------------

        $deleteResults = [ 'result' => 'Deleted' ];

        return $deleteResults;
    }

    /**
     * GET profile-1/documents
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function documents(array $API_Params, array $urlActions = []): array
    {
        // Get list and convert the IDs into objects.
        $documentsList = $this->getList($this->getSubject(), 'documents');
        $documents     = $documentsList->generatePageReply($API_Params);

        return $documents;
    }

    /**
     * POST profile-1/follow
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function follow(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        return $fixtureValues;
    }

    /**
     * POST profile-1/merge
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function merge(array $API_Params, array $urlActions = []): array
    {
        $merge1     = $this->getSubjectObject();
        $merge1ID   = $this->getSubject();
        $merge1GUID = $merge1 ['guid'] ?? 0;

        $merge2ID = $urlActions [0];
        $merge2   = $this->getTestObject($merge2ID);

        $profile          = array_merge($merge1, $merge2);
        $profile ['id']   = $merge1ID;
        $profile ['guid'] = $merge1GUID;

        $this->setSubjectObject($profile);

        $this->removeTestObject($merge2ID);

        $expectedResult = "$merge2ID merged into $merge1ID";
        $mergeResults   = ['result' => $expectedResult ];

        return $mergeResults;
    }

    /**
     * GET profile-1/path_to
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function path_to(array $API_Params, array $urlActions = []): array
    {
        $PREDEFINED_PATHS = [
            // Adam to Cainan
            'profile-53597431' => [ 'to' => '6000000000190220038', 'file' => 'adam-cainan.json'   ],
            // Noah to Jared.
            'profile-3917277'  => [ 'to' => '6000000001048812668', 'file' => 'noah-jared.json'    ],
            // Shmuel A. to Shmuel E.
            'profile-9288190'  => [ 'to' => '4048631877200038987', 'file' => 'shmuel-shmuel.json' ],
        ];

        $startID = $this->getSubject();
        $endGUID = $urlActions [0];

        $pathTo = [];

        if (isset($PREDEFINED_PATHS [$startID]))
        {
            $predefinedEnd = 'profile-g' . $PREDEFINED_PATHS [$startID]['to'];

            if ($endGUID === $predefinedEnd)
            {
                $pathFile = $PREDEFINED_PATHS [$startID]['file'];
                $pathTo   = $this->loadFixtureDirectly('profile', 'path-to', $pathFile);
            }
        }

        return $pathTo;
    }

    /**
     * GET profile-1/photos
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function photos(array $API_Params, array $urlActions = []): array
    {
        $photosList = $this->getList($this->getSubject(), 'photos');
        $photos     = $photosList->generatePageReply($API_Params);

        return $photos;
    }

    /**
     * POST profile-1/unfollow
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function unfollow(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        return $fixtureValues;
    }

    /**
     * POST profile-1/update
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function update(array $API_Params, array $urlActions = []): array
    {
        static $toArray = [
            'nicknames' => true,
        ];

        $updateProfile = $this->getSubjectObject();

        if (isset($API_Params ['fields']))
        {
            $checkAlive = false;
            $fields     = explode(',', $API_Params ['fields']);

            foreach ($fields as $fieldName)
            {
                // For all fields in the parameter list.

                if (isset($API_Params [$fieldName]))
                {
                    // Field is set BOTH in list of fields, and has a value in
                    // the list of parameters.

                    if (isset($toArray [$fieldName]))
                    {
                        if ($API_Params [$fieldName] !== "")
                        {
                            $updateProfile [$fieldName] = explode(',', $API_Params [$fieldName]);
                        }
                        else
                        {
                            $updateProfile [$fieldName] = [];
                        }
                    }
                    elseif ($fieldName == 'is_alive' || $fieldName == 'public')
                    {
                        // Set field.
                        $updateProfile [$fieldName] = $API_Params [$fieldName];

                        // Set flag to check if conflict between 'is_alive' and 'public'
                        // fields. Living profiles can NOT be public. This can only be
                        // checked after loop is complete, because both might be set in
                        // same API call.
                        $checkAlive = true;
                    }
                    else
                    {
                        // Generic field to save.

                        $updateProfile [$fieldName] = $API_Params [$fieldName];
                    }
                }
            } // End loop.

            if ($checkAlive)
            {
                // Check for conflict between 'is_alive' and 'public' fields.
                // Living profiles can NOT be public.
                if ($updateProfile ['is_alive'] == 1
                    && $updateProfile ['public'] == 1)
                {
                    $updateProfile ['public'] = '';
                }
            }

            $this->setSubjectObject($updateProfile);
        }

        return $updateProfile;
    }

    /**
     * GET profile-1/videos
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function videos(array $API_Params, array $urlActions = []): array
    {
        $videosList = $this->getList($this->getSubject(), 'videos');
        $videos     = $videosList->generatePageReply($API_Params);

        return $videos;
    }

    //-----------------------------------------------------
    // The API for these have not yet been developed, so there is nothing to mock.

    /**
     * GET profile-1/ancestors
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function ancestors(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        return $fixtureValues;
    }

    /**
     * GET profile-1/immediate_family
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function immediate_family(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        return $fixtureValues;
    }

    //-----------------------------------------------------
    // TODO implement.

    /**
     * GET profile/search
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function search(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        return $fixtureValues;
    }

    //=====================================================

    /**
     * {@inheritDoc}
     * This overrides the function from API_BaseMocker in order to handle the
     * special case of the subject being the user's own profile, i.e. 'profile'.
     * In this case we hard-code the profile's full ID value.
     * Because this is a Mocker, there's no way to get the "real" profile-ID of "self".
     * Using my own ID, because there are fixtures for it.
     *
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::setSubject()
     */
    protected function setSubject(string $subject): void
    {
        if ($subject === 'profile')
        {
            // See comment above.
            $subject = 'profile-9288190';
        }

        parent::setSubject($subject);
    }

    //=====================================================
    // Helper functions.

    /**
     * @param  string $fields List of fields to get.
     * @param  string $profileID
     * @param  array <string, mixed|array <string, mixed>> $profile
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function getFields(string $fields, string $profileID, array $profile): array
    {
        $fieldsProfile = [];

        $fieldsArray = explode(',', $fields);

        foreach ($fieldsArray as $fieldName)
        {
            if (isset($profile [$fieldName]))
            {
                $fieldsProfile [$fieldName] = $profile [$fieldName];
            }
        }

        // A number of fields like 'about_me' and 'detail_strings' require special handling
        // because they are NOT part of the reply for the regular 'get' call. So the JSON
        // for them must be loaded seperately.

        if (in_array('about_me', $fieldsArray))
        {
            // Check if already received value above.

            if (!isset($fieldsProfile ['about_me']))
            {
                $fixtureName = $profileID . '-about_me.json';
                $aboutme     = $this->loadFixtureDirectly('profile', 'get', $fixtureName);

                $fieldsProfile ['about_me'] = $aboutme ['about_me'] ?? '';
            }
        }

        if (in_array('detail_strings', $fieldsArray))
        {
            $fixtureName = $profileID . '-detail_strings.json';
            $details     = $this->loadFixtureDirectly('profile', 'get', $fixtureName);

            $fieldsProfile ['detail_strings'] = $details ['detail_strings'] ?? '';
        }

        return $fieldsProfile;
    }

    /**
     * Create object directly from API call values.
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  bool $isNew Creating new object, requires creating ID and other fields.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    public function createObject(array $API_Params, bool $isNew): array
    {
        $profile = [];

        //--------------------------------------------------
        // Create request specific fields.

        unset($API_Params ['fields']); // Shouldn't be part of fixture.

        foreach ($API_Params as $fieldName => $fieldValue)
        {
            $profile [$fieldName] = $fieldValue;
        }

        if ($isNew)
        {
            // Create and set generic fileds.

            // Generate deterministic ID and GUID
            // Note: Using $API_Params['name'] as seed for ID stability
            $profile ['id']   = FactoryBase::generatePrefixedId('profile', $API_Params['name'] ?? null);
            $profile ['guid'] = FactoryBase::generateGuidFromId($profile ['id']);

            $created_at = (string) time();

            $profile ['created_at'] = $created_at;
            $profile ['updated_at'] = $created_at;

            $profile ['url'] = '/' . $profile ['id'];

            // Ensure 'name' field has a value.
            if (!isset($profile ['name']) || $profile ['name'] == '')
            {
                $firstName = $profile ['first_name'] ?? '';
                $lastName  = $profile ['last_name'] ?? '';

                $profile ['name'] = $firstName . ' ' . $lastName;

                if ($profile ['name'] == ' ')
                {
                    $profile ['name'] = $profile ['id'];
                }
            }
        }

        if (isset($profile['is_alive']) && ($profile['is_alive'] == 1 || $profile['is_alive']))
        {
            if (isset($profile ['public']))
            {
                // If the profile is set to both alive and public, the server sets it to private.

                $profile ['public'] = false;
            }
        }

        return $profile;
    }

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array <string, mixed|array <string, mixed>>
     */
    public function createObjectByID(string $ID): array
    {
        if (isset(self::PREDEFINED_PROFILES [$ID]))
        {
            $createJSON = "$ID.json";
            $object     = $this->loadFixtureDirectly('profile', 'get', $createJSON);
        }
        else
        {
            // There is no fixture set to use.
            $object = [];

            // Use given ID, and generate GUID from it.

            if (!$this->isGUID($ID))
            {
                $object ['id']   = $ID;
                $object ['guid'] = FactoryBase::generateGuidFromId($object ['id']);
                $object ['name'] = $ID;
            }
            else
            {
                $object ['id']   = FactoryBase::generatePrefixedId('profile', $ID);
                $object ['guid'] = $ID;
                $object ['name'] = $object ['id'];
            }

            $created_at = (string) time();

            $object ['created_at'] = $created_at;
            $object ['updated_at'] = $created_at;

            $object ['url'] = '/' . $object ['id'];
        }

        return $object;
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  string $type Which list to add the profile to.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_profile(array $API_Params, string $type): array
    {
        $profileID = $this->getSubject();
        $profile   = $this->createProfile($API_Params);

        $this->updateList($profileID, $type, $API_Params, 'profile', [ $profile ]);

        return $profile;
    }

    public static function getChildFieldName(string $modifier): string
    {
        return self::MODIFIER_TO_FIELD [$modifier];
    }

    //-----------------------------------------------------

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function createProfile(array $API_Params): array
    {
        $profile = $this->createObject($API_Params, isNew: true);

        $this->setTestObject($profile, 'profile');

        return $profile;
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function createMedia(array $API_Params, string $objectType, string $contentType): array
    {
        $objectID  = FactoryBase::generatePrefixedId($objectType, $API_Params['title'] ?? null);
        $createdAt = time();

        $media = [
            'created_at'   => $createdAt,
            'guid'         => FactoryBase::generateGuidFromId($objectID),
            'id'           => $objectID,
            'page_count'   => 1,
            'title'        => $API_Params ['title'],
            'url'          => 'https://www.geni.com/api/' . $objectID
        ];

        if ($contentType != '')
        {
            $media ['content_type'] = $contentType;
        }

        if (isset($API_Params ['date']))
        {
            if (is_array($API_Params ['date']))
            {
                $media ['date'] = $API_Params ['date'];
            }
            else
            {
                // Input format of $API_Params ['date'] is JSON. Need to convert it
                // to an array. The simplest way to do this is using functions from
                // API_Date.
                $dateObj        = API_Date::fromJSON($API_Params ['date']);
                $media ['date'] = $dateObj->getDate();
            }
        }

        if (isset($API_Params ['description']))
        {
            $media ['description'] = $API_Params ['description'];
        }

        return $media;
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function createDocument(array $API_Params): array
    {
        $document = $this->createMedia($API_Params, 'document', 'text/plain');

        if (isset($API_Params ['labels']))
        {
            $document ['labels'] = $API_Params ['labels'];
        }
        if (isset($API_Params ['source_url']))
        {
            $document ['source_url'] = $API_Params ['source_url'];
        }

        // Create sizes DocumentImageSizeMap
        $document ['sizes'] = [
            'large'  => '',
            'origin' => '',
            'thumb'  => '',
        ];

        $this->setTestObject($document, 'document');

        return $document;
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function createPhoto(array $API_Params): array
    {
        $photo = $this->createMedia($API_Params, 'photo', 'image/jpeg');

        if (isset($API_Params ['album_id']))
        {
            $photo ['album_id'] = $API_Params ['album_id'];
        }

        // Create sizes PhotoImageSizeMap
        $photo ['sizes'] = $this->createSizeMap();

        $this->setTestObject($photo, 'photo');

        return $photo;
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function createVideo(array $API_Params): array
    {
        $video = $this->createMedia($API_Params, 'video', '');

        // Create sizes VideoImageSizeMap
        $video ['sizes'] = $this->createSizeMap();

        $this->setTestObject($video, 'video');

        return $video;
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>>
     */
    protected function createEvent(array $API_Params): array
    {
        static $VALID_FIELDS = [ 'name', 'description', 'date', 'location' ];

        $event = [];
        foreach ($VALID_FIELDS as $fieldName)
        {
            if (isset($API_Params [$fieldName]))
            {
                $event [$fieldName] = $API_Params [$fieldName];
            }
        }

        return $event;
    }

    /**
     * @return array <string, string>
     */
    protected function createSizeMap(): array
    {
        $sizeMap = [
            'large'  => '',
            'medium' => '',
            'origin' => '',
            'print'  => '',
            'small'  => '',
            'thumb'  => '',
            'thumb2' => '',
        ];

        return $sizeMap;
    }
}
