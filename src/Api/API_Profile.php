<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListObjects;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Gender;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_Name;
use ShmuelAKam\Greenjeans\Api\Data\API_Path;
use ShmuelAKam\Greenjeans\Api\Data\API_PhoneNumber;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;

use function ShmuelAKam\Greenjeans\Api\Core\boolToString;
use function ShmuelAKam\Greenjeans\Api\Core\print_rr;

/**
 * API_Profile - central object of the API. Represents a profile on the site.
 *
 * o  There are TWO ways to create API_Profile objects:
 *       o  For NEW profiles, call the constructor, which creates a blank
 *          object. Set the fields you want, and call one of the add-person
 *          functions.
 *       o  For existing profiles, call the static 'get' function.
 * o  This class has two additional functions to get profiles:
 *       o  getBrief - returns a minimalist object, with only the most basic
 *          fields set. Useful when creating large numbers of profile objects,
 *          or when only very specific data is needed. The regular get-data
 *          functions will get the specific fields if not loaded.
 *          @see API_Profile::BRIEF_FIELDS
 *       o  getProfileSelf - Returns the object for the USER that is calling
 *          the API. It has a 'isBrief' parameter, to get a brief object.
 * o  The many SET functions DO NOT update the server object. That is done
 *    by calling the UPDATE function, which then updates all of the
 *    relevant fields that were set.
 * o  There are two additional update functions for specific fields:
 *       o  updateAboutMe       - updates the default 'About Me' (i.e. en-US).
 *          (There is also a setAboutMe function).
 *       o  updateDetailStrings - updates the 'About Me' in a different language.
 * o  There are two static helper functions to can be used to convert a ID to the
 *    equivalent GUID (or GUID to ID): convertID for a single ID, and
 *    convertListIDs for converting an array of IDs.
 * o  This object is implemented internally as an ARRAY ('values'), because ALL
 *    of the data fields are optional.
 * o  All of the API get calls set fields=id, in addition to the requested field(s).
 *    This makes it easier to verify that the Server sent a reply with values.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=profile&version=1 API_Profile}
 */
class API_Profile extends API_Base
{
    /**
     * @var bool
     * When set the object will only include the very basic information:
     * Is set when the user calls one of the basic get functions: get & getBrief.
     *      @see API_Profile::BRIEF_FIELDS
     *
     * If the object isBrief:
     * 1. the user can STILL call additional get methods, and the information
     *    will be added to the object.
     * 2. When making API calls that return all fields, only the fields
     *    relevant to the call will be updated.
     */
    private bool   $isBrief;

    /**
     * $values - ALMOST all the data members for this object are implemented as
     * an array, because most of them are OPTIONAL. The key values are the same
     * as those used for the API field names. The exceptions are the lists of
     * objects - projects, documents, photos and videos, who have specific data
     * members.
     * @var array<string, mixed>
     *
     * 'about_me'             - Profile's about me section (cf. detail_strings) (must be requested)
     * 'baptism'              - Profile's baptism event info
     * 'big_tree'             - TRUE if the profile is attached to the big tree
     * 'birth'                - Profile's birth event info
     * 'block_exists'         - Indicates whether the profile is blocked
     * 'burial'               - Profile's burial event info
     * 'cause_of_death'       - Profile's death cause
     * 'claimed'              - TRUE if the profile is claimed by a user
     * 'created_at'           - Timestamp of when the profile was created
     * 'created_by'           - URL (or id) of the profile who added this profile to the tree
     * 'creator'              - URL (or id) of the user who added this profile to the tree
     * 'curator'              - Profile's curator's url (or id)
     * 'current_residence'    - Profile's current address
     * 'death'                - Profile's death event info
     * 'detail_strings'       - Nested maps of locales to details fields (eg. about me) to values
     *                          (must be requested)
     * 'display_name'         - Profile's display name
     * 'documents_updated_at' - Timestamp of the last document updated/added to the profile.
     *                          Will not be returned if no documents exist.
     * 'email'                - Profile's email address
     * 'events'               - Array of API_Event. Events associated with this profile (must be requested)
     * 'first_name'           - Profile's first name
     * 'gender'               - Profile's gender
     * 'get_email'            - Indicates whether the profile can receive email
     * 'guid'                 - Profile's globally unique identifier
     * 'id'                   - Profile's node id
     * 'is_alive'             - TRUE if the profile is living
     * 'language'             - Profile's language
     * 'last_name'            - Profile's last name
     * 'locked'               - TRUE if the profile has been locked down by a curator
     * 'maiden_name'          - Profile's maiden name
     * 'managers'             - Array of strings. URLs (or ids) of profile(s) currently managing this profile
     * 'master_profile'       - Indicates whether the profile is a master profile
     * 'merge_note'           - XXX or string. Note explaining the profile's merge status
     * 'merge_pending'        - Indicates whether the profile has a pending merge
     * 'merged_into'          - URL (or id) of the profile this profile is currently merged into
     * 'middle_name'          - Profile's middle name
     * 'mugshot_urls'         - All sizes of the profile's main photo
     * 'name'                 - Profile's name as it appears on the site to the current user
     * 'names'                - Nested maps of locales to API_Name.
     *                          Example: {"de": {"last_name": "Smith"}}
     * 'nicknames'            - Array of strings. Also known as (AKA). Returned as an array, but
     *                          can be set as a comma delimited list.
     * 'occupation'           - Profile's occupation
     * 'phone_numbers'        - Array of PhoneNumbers. Profile's phone numbers
     * 'photos_updated_at'    - Timestamp of the last photo updated/added to the profile. Will not
     *                          be returned if no photos exist.
     * 'premium_start_date'   - Date when the profile became a pro user
     * 'profile_url'          - URL to access profile in a browser
     * 'public'               - TRUE if the profile is public
     * 'relationship'         - Profile's relationship to the current user (if any)
     * 'requested_merges'     - Array of strings URLs (or ids) of the profile(s) requested to be merged
     *                          into this one
     * 'suffix'               - Profile's suffix
     * 'unions'               - Array of strings - URLs to unions
     * 'updated_at'           - Timestamp of when the profile was last updated
     * 'url'                  - URL to access profile through the API
     * 'videos_updated_at'    - Timestamp of the last video updated/added to the profile. Will not
     *                          be returned if no videos exist.
     */
    private array  $values = [];

    /**
     * @var array<string, bool> List of fields that have been set, but not yet updated or added
     *      to the server.
     */
    private array  $setFields = [];

    /**
     * Array of fields that make up the basic object. After the inital get (or getBrief)
     * call, all of these fields will have a value. When getBrief is called ONLY these
     * fields will have values until additional API are called to get or set values.
     * @var array<string, bool>
     */
    private const BRIEF_FIELDS = [
        'id'       => true,
        'is_alive' => true,
        'name'     => true,
        'public'   => true
    ];

    /**
     * White list of fields that can be processed into the $values array. Other
     * fields can be sent, but must be processed individually. The array is
     * structured with the values as keys for maximal access speed, instead of
     * searching the entire array for a value.
     * @var array<string, bool>
     */
    private const VALID_API_KEYS = [
        'about_me'             => true,
        'baptism'              => true,
        'big_tree'             => true,
        'birth'                => true,
        'block_exists'         => true,
        'burial'               => true,
        'cause_of_death'       => true,
        'claimed'              => true,
        'created_at'           => true,
        'created_by'           => true,
        'creator'              => true,
        'curator'              => true,
        'current_residence'    => true,
        'death'                => true,
        'detail_strings'       => true,
        'display_name'         => true,
        'documents_updated_at' => true,
        'email'                => true,
        'events'               => true,
        'first_name'           => true,
        'gender'               => true,
        'get_email'            => true,
        'guid'                 => true,
        'id'                   => true,
        'is_alive'             => true,
        'language'             => true,
        'last_name'            => true,
        'locked'               => true,
        'maiden_name'          => true,
        'managers'             => true,
        'master_profile'       => true,
        'merge_note'           => true,
        'merge_pending'        => true,
        'merged_into'          => true,
        'middle_name'          => true,
        'mugshot_urls'         => true,
        'name'                 => true,
        'names'                => true,
//      'nicknames'            => TRUE, Is actually a valid field but requires unique
//                                      processing. So removing it and handling it by
//                                      itself makes more sense.
        'occupation'           => true,
        'phone_numbers'        => true,
        'photos_updated_at'    => true,
        'premium_start_date'   => true,
        'profile_url'          => true,
        'project_ids'          => true,
        'public'               => true,
        'relationship'         => true,
        'requested_merges'     => true,
        'suffix'               => true,
        'title'                => true,
        'unions'               => true,
        'updated_at'           => true,
        'url'                  => true,
        'videos_updated_at'    => true
    ];

    // -----------------------------------------------------
    // The four lists of objects, that are NOT saved in the $values array.

    /**
     * Array contain project-IDs of project this profile belongs to.
     * Empty array if none.
     * @var array <string>
     */
    private array $projects = [];

    /**
     * List of documents this profile is tagged in.
     * Values gotten using {@link self::getDocuments}. Until then
     * list is NULL. Will contain {@link API_Document} objects.
     * @var \ShmuelAKam\Greenjeans\Api\Core\API_ListObjects
     */
    private ?API_ListObjects $documents;

    /**
     * List of documents this profile is tagged in.
     * Values gotten using {@link self::getPhotos}. Until then
     * list is NULL. Will contain {@link API_Photo} objects.
     * @var \ShmuelAKam\Greenjeans\Api\Core\API_ListObjects
     */
    private ?API_ListObjects $photos;

    /**
     * List of documents this profile is tagged in.
     * Values gotten using {@link self::getVideos}. Until then
     * list is NULL. Will contain {@link API_Video} objects.
     * @var \ShmuelAKam\Greenjeans\Api\Core\API_ListObjects
     */
    private ?API_ListObjects $videos;

    //=====================================================

    /**
     * Constructor - creates a BLANK object. This can NOT be used for anything.
     * When reading from the site, must be set using the {@link self::get}
     * or {@link self::getBrief} functions (also {@link self::getProfileSelf})
     * Example:
     *      $profile = new API_Profile ();
     *      $profile->get (profile-ID);
     *
     * When creating a NEW profile, must use the set functions to give it values before
     * updating it to the site.
     * Example:
     *      $profile = new API_Profile ();
     *      $profile->setName    ('First Name', 'Middle Name', ... );
     *      $profile->setAboutMe ('some text');
     */
    public function __construct()
    {
        parent::__construct();

        // This is the default state. Will be changed when 'get' is called.
        $this->setIsBrief(true);

        $this->values    = [];
        $this->setFields = self::BRIEF_FIELDS;
        $this->projects  = [];

        $this->documents = null;
        $this->photos    = null;
        $this->videos    = null;
    }

    //-----------------------------------------------------
    // Functions to handle results from API calls, and prepare parameters for POST calls.

    /**
     * Update object from return-values from API.
     * This function is NOT meant to be used by the user. It is only public
     * because of some quirk of the API_Union class, which needs it for the
     * {@link API_Union::addChild and @link API_Union::addPartner functions.}
     * @param array<string, mixed|array<string, mixed>> $arrayValues
     */
    public function updateFromArray(array $arrayValues, bool $fromDB = false): void
    {
        // List of fields that are objects that need to be handled
        // differently. See updateFieldObject function.

        static $complexFields = [
            'baptism'            => [ 'class' => API_Event::class,       'isArray' => false ],
            'birth'              => [ 'class' => API_Event::class,       'isArray' => false ],
            'burial'             => [ 'class' => API_Event::class,       'isArray' => false ],
            'current_residence'  => [ 'class' => API_Location::class,    'isArray' => false ],
            'death'              => [ 'class' => API_Event::class,       'isArray' => false ],
            'events'             => [ 'class' => API_Event::class,       'isArray' => true  ],
            'location'           => [ 'class' => API_Location::class,    'isArray' => false ],
            'mugshot_urls'       => [ 'class' => API_PhotoImageSizeMap::class, 'isArray' => false ],
            'names'              => [ 'class' => API_Name::class,        'isArray' => true  ],
            'phone_numbers'      => [ 'class' => API_PhoneNumber::class, 'isArray' => true  ],
        ];

        //--------------------------------------------------
        // These fields must ALWAYS have a value. Default set if none found in $arrayValues.
        // Values are unset to prevent them being processed again in loop.

        $this->values ['id']       = $arrayValues ['id'] ?? '';
        $this->values ['name']     = $arrayValues ['name'] ?? '';
        $this->values ['is_alive'] = $arrayValues ['is_alive'] ?? false;
        $this->values ['public']   = $arrayValues ['public'] ?? false;

        // Unset so that the loop does NOT process them again.

        unset($arrayValues ['id']);
        unset($arrayValues ['name']);
        unset($arrayValues ['is_alive']);
        unset($arrayValues ['public']);

        //--------------------------------------------------
        // Set all fields in the $values array.

        $isBrief = $this->isBrief();

        foreach ($arrayValues as $fieldName => $updateVal)
        {
            if (isset(self::VALID_API_KEYS [$fieldName]))
            {
                if (!$isBrief || isset($this->setFields [$fieldName]))
                {
                    if (!isset($complexFields [$fieldName]))
                    {
                        $this->updateFieldSimple($fieldName, $updateVal);
                    }
                    else
                    {
                        $type = $complexFields [$fieldName];

                        $this->updateFieldObject($fieldName, $updateVal, $type['class'], $type['isArray']);
                    }
                }
            }
        }

        //--------------------------------------------------
        // Exceptions to the generic handling above.

        if (isset($arrayValues ['nicknames']))
        {
            // 'nicknames' is an exception in that it is neither a simple field or an object.

            $this->values ['nicknames'] = self::parseArrayOrCommaList($arrayValues ['nicknames']);
        }

        if (isset($arrayValues ['detail_strings']))
        {
            // Value was already saved in loop above.

            $this->setLoaded('detail_strings');
        }

        if (isset($arrayValues ['project_ids']))
        {
            // This value is kept separately from the $values array.
            // The loops above will have set it there. Save and remove.

            $this->projects = $arrayValues ['project_ids'];
            $this->setLoaded('projects');

            // Remove from $values array.
            unset($this->values ['project_ids']);
        }

        //--------------------------------------------------

        if ($fromDB)
        {
            // Process collections. These are not valid API fields.

            if (isset($arrayValues ['documents']))
            {
                $this->documents = API_ListObjects::newFromDB($arrayValues ['documents'], API_Document::class, 'documents');
                $this->setLoaded('documents');
            }

            if (isset($arrayValues ['photos']))
            {
                $this->photos = API_ListObjects::newFromDB($arrayValues ['photos'], API_Photo::class, 'photos');
                $this->setLoaded('photos');
            }

            if (isset($arrayValues ['videos']))
            {
                $this->videos = API_ListObjects::newFromDB($arrayValues ['videos'], API_Video::class, 'videos');
                $this->setLoaded('videos');
            }
        }
    }

    /**
     * Create new API_Profile object from array of values returned from API.
     * @internal Public only because of unit-tests.
     * @param array<string, mixed|array<string, mixed>> $arrayValues
     * @param bool   $brief
     * @return API_Profile
     */
    public static function newFromArray(array $arrayValues, bool $fromDB = false, bool $brief = false, ): API_Profile
    {
        $profile = new API_Profile();

        $profile->setIsBrief($brief);

        $profile->updateFromArray($arrayValues, $fromDB);

        return $profile;
    }

    /**
     * Create array from field values. For POST calls to API.
     * @param  bool $allFields  Copy ALL fields from object into array of param
     *                          values, or only those that were SET,  using the
     *                          various set functions.
     * @return array<string, mixed>
     */
    public function makeParamArray(bool $allFields, bool $forDB = false): array
    {
        // TODO verify which fields are NOT sent as param.

        $paramArray = [];

        foreach ($this->values as $fieldName => $updateVal)
        {
            if ($allFields || isset($this->setFields [$fieldName]))
            {
                if (isset(self::VALID_API_KEYS [$fieldName]))
                {
                    if (is_array($this->values[$fieldName]))
                    {
                        // Check if array of objects, otherwise copy as is.
                        $firstKey = array_key_first($this->values[$fieldName]);
                        $firstVal = $this->values[$fieldName][$firstKey];

                        if (is_object($firstVal))
                        {
                            foreach ($this->values[$fieldName] as $fieldKey => $fieldValue)
                            {
                                $paramArray [$fieldName][$fieldKey] = $fieldValue->makeParamArray();
                            }
                        }
                        else
                        {
                            $paramArray [$fieldName] = $updateVal;
                        }
                    }
                    elseif (is_object($this->values[$fieldName]))
                    {
                        // Object fields.
                        if ($this->values[$fieldName] instanceof API_Gender)
                        {
                            // Gender enum.
                            $paramArray [$fieldName] = $this->values[$fieldName]->value;
                        }
                        else
                        {
                            $paramArray [$fieldName] = $updateVal->makeParamArray();
                        }
                    }
                    else
                    {
                        // All other fields.
                        $paramArray [$fieldName] = $updateVal;
                    }
                }
            }
        }

        if (isset($this->values['nicknames']))
        {
            // nicknames field. Convert to comma separated list.

            $paramArray ['nicknames'] = implode(',', $this->values['nicknames']);
        }

        if (!$forDB)
        {
            // These three array are sets of key values. Combine as relevant and
            // create a string of the field names from the results.

            if ($allFields)
            {
                $allFieldNames = self::BRIEF_FIELDS + $this->values;
            }
            else
            {
                $allFieldNames = self::BRIEF_FIELDS + $this->setFields;
            }

            // Add list of fields to show in results.
            $paramArray ['fields'] = implode(',', array_keys($allFieldNames));
        }
        else
        {
            // Making parameters for DB save. Include collections.
            // Collections are NOT sent to the API Server.

            if ($this->isLoaded('projects'))
            {
                $paramArray ['projects'] = $this->projects;
            }

            // Add collections for DB save.

            if ($this->isLoaded('documents'))
            {
                $paramArray ['documents'] = $this->documents->getListIDs();
            }

            if ($this->isLoaded('photos'))
            {
                $paramArray ['photos'] = $this->photos->getListIDs();
            }

            if ($this->isLoaded('videos'))
            {
                $paramArray ['videos'] = $this->videos->getListIDs();
            }
        }

        return $paramArray;
    }

    //-----------------------------------------------------

    /**
     * Update an object TO the $values array, from the Server.
     * Is called by {@link self::updateFromArray}.
     * @param string $fieldName    API name of field.
     * @param mixed  $objectArray  Array of object values (or array of object arrays),
     *                             as received from Server. Can also be an actual object
     *                             or an array of objects.
     * @param string $className    Name of class to create from values.
     * @param bool   $isArray      Set when $objectArray contaians array of objects arrays.
     */
    private function updateFieldObject(
        string $fieldName,
        mixed  $objectArray,
        string $className,
        bool   $isArray = false,
    ): void {
        if (is_array($objectArray))
        {
            // There are 3 options:
            // 1. This is a 1D array of values to make a  single object from.
            // 2. This is a 2D array of values to make an array of objects from.
            // 3. This is an array of objects.

            if (!$isArray)
            {
                // Option 1. The most common. 1D array of values to make a  single object from.

                $this->values [$fieldName] = $className::newFromArray($objectArray);
            }
            else
            {
                // Options 2 or 3.

                $first = reset($objectArray);

                if (!is_a($first, $className))
                {
                    // Option 2: 2D array of object values.

                    foreach ($objectArray as $key => $objectValues)
                    {
                        $this->values [$fieldName][$key] = $className::newFromArray($objectValues);
                    }
                }
                else
                {
                    // Option 3. An array of objects. Save without processing.

                    foreach ($objectArray as $key => $object)
                    {
                        $this->values [$fieldName][$key] = $object;
                    }
                }
            }
        }
        else
        {
            // Option 4 - NOT an array. Is a single object.

            $this->values [$fieldName] = $objectArray;
        }
    }

    /**
     * Update field of basic type TO the $values array, from the Server.
     * Is called by {@link self::updateFromArray}.
     * @param string $fieldName    API name of field.
     * @param mixed  $updateVal    Filed value.
     */
    private function updateFieldSimple(string $fieldName, mixed $updateVal): void
    {
        static $urlFields = [
            'created_by' => true,
            'creator'    => true,
            'curator'    => true,
            'managers'   => true,
            'requested_merges' => true,
            'unions'     => true
        ];

        // $complexFields are processed in updateFromArray

        if (!isset($urlFields [$fieldName]))
        {
            $this->values[$fieldName] = $updateVal;
        }
        else
        {
            // Value is URL.

            if (!is_array($updateVal))
            {
                $this->values[$fieldName] = $this->getIDFromURL($updateVal);
            }
            else
            {
                $strippedIDs = [];
                foreach ($updateVal as $URL)
                {
                    $strippedIDs [] = $this->getIDFromURL($URL);
                }

                $this->values [$fieldName] = $strippedIDs;
            }
        }
    }

    //=====================================================
    // Helper functions to get, set and update field values.
    // The set functions only update the OBJECT. They do NOT update the Server.
    // The actual update must be done by calling update or the various add
    // functions. To do this, the set functions update the list of fields
    // that were set in setFields.

    /**
     * Get string field.
     * If the field is NOT loaded, first call the API to get it.
     * @param  string $fieldName  API name of field to get.
     * @param  bool   $force      Force an API call, to refresh the value.
     * @param  bool   $isURL      Process string as URL when set.
     * @return string|array<string> Field value (possibly an array), or '' if field not defined.
     */
    private function getFieldString(string $fieldName, bool $force = false, bool $isURL = false): string|array
    {
        if (!isset($this->values[$fieldName]) || $force)
        {
            // Call API to get field if not set in object (or if user wants to
            // force a get (e.g. after they think it's been updated since
            // previous get)).

            $API_Wrap = API_Wrapper::getInstance();

            $ID       = $this->getID();
            $FieldURL = "/$ID?fields=$fieldName";

            // -------------------------------
            $fieldArray = $API_Wrap->callAPI($FieldURL);
            // -------------------------------

            if (isset($fieldArray[$fieldName]))
            {
                if (!$isURL)
                {
                    $this->values [$fieldName] = $fieldArray [$fieldName];
                }
                else
                {
                    // Field contains a URL to strip.

                    $this->values [$fieldName] = $this->getIDFromURL($fieldArray [$fieldName]);
                }
            }
            else
            {
                // API call did NOT return field.
                $this->values [$fieldName] = '';
            }
        }

        return $this->values [$fieldName];
    }

    /**
     * Get field that contains ARRAY of strings.
     * If the field is NOT loaded, first call the API to get it.
     * @param  string $fieldName  API name of field to get.
     * @param  bool   $force      Force an API call, to refresh the value.
     * @param  bool   $isURL      Process string as URL when set.
     * @return array <string> Field value or [] if field not defined.
     */
    private function getFieldStringArray(string $fieldName, bool $force = false, bool $isURL = false): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        if (!$this->isLoaded($fieldName) || $force)
        {
            // Call API to get field if not set in object (or if user wants to
            // force a get (e.g. after they think it's been updated since
            // previous get)).

            $ID       = $this->getID();
            $fieldURL = "/$ID?fields=$fieldName";

            // -------------------------------
            $fieldArray = $API_Wrap->callAPI($fieldURL);
            // -------------------------------

            if (isset($fieldArray [$fieldName]))
            {
                if (!$isURL)
                {
                    $this->values [$fieldName] = $fieldArray [$fieldName];
                }
                else
                {
                    // field contains URL that needs stripping.

                    $strippedIDs = [];
                    foreach ($fieldArray [$fieldName] as $URL)
                    {
                        $strippedIDs [] = $this->getIDFromURL($URL);
                    }

                    $this->values [$fieldName] = $strippedIDs;
                }

                $this->setLoaded($fieldName);
            }
            else
            {
                // API call did NOT return field.
                $this->values [$fieldName] = [];
            }
        }

        return $this->values [$fieldName];
    }

    /**
     * Get bool field.
     * If the field is NOT loaded, first call the API to get it.
     * If API returns NULL, then return FALSE.
     * @param  string $fieldName  API name of field to get.
     * @return bool Field value or FALSE if field not defined.
     */
    private function getFieldBool(string $fieldName, bool $force = false)
    {
        $API_Wrap = API_Wrapper::getInstance();

        if (!array_key_exists($fieldName, $this->values) || $force)
        {
            // Call API to get field if not set in object (or if user wants to
            // force a get (e.g. after they think it's been updated since
            // previous get)).

            $ID       = $this->getID();
            $fieldURL = "/$ID?fields=$fieldName";

            // -------------------------------
            $fieldArray = $API_Wrap->callAPI($fieldURL);
            // -------------------------------

            if ($fieldArray != null && array_key_exists($fieldName, $fieldArray))
            {
                $this->values [$fieldName] = $fieldArray [$fieldName];
            }
            else
            {
                // API call did NOT return field.
                $this->values [$fieldName] = false;
            }
        }

        return $this->values [$fieldName];
    }

    /**
     * Get object field (or array of objects).
     * @param  string $fieldName  API name of field to get.
     * @param  string $className  Name of class to create from field.
     * @param  bool   $force      Force an API call, to refresh the value.
     * @param  bool   $isArray    When set, value is array of objects.
     * @return mixed  Field object(s), or NULL if not defined.
     */
    private function getFieldObj(string $fieldName, string $className, bool $force = false, bool $isArray = false): mixed
    {
        $API_Wrap = API_Wrapper::getInstance();

        if (!array_key_exists($fieldName, $this->values) || $force)
        {
            // Call API to get field if not set in object (or if user wants to
            // force a get (e.g. after they think it's been updated since
            // previous get)).

            $ID       = $this->getID();
            $fieldURL = "/$ID?fields=$fieldName";

            // -------------------------------
            $fieldArray = $API_Wrap->callAPI($fieldURL);
            // -------------------------------

            if ($fieldArray == null || !array_key_exists($fieldName, $fieldArray))
            {
                // API call did NOT return field.
                if (!$isArray)
                {
                    return null;
                }
                else
                {
                    return [];
                }
            }

            // API always returns an array (or NULL). Check if this is an array of arrays.

            if (!is_array($fieldArray [$fieldName][array_key_first($fieldArray [$fieldName])]))
            {
                if (!$isArray)
                {
                    $this->values [$fieldName] = $className::newFromArray($fieldArray [$fieldName]);
                }
                else
                {
                    $this->values [$fieldName] = [ $className::newFromArray($fieldArray [$fieldName]) ];
                }
            }
            else
            {
                $objArray = [];
                foreach ($fieldArray [$fieldName] as $objKey => $objValue)
                {
                    $objArray [$objKey] = $className::newFromArray($objValue);
                }

                $this->values [$fieldName] = $objArray;
            }
        }

        return $this->values [$fieldName];
    }

    //-----------------------------------------------------
    // The following functions set values in the $values array, where they stay
    // until a call to 'update' happens. This is why they all call setSetFields
    // which contains a list of fields to update.

    /**
     * Set a string field in the $values array.
     * @param  string $fieldName  API name of field to set.
     * @param  string $value      Set to empty string '' if NULL.
     * TODO Check why this accepts NULL.
     */
    private function setFieldString(string $fieldName, ?string $value): void
    {
        if ($value == null)
        {
            $value = '';
        }

        $this->values [$fieldName] = $value;

        $this->setSetFields($fieldName);
    }

    /**
     * Set a bool field in the $values array.
     * @param  string $fieldName  API name of field to set.
     * @param  bool   $value
     */
    private function setFieldBool(string $fieldName, bool   $value): void
    {
        $this->values [$fieldName] = $value;

        $this->setSetFields($fieldName);
    }

    /**
     * Set a array field in the $values array.
     * @param  string $fieldName  API name of field to set.
     * @param  array <string> $value
     */
    private function setFieldArray(string $fieldName, array  $value): void
    {
        $this->values [$fieldName] = $value;

        $this->setSetFields($fieldName);
    }

    /**
     * Set a field containg API_Event object in the $values array.
     * @param  string    $fieldName  API name of field to set.
     * @param  API_Event $setEvent
     */
    private function setFieldEvent(string $fieldName, API_Event $setEvent): void
    {
        $this->values [$fieldName] = $setEvent;

        $this->setSetFields($fieldName);
    }

    /**
     * Set a field containg object in the $values array.
     * @param  string  $fieldName  API name of field to set.
     * @param  mixed   $obj
     */
    private function setFieldObject(string $fieldName, mixed $obj): void
    {
        $this->values [$fieldName] = $obj;

        $this->setSetFields($fieldName);
    }

    /**
     * Add field  to list of fields that have been set, so that they are sent
     * to the Server on the next call to {@link self::update}.
     * @param string $fieldName NAME of field (NOT value).
     */
    private function setSetFields(string $fieldName): void
    {
        $this->setFields [$fieldName] = true;
    }

    //--------------------------------------------------------------------------

    /**
     * Set critical flag (is_alive or is_public only) directly by calling '/update'
     * with only the flag. If successful also updates the internal field value.
     * This is needed (maybe) because these two flag impact many other operations.
     *
     * @param string $fieldName Name of flag to set (is_alive or is_public).
     * @param bool   $value
     * @return bool  Success.
     */

    /** @phpstan-ignore-next-line */
    private function setPriorityFlag(string $fieldName, bool $value): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $updateURL     = '/' . $this->getID() . "/update";
        $priorityParam = [
            "$fieldName" => $value,
            "fields"     => "id,$fieldName"
        ];

        // -------------------------------
        $priorityResults = $API_Wrap->callAPI_POST($updateURL, $priorityParam);
        // -------------------------------

        if (array_key_exists('id', $priorityResults))
        {
            $this->values [$fieldName] = $value;

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Isolate the profile ID fro a URL.
     * @param  string $URL
     * @return string
     */
    private function getIDFromURL(string $URL): string
    {
        $parts = explode("/", $URL);
        return end($parts);
    }

    /**
     * Check if ID is an GUID that does NOT have the prefix, and add it.
     * @param  string $ID to check and fix if needed.
     * @return string the ID, fixed or as is.
     */
    private static function fixGUID(string $ID): string
    {
        if (strcmp(substr($ID, 0, 8), 'profile-') != 0)
        {
            // GUID without the prefex. Add it.
            $ID = "profile-g$ID";
        }

        return $ID;
    }

    /**
     * @internal
     * Function for TESTING only. Compare object to test array with same values.
     * @param array <string, mixed> $testArray  Values to compare to.
     * @param bool  $report     Dump full report of differences.
     * @return bool             TRUE when identical.
     */
    public function compareToArray(array $testArray, bool $report = false, bool $isBrief = false): bool
    {
        $profilesSame = true;

        if (!$isBrief)
        {
            // Check arrays of references.
            if ($testArray ['projects'] != $this->projects
                || $testArray ['documents'] != $this->documents
                || $testArray ['photos'] != $this->photos
                || $testArray ['videos'] != $this->videos)
            {
                $profilesSame = false;
                if ($report)
                {
                    echo "One of the arrays is different\r\n";
                }

                if ($testArray ['projects'] != $this->projects)
                {
                    if ($report)
                    {
                        echo "projects array is different\r\n";
                    }

                    print_rr($testArray ['projects'], $this->projects, 40);
                }

                if ($testArray ['documents'] != $this->documents)
                {
                    if ($report)
                    {
                        echo "documents arrays is different\r\n";
                    }

                    print_rr($testArray ['documents'], $this->documents, 40);
                }

                if ($testArray ['photos'] != $this->photos)
                {
                    if ($report)
                    {
                        echo "photos arrays is different\r\n";
                    }

                    print_rr($testArray ['photos'], $this->photos, 40);
                }

                if ($testArray ['videos'] != $this->videos)
                {
                    if ($report)
                    {
                        echo "videos arrays is different\r\n";
                    }

                    print_rr($testArray ['videos'], $this->videos, 40);
                }
            }
        }

        // Check 'values' array.
        if ($profilesSame || $report)
        {
            foreach ($this->values as $fieldname => $field)
            {
                if (array_key_exists($fieldname, $testArray['values']))
                {
                    $fieldOK = true;

                    // Field exists in both arrays.
                    // Do field specific compares.

                    switch ($fieldname)
                    {
                        case 'mugshot_urls':
                        case 'photo_urls':
                            // Ignore field, because hash values can change between calls.
                            break;

                        case 'birth':
                        case 'death':
                        case 'burial':
                            // Handle fields with API_Event objects.
                            // Complex objects require specific compare.

                            $fieldOK = API_Event::__equals($field, $testArray['values'][$fieldname]);

                            if (!$fieldOK)
                            {
                                if ($report)
                                {
                                    echo "Event $fieldname is different.\n";
                                }
                            }
                            break;

                        case 'current_residence':
                        case 'location':
                            $fieldOK = API_Location::__equals($field, $testArray['values'][$fieldname]);

                            if (!$fieldOK)
                            {
                                if ($report)
                                {
                                    echo "current_residence or location field is different.\n";
                                }
                            }
                            break;

                        case 'phone_numbers':
                            $phoneArray = $testArray['values']['phone_numbers'];
                            $fieldOK    = ($field != $phoneArray);

                            if (!$fieldOK)
                            {
                                if ($report)
                                {
                                    echo "phone_numbers field is different.\n";
                                }
                            }
                            break;

                        default:
                            $expected = $testArray['values'][$fieldname];

                            if (is_array($field))
                            {
                                sort($field);
                                sort($expected);
                            }

                            if ($field != $expected)
                            {
                                if ($report)
                                {
                                    echo "\r\n \"$fieldname\" is different.\r\n";
                                }

                                $fieldOK = false;
                            }
                            break;
                    }   // End switch.

                    if (!$fieldOK)
                    {
                        $profilesSame = false;

                        if ($report)
                        {
                            echo "Expected:\r\n";
                            print_r($testArray['values'][$fieldname]);
                            echo "\r\nActual:\r\n";
                            print_r($field);
                        }
                    }
                }       // End Field exists in both arrays.
            }           // End fields loop.

            // Finished comparing all 'values' fields that exist in BOTH objects.
            // Verify that there are NO additional fields.

            $missingFields = array_diff_key($this->values, $testArray['values']);

            if (count($missingFields) > 0)
            {
                $profilesSame = false;

                if ($report)
                {
                    // Some keys only found in one side.
                    foreach ($missingFields as $fieldname => $fieldValue)
                    {
                        if (array_key_exists($fieldname, $testArray['values']))
                        {
                            echo "\r\n['values']['$fieldname'] only found in testArray. Value:\r\n";
                        }
                        else
                        {
                            echo "\r\n['values']['$fieldname'] only found in this object. Value:\r\n";
                        }

                        if (is_bool($fieldValue))
                        {
                            echo boolToString($fieldValue);
                        }
                        else
                        {
                            print_r($fieldValue);
                        }
                    }
                }
            }
        }

        return $profilesSame;
    }

    /**
     * @internal
     * Function for TESTING only. Compare $values array to array in other API_Profile object.
     * @param  API_Profile $otherProfile Profile to compare to.
     * @param  bool  $report Dump full report of differences.
     * @return bool          TRUE when identical.
     */
    public function comparePartial(API_Profile $otherProfile, bool $report = false): bool
    {
        // These are new profiles, so we're only comparing the 'values' arrays.
        // The other fields don't have values yet.
        $profilesSame = true;

        foreach ($this->values as $fieldname => $field)
        {
            if (array_key_exists($fieldname, $otherProfile->values))
            {
                $fieldOK = true;

                // Field exists in both profiles.
                // Do field specific compares.

                switch ($fieldname)
                {
                    case 'mugshot_urls':
                    case 'photo_urls':
                        // Ignore field, because hash values can change between calls.
                        break;

                    case 'url':
                    case 'profile_url':
                    case 'birth_order':
                    case 'claimed':
                    case 'created_by':
                    case 'creator':
                    case 'created_at':
                    case 'deleted':
                    case 'updated_at':
                    case 'living':
                        // Ignore field, because missing from ADD replies.
                        break;

                    case 'birth':
                    case 'death':
                    case 'burial':
                        // Handle fields with API_Event objects.
                        // Complex objects require specific compare.

                        $fieldOK = API_Event::__equals($field, $otherProfile->values[$fieldname]);

                        if (!$fieldOK)
                        {
                            if ($report)
                            {
                                echo "Event $fieldname is different.\r\n";
                            }
                        }
                        break;

                    case 'current_residence':
                    case 'location':
                        $fieldOK = API_Location::__equals($this->values['location'], $otherProfile->values['location']);

                        if (!$fieldOK)
                        {
                            if ($report)
                            {
                                echo "current_residence or Location field is different.\r\n";
                            }
                        }
                        break;

                    default:
                        if ($field != $otherProfile->values[$fieldname])
                        {
                            if ($report)
                            {
                                echo "$fieldname is different.\r\n";
                            }

                            $fieldOK = false;
                        }
                        break;
                }   // End switch.

                if (!$fieldOK && $report)
                {
                    $profilesSame = false;

                    if (is_string($field))
                    {
                        echo "Expected: '$field'\r\n";
                    }
                    else
                    {
                        echo "Expected:\r\n";
                        print_r($field);
                    }

                    if (is_string($otherProfile->values[$fieldname]))
                    {
                        echo "Actual:   '{$otherProfile->values[$fieldname]}'\r\n\r\n";
                    }
                    else
                    {
                        echo "\r\nActual:\r\n";
                        print_r($otherProfile->values[$fieldname]);
                        echo "\r\n";
                    }
                }
            }       // End Field exists in both arrays.
            else
            {
                // TODO Field only exists in this object.
            }
        }           // End fields loop.

        return $profilesSame;
    }

    //=====================================================
    // Functions to add profile objects. Either as a standalone object, or in
    // reference to the current profile.

    /**
     * Add THIS Profile as a SEPARATE branch.
     *
     * @return API_Profile  Updated profile (ID values, and others are added).
     *
     * The add function can update the following fields. Use the set functions
     * on the object before adding it.
     *
     * 'first_name'   string      Profile's first name
     * 'middle_name'  string      Profile's middle name
     * 'last_name'    string      Profile's last name
     * 'maiden_name'  string      Profile's maiden name
     * 'display_name' string      Profile's display name
     * 'suffix'       string      Profile's suffix
     * 'title'        string      Profile's title
     *
     * 'names'        array       Nested maps of locales to name fields to values.
     *                            Example: {"de": {"last_name": "Smith"}}
     * 'nicknames'    array       Array of strings for "Also known as" field. Can
     *                            also be set as as a comma delimited list (Returned as an array).
     *
     * 'about_me'     string      Profile's about me section (cf. detail_strings)
     *                            (must be requested)
     * 'baptism'      API_Event   Profile's baptism event info
     * 'birth'        API_Event   Profile's birth event info
     * 'burial'       API_Event   Profile's burial event info
     * 'death'        API_Event   Profile's death event info
     * 'cause_of_death' string    Profile's death cause
     * 'gender'       string      Profile's gender
     * 'is_alive'     bool        TRUE if the profile is living. See comment for 'public'.
     * 'public'       bool        TRUE if the profile is public. It is NOT possible to add
     *                            a profile that is BOTH Alive && Public. If 'is_alive' is
     *                            set, the server automatically sets 'public' to FALSE.

     * 'locked'       bool        TRUE if the profile has been locked down by a curator
     * 'merge_note'   array       or string Note explaining the profile's merge status
     * 'occupation'   string      Profile's occupation
     *
     * Missing:
     * 'email'        string      Profile's email address.
     */
    public function add(): ?API_Profile
    {
        return $this->addRelative($this, '/add', null);
    }

    /**
     * Add Profile as a CHILD of the current profile.
     *
     * @param  API_Profile  $child   profile information.
     * @param  string       $relationship_modifier  Set to 'adopt' or 'foster' when
     *                      adding children or siblings to a profile
     * @return API_Profile  Updated profile (ID values, and others are added).
     *
     * The addChild function can update the following fields. Use the set
     * functions on the child object before adding it.
     *
     * 'first_name'   string      Profile's first name
     * 'middle_name'  string      Profile's middle name
     * 'last_name'    string      Profile's last name
     * 'maiden_name'  string      Profile's maiden name
     * 'display_name' string      Profile's display name
     * 'suffix'       string      Profile's suffix
     * 'title'        string      Profile's title
     *
     * 'names'        array       Nested maps of locales to name fields to values.
     *                            Example: {"de": {"last_name": "Smith"}}
     * 'nicknames'    array       Array of strings for "Also known as" field. Can
     *                            also be set as as a comma delimited list (Returned as an array).
     *
     * 'baptism'      API_Event   Profile's baptism event info
     * 'birth'        API_Event   Profile's birth event info
     * 'burial'       API_Event   Profile's burial event info
     * 'death'        API_Event   Profile's death event info
     * 'marriage'     API_Event   Profile's marriage event info
     * 'divorce'      API_Event   Profile's divorce event info
     *
     * 'about_me'     string      Profile's about me section (cf. detail_strings)
     * 'email'        string      Profile's email address
     * 'gender'       string      Profile's gender
     * 'is_alive'     bool        TRUE if the profile is living. See comment for 'public'.
     * 'public'       bool        TRUE if the profile is public. It is NOT possible to add
     *                            a profile that is BOTH Alive && Public. If 'is_alive' is
     *                            set, the server automatically sets 'public' to FALSE.
     *
     * Missing:
     * 'cause_of_death' string    Profile's death cause
     * 'locked'       bool        TRUE if the profile has been locked down by a curator
     * 'occupation'   string      Profile's occupation
     */
    public function addChild(API_Profile $child, string $relationship_modifier = ''): ?API_Profile
    {
        $modifierParam = [ 'relationship_modifier' => $relationship_modifier ];

        return $this->addRelative($child, '/add-child', $modifierParam);
    }

    /**
     * Add Profile as a PARENT of the current profile.
     *
     * @param  API_Profile  $parent  profile information.
     * @param  string       $relationship_modifier  Set to 'adopt' or 'foster' when
     *                               adding children or siblings to a profile
     * @return API_Profile  Updated profile (ID values, and others are added).
     *
     * @see API_Profile::addChild for list of fields that can be set.
     */
    public function addParent(API_Profile $parent, string $relationship_modifier = ''): ?API_Profile
    {
        $modifierParam = [ 'relationship_modifier' => $relationship_modifier ];

        return $this->addRelative($parent, '/add-parent', $modifierParam);
    }

    /**
     * Add Profile as a PARTNER of the current profile.
     *
     * @param  API_Profile  $partnerIn  profile information.
     * @param  API_Event    $marriageEvent
     * @param  API_Event    $divorceEvent
     * @return API_Profile  Updated profile (ID values, and others are added).
     *
     * @see API_Profile::addChild for list of fields that can be set on $partner.
     */
    public function addPartner(
        API_Profile $partnerIn,
        ?API_Event  $marriageEvent = null,
        ?API_Event  $divorceEvent = null,
    ): ?API_Profile {
        $partnerParams = [];

        if ($marriageEvent != null)
        {
            $partnerParams ['marriage'] = $marriageEvent->makeParamArray();
        }

        if ($divorceEvent != null)
        {
            $partnerParams ['divorce'] = $divorceEvent->makeParamArray();
        }

        return $this->addRelative($partnerIn, '/add-partner', $partnerParams);
    }

    /**
     * Add Profile as a SIBLING of the current profile.
     *
     * @param  API_Profile  $sibling  profile information.
     * @param  string       $relationship_modifier  Set to 'adopt' or 'foster' when
     *                      adding children or siblings to a profile
     * @return API_Profile  Updated profile (ID values, and others are added).
     *
     * @see API_Profile::addChild for list of fields that can be set.
     */
    public function addSibling(API_Profile $sibling, string $relationship_modifier = ''): ?API_Profile
    {
        $modifierParam = [ 'relationship_modifier' => $relationship_modifier ];

        return $this->addRelative($sibling, '/add-sibling', $modifierParam);
    }

    /**
     * Private function that does the API work for all of the add-profile functions.
     * @param  API_Profile  $relativeIn  Profile to add.
     * @param  string       $addType     part of API URL signifying what relation profile
     *                                   has to base profile.
     * @param  array <string, string> $specificParams  Values for specific add. Typically the
     *                                       relationship_modifier.
     * @return API_Profile  Updated profile (ID values, and others are added). NULL
     *                      When add failed.
     */
    private function addRelative(
        API_Profile $relativeIn,
        string      $addType,
        ?array      $specificParams,
    ): ?API_Profile {
        $API_Wrap = API_Wrapper::getInstance();

        if (strcmp($addType, '/add') != 0)
        {
            // Regular types of add calls need to reference the profile being added to.
            $addURL = '/' . $this->getID() . $addType;
        }
        else
        {
            // When adding separate branch no ID is used.
            $addURL = '/profile/add';
        }

        if ($specificParams == null)
        {
            $specificParams = [];
        }

        // Combine fields from profile with parameters for the specific add call.
        // The value order for array_merge doesn't matter because the two arrays
        // have mutually exclusive values.

        $profileParams = $relativeIn->makeParamArray(allFields: true);
        $addParams     = array_merge($profileParams, $specificParams);

        // -------------------------------
        $relativeArray = $API_Wrap->callAPI_POST($addURL, $addParams);
        // -------------------------------

        $relativeOut = null;

        if (array_key_exists('id', $relativeArray))
        {
            $relativeOut = API_Profile::newFromArray($relativeArray, brief: $relativeIn->isBrief());
        }

        return $relativeOut;
    }

    //=====================================================
    // Functions to add sub-objects to this object.

    /**
     * Create and add document to current profile.
     * ALL of the parameters describe the document.
     * @param API_Date $date
     * @param string   $description
     * @param string   $filePathname file to create document from. Will be Base64 encoded.
     * @param string   $labels       Document's comma separated labels.
     * @param string   $source_url   Source URL for the document.
     * @param string   $text         Text to create document from.
     * @param string   $title        (is the only mandatory parameter).
     * @return API_Document|NULL     Created object. NULL if add failed.
     * @see API_Document.
     */
    public function addDocument(
        ?API_Date $date,
        ?string   $description,
        ?string   $filePathname,
        ?string   $labels,
        ?string   $source_url,
        ?string   $text,
        string    $title,
    ): ?API_Document {
        $API_Wrap = API_Wrapper::getInstance();

        $addDocumentURL = '/' . $this->getID() . '/add-document';

        $addParams = [
            'title' => $title   // The only mandatory parameter.
        ];

        if ($date != null)
        {
            $addParams ['date'] = $date->makeParamArray();
        }

        if ($description != null)
        {
            $addParams ['description'] = $description;
        }

        if ($labels != null)
        {
            $addParams ['labels'] = $labels;
        }

        if ($source_url != null)
        {
            $addParams ['source_url'] = $source_url;
        }

        if ($text != null)
        {
            $addParams ['text'] = $text;
        }

        if ($filePathname != null)
        {
            $fileString = file_get_contents($filePathname);
            $file       = base64_encode($fileString);

            $addParams ['file'] = $file;
        }

        // -------------------------------
        $docArray = $API_Wrap->callAPI_POST($addDocumentURL, $addParams);
        // -------------------------------

        $doc = null;

        if (array_key_exists('id', $docArray))
        {
            $doc = API_Document::newFromArray($docArray);
        }

        return $doc;
    }

    /**
     * Create and/or add mugshot to profile.
     * ALL of the parameters describe the mugshot photo.
     * IF $photo_id is set, will add an existing photo, otherwise it will create
     * and add a new file.
     * @param  string   $album_id     Album to put mugshot photo in.
     * @param  API_Date $date
     * @param  string   $description
     * @param  string   $filePathname File to create document from. Will be Base64 encoded.
     * @param  string   $photo_id     ID of existing photo to use, and NOT create a new one.
     * @param  string   $title
     * @param  string   $fileType     Content-Type file is encoded in.
     *
     * @return API_Photo Created object. NULL if add failed.
     * @see API_Photo.
     */
    public function addMugshot(
        ?string   $album_id,
        ?API_Date $date,
        ?string   $description,
        ?string   $filePathname,
        ?string   $photo_id,
        string    $title,
        string    $fileType = null,
    ): API_Photo {
        $API_Wrap = API_Wrapper::getInstance();

        $addMugshotURL = '/' . $this->getID() . '/add-mugshot';

        $addParams = [
            'title' => $title   // Mandatory value.
        ];

        if ($album_id != null)
        {
            $addParams ['album_id'] = $album_id;
        }

        if ($date != null)
        {
            $addParams ['date'] = $date->toJSON();
        }

        if ($description != null)
        {
            $addParams ['description'] = $description;
        }

        $fileBinary = null;

        if ($photo_id != null)
        {
            // Create mugshot from existing photo. NO file to upload.
            // So use regular POST operation.

            $addParams ['photo_id'] = $photo_id;

            //--------------------------------------------------
            $mugshotArray = $API_Wrap->callAPI_POST($addMugshotURL, $addParams);
            //--------------------------------------------------
        }
        else
        {
            // $photo_id is NULL. Use $filePathname value to create photo from.

            if ($filePathname != null)
            {
                $fileBinary = file_get_contents($filePathname);
            }

            //--------------------------------------------------
            $mugshotArray = $API_Wrap->callAPI_POST_WithBinary(
                $addMugshotURL,
                $addParams,
                $fileBinary,
                $filePathname,
                $fileType,
            );
            //--------------------------------------------------
        }

        $mug = null;

        if (isset($mugshotArray ['id']))
        {
            $mug = API_Photo::newFromArray($mugshotArray);
        }

        return $mug;
    }

    /**
     * Create and add photo to profile.
     * ALL of the parameters describe the photo.
     * @param  API_Date $date
     * @param  string   $description
     * @param  string   $filePathname file to create document from. Will be Base64 encoded.
     * @param  string   $title
     * @param  string   $fileType     Content-Type file is encoded in.
     *
     * @return API_Photo|NULL  Created object. NULL if add failed.
     */
    public function addPhoto(
        ?API_Date $date,
        ?string   $description,
        ?string   $filePathname,
        string    $title,
        string    $fileType = null,
    ): ?API_Photo {
        $API_Wrap = API_Wrapper::getInstance();

        $addPhotoURL = '/' . $this->getID() . '/add-photo';

        $addParams = [
            'title' => $title   // Mandatory value.
        ];

        if ($date != null)
        {
            $addParams ['date'] = $date->toJSON();
        }

        if ($description != null)
        {
            $addParams ['description'] = $description;
        }

        if ($filePathname != null)
        {
            $fileBinary = file_get_contents($filePathname);
        }
        else
        {
            $fileBinary = null;
        }

        // -------------------------------
        $photoArray = $API_Wrap->callAPI_POST_WithBinary($addPhotoURL, $addParams, $fileBinary, $filePathname, $fileType);
        // -------------------------------

        $photo = null;

        if (array_key_exists('id', $photoArray))
        {
            $photo = API_Photo::newFromArray($photoArray);
        }

        return $photo;
    }

    /**
     * Create and add video to profile.
     * ALL of the parameters describe the video.
     * @param  API_Date $date
     * @param  string   $description
     * @param  string   $filePathname File to create document from. Will be Base64 encoded.
     * @param  string   $title
     * @param  string   $fileType     Content-Type file is encoded in.
     * @return API_Video|NULL  Created object. NULL if add failed.
     */
    public function addVideo(
        ?API_Date $date,
        ?string   $description,
        ?string   $filePathname,
        string    $title,
        string    $fileType = null,
    ): ?API_Video {
        $API_Wrap = API_Wrapper::getInstance();

        $addVideoURL = '/' . $this->getID() . '/add-video';

        $addParams ['title'] = $title;   // Value NOT optional.
        if ($date != null)
        {
            $addParams ['date'] = $date->toJSON();
        }

        if ($description != null)
        {
            $addParams ['description'] = $description;
        }

        if ($filePathname != null)
        {
            $fileBinary = file_get_contents($filePathname);
        }
        else
        {
            $fileBinary = null;
        }

        //--------------------------------------------------
        $videoArray = $API_Wrap->callAPI_POST_WithBinary($addVideoURL, $addParams, $fileBinary, $filePathname, $fileType);
        //--------------------------------------------------

        $video = null;

        if (array_key_exists('id', $videoArray))
        {
            $video = API_Video::newFromArray($videoArray);
        }

        return $video;
    }

    //=====================================================

    /**
     * Compare this profile to another.
     * Work is done by {@link API_Profile::compare2}.
     * @param  string $compareID  ID of other profile.
     * @return array <array <string, string|mixed>> Differenced between the profiles.
     */
    public function compare(string $compareID): array
    {
        return API_Profile::compare2($this->getID(), $compareID);
    }

    /**
     * Delete this profile
     * @return bool  Success.
     */
    public function delete(): bool
    {
        $deleted = self::deleteID($this->getID());

        if ($deleted)
        {
            $this->values ['deleted'] = true;
        }

        return $deleted;
    }

    /**
     * Delete profile by ID.
     * @param  string $ID
     * @return bool  Success.
     */
    public static function deleteID(string $ID): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $delete_URL = "/$ID/delete";

        // -------------------------------
        $deleteResults = $API_Wrap->callAPI_POST($delete_URL, null);
        // -------------------------------

        $deleted = false;
        if (array_key_exists('result', $deleteResults)
            && strcmp('Deleted', $deleteResults ['result']) == 0)
        {
            $deleted = true;
        }

        return $deleted;
    }

    /**
     * Follow this profile (for user running application).
     * @return bool  Success.
     */
    public function follow(): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $follow_URL = '/' . $this->getID() . "/follow";

        // -------------------------------
        $followResults = $API_Wrap->callAPI_POST($follow_URL, null);
        // -------------------------------

        if (array_key_exists('id', $followResults))
        {
            $this->updateFromArray($followResults);

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Get ancestors of current profile. TODO implement this function.
     * This API call only works on YOUR oen ancestors!
     * @return bool  Success.
     */
    public function getAncestors(int $generations): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $getAncestors_URL   = '/' . $this->getID() . "/ancestors";
        $getAncestors_Param = [
            'generations' => (string) $generations
        ];

        // -------------------------------
        $getResults = $API_Wrap->callAPI_POST($getAncestors_URL, $getAncestors_Param);
        // -------------------------------

        if (array_key_exists('id', $getResults))
        {
            // TODO Somehow I missed implementing this. Most complex
            // reply structure. Will get to it later. Will be required when
            // I want to actually crawl the tree...
            // Use this link for test data. It's complicated enough. :-p
            // profile-g5098603059080070794/immediate-family
        }

        return false;   //XXX
    }

    /**
     * Get immediate family of current profile. TODO implement this function.
     * @return bool  Success.
     */
    public function getImmediateFamily(): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $getFamily_URL = '/' . $this->getID() . "/immediate-family";

        // -------------------------------
        $getResults = $API_Wrap->callAPI_POST($getFamily_URL, null);
        // -------------------------------

        if (array_key_exists('id', $getResults))
        {
            // TODO Somehow I missed implementing this. Most complex
            // reply structure. Will get to it later. Will be required when
            // I want to actually crawl the tree...
            // Use this link for test data. It's complicated enough. :-p
            // profile-g5098603059080070794/immediate-family
        }

        return false;   //XXX
    }

    /**
     * Merge this profile with another (or create merge-request if not enough
     * permission to merge). Work is done by {@link API_Profile::merge2}.
     * @param  string $mergeID  ID of other profile.
     * @return bool  Success.
     */
    public function merge(string $mergeID): bool
    {
        return API_Profile::merge2($this->getID(), $mergeID);
    }

    /**
     * Unfollow this profile (for user running application).
     * @return bool  Success.
     */
    public function unfollow()
    {
        $API_Wrap = API_Wrapper::getInstance();

        $unfollow_URL = '/' . $this->getID() . "/unfollow";

        // -------------------------------
        $unfollowResults = $API_Wrap->callAPI_POST($unfollow_URL, null);
        // -------------------------------

        if (array_key_exists('id', $unfollowResults))
        {
            $this->updateFromArray($unfollowResults);

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Update profile's fields to Server.
     * This sends all of the fields that have been set, since the last update, to
     * the Server. The list of fields to update is in $setFields.
     * @return bool  Success.
     */
    public function update(): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $updateURL   = '/' . $this->getID() . "/update";
        $updateParam = $this->makeParamArray(allFields: false);

        // -------------------------------
        $updateResults = $API_Wrap->callAPI_POST($updateURL, $updateParam);
        // -------------------------------

        if (array_key_exists('id', $updateResults))
        {
            $this->updateFromArray($updateResults);

            $this->setFields = self::BRIEF_FIELDS;

            return true;
        }
        else
        {
            return false;
        }
    }

    //=====================================================

    /**
     * Find path (if any) between current profile and other profile.
     * @param string  $secondProfile  ID of profile to connect to.
     * @param string  $path_type      Type of path search (values: blood or inlaw, default: closest).
     * @param bool    $refresh        Refresh an exising path (if allowed).         (default: FALSE).
     * @param bool    $search         Whether to search beyond maximum family range (default: TRUE).
     * @param bool    $skip_email     Omits email notification of result            (default: TRUE).
     * @param bool    $skip_notify    Omits on-site notification of result          (default: TRUE).
     * @return API_Path|NULL Structure of array:
     */
    public function getPathTo(
        string $secondProfile,
        string $path_type = 'closest',
        bool   $refresh = false,
        bool   $search = true,
        bool   $skip_email = true,
        bool   $skip_notify = true,
    ): ?API_Path {
        $API_Wrap = API_Wrapper::getInstance();

        static $pathTypes = [ 'blood', 'inlaw', 'closest' ];
        $pathParams       = [];

        if (in_array($path_type, $pathTypes))
        {
            $pathParams [] = 'path_type=' . $path_type;
        }

        // TODO Check if this parameter stopped working???
        // if ($refresh) { $pathParams [] = 'refresh=1';     }
        // else          { $pathParams [] = 'refresh=0';     }

        // The following paramters are only added if NOT their default values.
        if ($search)
        {
            $pathParams [] = 'search=1';
        }

        if (!$skip_email)
        {
            $pathParams [] = 'skip_email=0';
        }

        if (!$skip_notify)
        {
            $pathParams [] = 'skip_notify=0';
        }

        $pathTo_URL = '/' . $this->getID() . '/path-to/' . API_Profile::fixGUID($secondProfile)
                      . '?' . implode('&', $pathParams);

        //--------------------------------------------------
        $pathArray = $API_Wrap->callAPI($pathTo_URL);
        //--------------------------------------------------

        $path = null;
        if (array_key_exists('relations', $pathArray))
        {
            $path = API_Path::newFromArray($pathArray);
        }

        return $path;
    }

    /**
     * Get list of documents that this profile is tagged in.
     * @param  bool $getAll    Return entire list, or only first page of it.
     * @param  int  $pageSize  Number of entries per page. Not relevant when $getAll is set.
     * @return API_ListObjects
     */
    public function getDocuments(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        if (!$this->isLoaded('documents'))
        {
            $getDocumentsURL = '/' . $this->getID() . '/documents';

            $this->documents = new API_ListObjects($getDocumentsURL, API_Document::class, $getAll, $IDsOnly, fromAPICall: true, pageSize: $pageSize);
            $this->setLoaded('documents');
        }

        return $this->documents;
    }

    /**
     * Get list of photos that this profile is tagged in.
     * @param  bool $getAll    Return entire list, or only first page of it.
     * @param  int  $pageSize  Number of entries per page. Not relevant when $getAll is set.
     * @return API_ListObjects
     */
    public function getPhotos(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        if (!$this->isLoaded('photos'))
        {
            $getPhotosURL = '/' . $this->getID() . '/photos';

            $this->photos = new API_ListObjects($getPhotosURL, API_Photo::class, $getAll, $IDsOnly, fromAPICall: true, pageSize: $pageSize);
            $this->setLoaded('photos');
        }

        return $this->photos;
    }

    /**
     * Get array of projects the profile is included in.
     * @return array <string> of project-UIDs. Empty array if NO projects.
     */
    public function getProjects(bool $force = false): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        if (!$this->isLoaded('projects'))
        {
            $GetProjects_URL = '/' . $this->getID() . '?fields=project_ids';

            //--------------------------------------------------
            $projectArray = $API_Wrap->callAPI($GetProjects_URL);
            //--------------------------------------------------

            $projects = [];

            if (isset($projectArray['project_ids']))
            {
                // Answer returned.

                $projects = $projectArray['project_ids'];
            }

            $this->projects = $projects;
            $this->setLoaded('projects');
        }

        return $this->projects;
    }

    /**
     * Get list of videos that this profile is tagged in.
     * @param  bool $getAll    Return entire list, or only first page of it.
     * @param  int  $pageSize  Number of entries per page. Not relevant when $getAll is set.
     * @return API_ListObjects
     */
    public function getVideos(bool $getAll = false, bool $IDsOnly = true, bool $force = false, int $pageSize = API_ListBase::SIZE_OF_BATCH): API_ListObjects
    {
        if (!$this->isLoaded('videos'))
        {
            $getVideosURL = '/' . $this->getID() . '/videos';

            $this->videos = new API_ListObjects($getVideosURL, API_Video::class, $getAll, $IDsOnly, fromAPICall: true, pageSize: $pageSize);
            $this->setLoaded('videos');
        }

        return $this->videos;
    }

    //=====================================================
    // Special cases of update for specific fields.

    /**
     * Update AboutMe text.
     * @param  string  $aboutMe New text.
     * @return bool    Update suceeded.
     */
    public function updateAboutMe(string $aboutMe): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $updateOK = false;

        $updateAboutMe_URL = '/' . $this->getID() . '/update';
        $updateParams      = [
            'about_me' => $aboutMe,
            'fields'   => 'id,about_me'
        ];

        //--------------------------------------------------
        $updateArray = $API_Wrap->callAPI_POST($updateAboutMe_URL, $updateParams);
        //--------------------------------------------------

        if (array_key_exists('about_me', $updateArray))
        {
            $this->values ['about_me'] = $updateArray ['about_me'];
            $updateOK                  = true;
        }
        else
        {
            // No 'about_me' in reply.
            if ($aboutMe == '' && count($updateArray) == 1)
            {

                $this->values ['about_me'] = '';
                $updateOK                  = true;
            }
        }

        return $updateOK;
    }

    /**
     * Update DetailStrings (AboutMe) text.
     * @param string $locale      ISO-639 language codes (e.g. "en-US", "he", "sp").
     *                            User is responsible to ensure value is valid.
     * @param string $detailText  New text.
     * @return boolean Update suceeded.
     */
    public function updateDetailStrings(string $locale, string $detailText): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $detailValue [$locale] = [ 'about_me' => $detailText ];

        $updateAboutMe_URL = '/' . $this->getID() . '/update';
        $updateParams      = [
            'detail_strings' => $detailValue,
            'fields'         => 'id,detail_strings'
        ];

        //--------------------------------------------------
        $updateReply = $API_Wrap->callAPI_POST($updateAboutMe_URL, $updateParams);
        //--------------------------------------------------

        $updateOK = false;

        if (array_key_exists('detail_strings', $updateReply))
        {
            $this->values   ['detail_strings'] = $updateReply ['detail_strings'];
            $updateOK                          = true;
        }
        else
        {
            // No 'detail_strings' in reply.
            // Only the single ID field. This is the reply when the
            // "default" 'en-US' about_me is updated.
            if ($locale == 'en-US' && array_key_exists('id', $updateReply))
            {
                $this->values ['detail_strings']['en-US'] = $detailText;
                $updateOK                                 = true;
            }
        }

        return $updateOK;
    }

    //=====================================================
    // Static functions to perform on TWO profiles, or the search function,
    // These do not need a Profile object and return other data types.

    /**
     * Compare two profiles to each other.
     * @param  string $compare1  ID of first  profile.
     * @param  string $compare2  ID of second profile.
     * @return array <array <string, string|mixed>> Differenced between the profiles.
     */
    public static function compare2(string $compare1, string $compare2): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        $compare1 = API_Profile::fixGUID($compare1);
        $compare2 = API_Profile::fixGUID($compare2);

        $compare_URL = "/$compare1/compare/$compare2";

        //--------------------------------------------------
        $compareResults = $API_Wrap->callAPI($compare_URL);
        //--------------------------------------------------

        return $compareResults['results'];
    }

    /**
     * Merge two profiles with each other (or create merge-request if not enough
     * permission to merge).
     * @param  string $merge1  ID of first  profile.
     * @param  string $merge2  ID of second profile.
     * @return bool  Success.
     * TODO Test what results are when merge-request created.
     */
    public static function merge2(string $merge1, string $merge2): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        $merge1 = API_Profile::fixGUID($merge1);
        $merge2 = API_Profile::fixGUID($merge2);

        $merge_URL = "/$merge1/merge/$merge2";

        //--------------------------------------------------
        $mergeResults = $API_Wrap->callAPI_POST($merge_URL, null);
        //--------------------------------------------------

        $expectedResult = "$merge2 merged into $merge1";

        $mergeOK = false;
        if ($mergeResults != null && strcmp($mergeResults['result'], $expectedResult) == 0)
        {
            $mergeOK = true;
        }

        return $mergeOK;
    }

    /**
     * Search for profiles that match $names. Returns list of matches.
     * @param string $names     Names to search for.
     * @param  bool  $getAll    Return entire list, or only first page of it.
     * @param  bool $IDsOnly    Instead of returning API_Profile objects, returns
     *                          only their ID and GUID values.
     * @param  int   $pageSize  Number of entries per page. Not relevant when $getAll is set.
     * @return API_ListProfiles
     */
    public static function search(
        string $names,
        bool   $getAll = false,
        bool   $IDsOnly = true,
        int    $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        $names = str_replace(' ', '+', $names);

        $search_URL = "/profile/search?names=$names";

        $searchResults = new API_ListProfiles($search_URL, $getAll, $IDsOnly, true, $pageSize);

        return $searchResults;
    }

    /**
     * Static function to convert between a profiles' ID & GUID, without needing
     * to create an API_Profile object.
     * @param string   $ID      Value to convert.
     * @param bool     $isGUID  When TRUE $id is a GUID (convert to ID).
     * @return string  Requested GUID/ID. '-1' when profile not found.
     */
    public static function convertID(string $ID, bool $isGUID = false): string
    {
        $API_Wrap = API_Wrapper::getInstance();

        if ($isGUID)
        {
            $ID = 'profile-g' . $ID;
        }

        $Get_URL = "/$ID?fields=id,guid";

        //--------------------------------------------------
        $idArray = $API_Wrap->callAPI($Get_URL);
        //--------------------------------------------------

        $convertedID = "-1";

        if ($idArray != null && (array_key_exists('id', $idArray)))
        {
            if ($isGUID)
            {
                $convertedID = $idArray ['id'];
            }
            else
            {
                $convertedID = $idArray ['guid'];
            }
        }

        return $convertedID;
    }

    /**
     * Static function to convert arrays of ID from ID to GUID (or GUID to ID).
     * @param array <string> $IDs    array of ID/GUID values.
     * @param bool   $isGUID
     * @return array <string> of IDs / GUIDs.
     */
    public static function convertListIDs(array $IDs, bool $isGUID = false): array
    {
        $convertedIDs = [];

        if ($IDs != null)
        {
            foreach ($IDs as $idVal)
            {
                $convertedIDs [] = API_Profile::convertID($idVal, $isGUID);
            }
        }

        return $convertedIDs;
    }

    //=====================================================

    /**
     * Gets the entire profile.
     * @param string  $ID   ID or GUID of profile.
     * @return API_Profile  The profile. Returns NULL if profile not found ($ID is
     *                      not valid).
     */
    public static function get(string $ID): ?API_Profile
    {
        $API_Wrap = API_Wrapper::getInstance();

        $profile = new API_Profile();

        $profile->setIsBrief(false);

        $Get_URL = '/' . API_Profile::fixGUID($ID);

        //--------------------------------------------------
        $profileGetArray = $API_Wrap->callAPI($Get_URL);
        //--------------------------------------------------

        if ($profileGetArray != null && array_key_exists('id', $profileGetArray))
        {
            $profile->updateFromArray($profileGetArray);
        }
        else
        {
            // NO reply. Probably an invalid ID.
            $profile = null;
        }

        return $profile;
    }

    /**
     * Get profile with only the most basic fields set.
     * @see API_Profile::BRIEF_FIELDS
     * @param  string  $ID  ID or GUID of profile.
     * @return API_Profile  The profile. Returns NULL if profile not found ($ID is
     *                      not valid).
     */
    public static function getBrief(string $ID): ?API_Profile
    {
        $API_Wrap = API_Wrapper::getInstance();

        $profile = new API_Profile();

        $profile->setIsBrief(true);

        $get_URL = '/' . API_Profile::fixGUID($ID);

        $get_URL = $get_URL . '?fields=' . implode(',', array_keys(self::BRIEF_FIELDS));

        //--------------------------------------------------
        $profileBriefArray = $API_Wrap->callAPI($get_URL);
        //--------------------------------------------------

        if ($profileBriefArray != null && (array_key_exists('id', $profileBriefArray)))
        {
            $profile->updateFromArray($profileBriefArray);
        }
        else
        {
            $profile = null;
        }

        return $profile;
    }

    /**
     * Get profile of the user who authorized the app ("self").
     * This can NOT be called from Client-side Auth because there is NO specific user.
     */
    public static function getProfileSelf(bool $brief): API_Profile
    {
        $API_Wrap = API_Wrapper::getInstance();

        $profile = new API_Profile();

        $profile->setIsBrief($brief);

        if ($brief)
        {
            $get_URL = "/profile?fields=" . implode(',', array_keys(self::BRIEF_FIELDS));
        }
        else
        {
            $get_URL = "/profile";
        }

        //--------------------------------------------------
        $profileSelfArray = $API_Wrap->callAPI($get_URL);
        //--------------------------------------------------

        if (isset($profileSelfArray['id']))
        {
            $profile->updateFromArray($profileSelfArray);
        }
        else
        {
            // TODO Fail something.
        }

        return $profile;
    }

    //=====================================================
    // Getters for specific fields. They call the API, except for the
    // basic fields which ALWAYS have a value for initialized objects.

    /**
     * Get the About-Me text for profile.
     * Call the API if value not loaded* or if forced.
     * This field must be requested explicitly, i.e. it's NOT returned by the
     * default '/profile' API call.
     * @param  bool   $force      Force an API call, to refresh the value.
     * @return string About-Me text.
     */
    public function getAboutMe(bool $force = false): string
    {
        $API_Wrap = API_Wrapper::getInstance();

        if (!array_key_exists('about_me', $this->values))
        {
            $this->values ['about_me'] = '';
        }

        if ($this->values['about_me'] == '' || $force)
        {
            $getAboutMe_URL = '/' . $this->getID() . '?fields=about_me';

            //--------------------------------------------------
            $aboutMeArray = $API_Wrap->callAPI($getAboutMe_URL);
            //--------------------------------------------------

            if ($aboutMeArray != null)
            {
                if (array_key_exists('about_me', $aboutMeArray))
                {
                    $this->values ['about_me'] = $aboutMeArray ['about_me'];
                }
            }
        }

        return $this->values ['about_me'];
    }

    public function getCauseOfDeath(bool $force = false): ?string
    {
        return $this->getFieldString('cause_of_death', $force);
    }

    public function getCreatedAt(bool $force = false): ?string
    {
        return $this->getFieldString('created_at', $force);
    }

    public function getCreatedBy(bool $force = false): ?string
    {
        return $this->getFieldString('created_by', $force, isURL: true);
    }

    public function getCreator(bool $force = false): ?string
    {
        return $this->getFieldString('creator', $force, isURL: true);
    }

    public function getCurator(bool $force = false): ?string
    {
        return $this->getFieldString('curator', $force, isURL: true);
    }

    public function getDocumentsUpdatedAt(bool $force = false): ?string
    {
        return $this->getFieldString('documents_updated_at', $force);
    }

    public function getEmail(bool $force = false): ?string
    {
        return $this->getFieldString('email', $force);
    }

    public function getGender(bool $force = false): API_Gender
    {
        // Get string from API and convert it to the API_Gender enum.
        $genderString = $this->getFieldString('gender', $force);

        if ($genderString != '')
        {
            $gender = API_Gender::from($genderString);
        }
        else
        {
            $gender = API_Gender::Unknown;
        }

        $this->values ['gender'] = $gender;

        return $gender;
    }

    public function getGUID(bool $force = false): string
    {
        return $this->getFieldString('guid', $force);
    }

    public function getID(): string
    {
        return $this->values ['id'];
    }

    public function getLanguage(bool $force = false): ?string
    {
        return $this->getFieldString('language', $force);
    }

    public function getMergedInto(bool $force = false): ?string
    {
        return $this->getFieldString('merged_into', $force);
    }

    public function getOccupation(bool $force = false): ?string
    {
        return $this->getFieldString('occupation', $force);
    }

    public function getPhotosUpdatedAt(bool $force = false): ?string
    {
        return $this->getFieldString('photos_updated_at', $force);
    }

    public function getPremiumStartDate(bool $force = false): ?string
    {
        return $this->getFieldString('premium_start_date', $force);
    }

    public function getProfileURL(bool $force = false): ?string
    {
        return $this->getFieldString('profile_url', $force);
    }

    public function getRelationship(bool $force = false): ?string
    {
        return $this->getFieldString('relationship', $force);
    }

    public function getUpdatedAt(bool $force = false): ?string
    {
        return $this->getFieldString('updated_at', $force);
    }

    public function getURL(bool $force = false): ?string
    {
        return $this->getFieldString('url', $force);
    }

    public function getVideosUpdatedAt(bool $force = false): ?string
    {
        return $this->getFieldString('videos_updated_at', $force);
    }

    //-----------------------------------------------------

    /**
     * Get 'About-Me' in ALL languages it is written in.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <string, array <string>> The key for the array are the locale codes:
     *                ISO-639 language codes (e.g. "en-US", "he", "sp").
     */
    public function getDetailStrings(bool $force = false): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        $details = [];

        if ($force || !array_key_exists('detail_strings', $this->values))
        {
            $this->values ['detail_strings'] = [];

            $getDetails = '/' . $this->getID() . '?fields=detail_strings';

            //--------------------------------------------------
            $detailsArray = $API_Wrap->callAPI($getDetails);
            //--------------------------------------------------

            if ($detailsArray != null)
            {
                $detailsArray = $detailsArray ['detail_strings'];

                foreach ($detailsArray as $locale => $detail)
                {
                    $details [$locale] = $detail;
                }

                $this->values ['detail_strings'] = $details;
            }
        }

        return $details;
    }

    /**
     * Get array of life events for profile.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <API_Event> Array of Events defined for profile, or empty array.
     */
    public function getEvents(bool $force = false): array
    {
        $API_Wrap = API_Wrapper::getInstance();

        if ($force || !array_key_exists('events', $this->values))
        {
            $this->values ['events'] = [];

            $getEventsURL = '/' . $this->getID() . '?fields=events';

            //--------------------------------------------------
            $eventsArray = $API_Wrap->callAPI($getEventsURL);
            //--------------------------------------------------

            if ($eventsArray != null)
            {
                foreach ($eventsArray['events'] as $event)
                {
                    $this->values ['events'][] = API_Event::newFromArray($event);
                }
            }

            return $this->values ['events'];
        }

        return $this->values ['events'];
    }

    /**
     * Get array of profile-ID of managers of this profile.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <string>
     */
    public function getManagers(bool $force = false): array
    {
        return $this->getFieldStringArray('managers', $force, isURL: true);
    }

    /**
     * Get Curator's Note (if any).
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return string
     */
    public function getMergeNote(bool $force = false): string
    {
        $note = $this->getFieldString('merge_note', $force);

        if (is_array($note))
        {
            $note = implode("\n", $note);
        }

        return $note;
    }

    /**
     * Get array of profile's phone number(s).
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <API_PhoneNumber> If none, returns empty array.
     * TODO Check if array needs processing.
     */
    public function getPhoneNumbers(bool $force = false): array
    {
        return $this->getFieldObj('phone_numbers', API_PhoneNumber::class, $force, isArray: true);
    }

    /**
     * Return array of profile-IDs of pending arrays for this profile.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <string> If none, returns empty array.
     * TODO Check if array needs processing.
     */
    public function getRequestedMerges(bool $force = false): array
    {
        return $this->getFieldStringArray('requested_merges', $force);
    }

    /**
     * Return array of profile-IDs of pending arrays for this profile.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <string> If none, returns empty array.
     */
    public function getUnions(bool $force = false): array
    {
        return $this->getFieldStringArray('unions', $force, isURL: true);
    }

    /**
     * Returns the Baptism event (if it exists), otherwise returns NULL.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return API_Event|NULL
     */
    public function getBaptism(bool $force = false): ?API_Event
    {
        return $this->getFieldObj('baptism', API_Event::class, $force);
    }

    /**
     * Returns the Birth event   (if it exists), otherwise returns NULL.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return API_Event|NULL
     */
    public function getBirth(bool $force = false): ?API_Event
    {
        return $this->getFieldObj('birth', API_Event::class, $force);
    }

    /**
     * Returns the Burial event  (if it exists), otherwise returns NULL.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return API_Event|NULL
     */
    public function getBurial(bool $force = false): ?API_Event
    {
        return $this->getFieldObj('burial', API_Event::class, $force);
    }

    /**
     * Returns the Death event   (if it exists), otherwise returns NULL. In very rare
     * situations does NOT return the event even if exists (this is a server bug).
     * Just call the function again.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return API_Event|NULL
     */
    public function getDeath(bool $force = false): ?API_Event
    {
        return $this->getFieldObj('death', API_Event::class, $force);
    }

    public function getCurrentResidence(bool $force = false): ?API_Location
    {
        return $this->getFieldObj('current_residence', API_Location::class, $force);
    }

    public function getMugshotURLs(bool $force = false): ?API_PhotoImageSizeMap
    {
        return $this->getFieldObj('mugshot_urls', API_PhotoImageSizeMap::class, $force);
    }

    //-----------------------------------------------------
    // Various Getters for the profile's name(s).

    /**
     * Get all name parts.
     * This function does NOT need a force parameter, because it always calls the Server.
     * @return API_Name
     */
    public function getFullName(): API_Name
    {
        $API_Wrap = API_Wrapper::getInstance();

        $FieldURL = '/' . $this->getID()
                    . '?fields=first_name,middle_name,last_name,maiden_name,suffix,display_name,title,nicknames';

        //--------------------------------------------------
        $nameArray = $API_Wrap->callAPI($FieldURL);
        //--------------------------------------------------

        if ($nameArray != null)
        {
            if (isset($nameArray ['first_name']))
            {
                $this->values ['first_name'] = $nameArray ['first_name'];
            }

            if (isset($nameArray ['middle_name']))
            {
                $this->values ['middle_name'] = $nameArray ['middle_name'];
            }

            if (isset($nameArray ['last_name']))
            {
                $this->values ['last_name'] = $nameArray ['last_name'];
            }

            if (isset($nameArray ['maiden_name']))
            {
                $this->values ['maiden_name'] = $nameArray ['maiden_name'];
            }

            if (isset($nameArray ['suffix']))
            {
                $this->values ['suffix'] = $nameArray ['suffix'];
            }

            if (isset($nameArray ['title']))
            {
                $this->values ['title'] = $nameArray ['title'];
            }

            if (isset($nameArray ['display_name']))
            {
                $this->values ['display_name'] = $nameArray ['display_name'];
            }

            if (isset($nameArray ['nicknames']))
            {
                $this->values ['nicknames'] = self::parseArrayOrCommaList($nameArray ['nicknames']);
            }
        }

        $fullName = API_Name::newFromArray($nameArray);

        return $fullName;
    }

    /**
     * Get name as displayed, i.e. the whole thing.
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->values ['name'] ?? null;
    }

    /**
     * Get names in specific language.
     * @param string $languageID  ISO-639 language codes (e.g. "en-US", "he", "sp").
     *                            User is responsible to ensure value is valid.
     * @param  bool  $force       Force an API call, to refresh the value.
     * @return API_Name|NULL
     * @see API_Profile
     */
    public function getNames(string $languageID, bool $force = false): ?API_Name
    {
        $allNames = null;
        if (!array_key_exists('names', $this->values))
        {
            $allNames = $this->getNamesAll($force);
        }
        else
        {
            $allNames = $this->values ['names'];
        }

        $langName = $allNames [$languageID] ?? null;

        return $langName;
    }

    /**
     * Get names in ALL languages.
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <API_Name>  of API_Name objects.
     */
    public function getNamesAll(bool $force = false): ?array
    {
        $allNames = [];
        if (array_key_exists('names', $this->values) && !$force)
        {
            $allNames = $this->values ['names'];
        }
        else
        {
            $allNames = $this->getFieldObj('names', API_Name::class, $force, isArray: true);
        }

        if (count($allNames) == 0)
        {
            // The get names API only returns a value if there are MORE than
            // one language (other than the default "en-US"). So if nothing
            // returned get the English name and wrap it in an array.
            $fullName             = $this->getFullName();
            $allNames [ "en-US" ] = $fullName;
        }

        return $allNames;
    }

    /**
     * Get array of profile's nicknames ("AKA").
     * @param  bool   $force  Force an API call, to refresh the value.
     * @return array <string>
     */
    public function getNicknames(bool $force = false): array
    {
        return $this->getFieldStringArray('nicknames', $force);
    }

    //-----------------------------------------------------
    // Getters for FLAG fields.

    /**
     * Return whether this object is a brief or full object.
     * @return bool
     * @see API_Profile::$isBrief for more details.
     */
    public function isBrief(): bool
    {
        return $this->isBrief;
    }

    public function isBig_tree(bool $force = false): bool
    {
        return $this->getFieldBool('big_tree', $force);
    }

    public function isBlockExists(bool $force = false): bool
    {
        return $this->getFieldBool('block_exists', $force);
    }

    public function isClaimed(bool $force = false): bool
    {
        return $this->getFieldBool('claimed', $force);
    }

    public function isDeleted(bool $force = false): bool
    {
        return $this->getFieldBool('deleted', $force);
    }

    public function isGet_email(bool $force = false): bool
    {
        return $this->getFieldBool('get_email', $force);
    }

    public function isAlive(bool $force = false): bool
    {
        return $this->getFieldBool('is_alive', $force);
    }

    public function isLocked(bool $force = false): bool
    {
        return $this->getFieldBool('locked', $force);
    }

    public function isMasterProfile(bool $force = false): bool
    {
        return $this->getFieldBool('master_profile', $force);
    }

    public function isMergePending(bool $force = false): bool
    {
        return $this->getFieldBool('merge_pending', $force);
    }

    public function isPublic(bool $force = false): bool
    {
        return $this->getFieldBool('public', $force);
    }

    //=====================================================

    /**
     * Define whether the object will initially include only the very basic
     * information, or the whole set of fields.
     * @param bool $isBrief
     *
     * @see API_Profile::$isBrief for more details.
     * @see API_Profile::BRIEF_FIELDS
     */
    private function setIsBrief(bool   $isBrief): void
    {
        $this->isBrief = $isBrief;
    }

    // These setter function only change the OBJECT. The server gets changed when update is called.
    // (that is why it does NOT matter that the "setIsAlive(FALSE)" comes after the setField calls).

    public function setAboutMe(?string $about_me): void
    {
        $this->setFieldString('about_me', $about_me);
    }

    /**
     * TODO There is a bug with this function that happens pretty rarely. I could not find it.
     * Not clear whether it's in the SDK or the Server. So if the set fails, call it a
     * second time. Keep in mind to call after each call. As the function does NOT have
     * a return value, the only way to know if it failed is to.... call the getter and
     * compare. Yeah not good.
     * try and find and fix the bug.
     * @see API_Profile::getCauseOfDeath
     * @see API_Profile::update
     */
    public function setCauseOfDeath(?string $cause_of_death): void
    {
        $this->setFieldString('cause_of_death', $cause_of_death);
        $this->setIsAlive(false);
    }

    public function setEmail(?string $email): void
    {
        $this->setFieldString('email', $email);
    }

    public function setGender(API_Gender  $gender): void
    {
        $this->setFieldString('gender', $gender->value);
    }

    public function setIsAlive(bool    $is_alive): void
    {
        $this->setFieldBool('is_alive', $is_alive);
    }

    /**
     * Curator ONLY?
     * TODO verify that flag can be set.
     */
    public function setLocked(bool    $locked): void
    {
        $this->setFieldBool('locked', $locked);
    }
    //     public function  setMergeNote    (?string $merge_note)     : void { $this->setFieldString ('merge_note',      $merge_note);     }

    public function setOccupation(?string $occupation): void
    {
        $this->setFieldString('occupation', $occupation);
    }

    public function setPublic(bool    $public): void
    {
        $this->setFieldBool('public', $public);
    }

    public function setBaptism(API_Event $baptism): void
    {
        $this->setFieldEvent('baptism', $baptism);
    }

    public function setBirth(API_Event $birth): void
    {
        $this->setFieldEvent('birth', $birth);
    }

    public function setBurial(API_Event $burial): void
    {
        $this->setFieldEvent('burial', $burial);
        $this->setIsAlive(false);
    }

    public function setDeath(API_Event $death): void
    {
        $this->setFieldEvent('death', $death);
        $this->setIsAlive(false);
    }

    public function setCurrentResidence(API_Location $current): void
    {
        $this->setFieldObject('current_residence', $current);
    }

    /** @param array <string> $nicknames */
    public function setNicknames(array  $nicknames): void
    {
        $this->setFieldArray('nicknames', $nicknames);
    }

    public function setFullName(
        ?string $first_name = null,
        ?string $middle_name = null,
        ?string $last_name = null,
        ?string $maiden_name = null,
        ?string $suffix = null,
        ?string $title = null,
        ?string $display_name = null,
    ): void {
        if ($first_name != null)
        {
            $this->setFieldString('first_name', $first_name);
        }

        if ($middle_name != null)
        {
            $this->setFieldString('middle_name', $middle_name);
        }

        if ($last_name != null)
        {
            $this->setFieldString('last_name', $last_name);
        }

        if ($maiden_name != null)
        {
            $this->setFieldString('maiden_name', $maiden_name);
        }

        if ($suffix != null)
        {
            $this->setFieldString('suffix', $suffix);
        }

        if ($title != null)
        {
            $this->setFieldString('title', $title);
        }

        if ($display_name != null)
        {
            $this->setFieldString('display_name', $display_name);
        }
    }

    /**
     * Set names in specific language.
     * @param string    $languageID  ISO-639 language codes (e.g. "en-US", "he", "sp").
     *                               User is responsible to ensure value is valid.
     * @param API_Name  $name        Names.
     */
    public function setNames(string $languageID, API_Name $name): void
    {
        if (!array_key_exists('names', $this->values))
        {
            $this->values ['names'] = [];
        }

        $this->values ['names'][$languageID] = $name;

        $this->setSetFields('names');
    }
}
