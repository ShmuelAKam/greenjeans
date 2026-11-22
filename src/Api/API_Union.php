<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;

/**
 * The API_Union object exist only in the context of an API_Profile object.
 * As such it can not be created independently. This is why the constructor
 * and related functions are private.
 *
 * The ONLY way to create an API_Union object is by using the API_Union::get
 * function, which takes a union ID value. The only way to get a union ID is
 * by using the API_Profile::getUnions function.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=union&version=1 API_Union}
 */
class API_Union extends API_Base
{
    /**
     * The union's ID.
     * @var string
     */
    private string $ID;

    /**
     * Array of the partners IDs in the union (urls or ids, if requested).
     * @var array<string>
     */
    private array  $partners;

    /**
     * Array of ALL children IDs in the union (urls or ids, if requested).
     * @var array<string>
     */
    private array  $children;

    /**
     * Subset of the children ID array, indicating which children are adopted.
     * @var array<string>
     */
    private array  $adopted_children;

    /**
     * Subset of the children ID array, indicating which children are foster.
     * @var array<string>
     */
    private array  $foster_children;

    /**
     * The status of the union (spouse|ex_spouse).
     * @var string
     */
    private string $status;

    /**
     * Marriage date and location.
     * @var API_Event|NULL
     */
    private ?API_Event $marriage;

    /**
     * Divorce date and location.
     * @var API_Event|NULL
     */
    private ?API_Event $divorce;

    //------------------------------------------------------

    /**
     * Constructs EMPTY object. Object is set by the get API call.
     */
    private function __construct()
    {
        parent::__construct();

        $this->ID     = '';
        $this->status = '';

        $this->partners         = [];
        $this->children         = [];
        $this->adopted_children = [];
        $this->foster_children  = [];

        $this->marriage = null;
        $this->divorce  = null;
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     * @param array <string, string|array<string, string|array<string, string>>> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        // This sets ONLY fields that were changed.

        $this->ID     = $arrayValues ['id'] ?? $this->ID;
        $this->status = $arrayValues ['status'] ?? $this->status;

        if (isset($arrayValues ['marriage']))
        {
            $this->marriage = API_Event::newFromArray($arrayValues ['marriage']);
        }

        if (isset($arrayValues ['divorce']))
        {
            $this->divorce = API_Event::newFromArray($arrayValues ['divorce']);
        }

        // Arrays of strings.
        $this->partners         = $arrayValues ['partners'] ?? $this->partners;
        $this->children         = $arrayValues ['children'] ?? $this->children;
        $this->adopted_children = $arrayValues ['adopted_children'] ?? $this->adopted_children;
        $this->foster_children  = $arrayValues ['foster_children'] ?? $this->foster_children;
    }

    /**
     * Factory method: Create instance from array
     * @param array <string, string|array<string, string|array<string, string>>> $arrayValues
     */
    public static function newFromArray(array $arrayValues): API_Union
    {
        $union = new API_Union();

        $union->updateFromArray($arrayValues);

        return $union;
    }

    //=====================================================

    /**
     * Call API to add child or partner. Returns ID of added profile.
     * @param API_Profile $person
     * @param bool        $isChild
     * @param string      $modifier
     * @return string     ID of added profile. NULL if add failed.
     */
    private function add(API_Profile &$person, bool $isChild, string $modifier = ''): ?string
    {
        $API_Wrap = API_Wrapper::getInstance();

        $addParamArray = $person->makeParamArray(true);

        if ($modifier != '')
        {
            $addParamArray ['relationship_modifier'] = $modifier;
        }

        if ($isChild)
        {
            $addURL = '/' . $this->getID() . '/add-child';
        }
        else
        {
            $addURL = '/' . $this->getID() . '/add-partner';
        }

        // Send URL to API.
        // XXX Returns the properties of the person added. This is NOT what
        // XXX the documentation says.

        // -------------------------------
        $addReplyArray = $API_Wrap->callAPI_POST($addURL, $addParamArray);
        // -------------------------------

        // Process response.

        $addID = null;

        if (isset($addReplyArray ['id']))
        {
            $person->updateFromArray($addReplyArray);

            $addID = $person->getID();

            // XXX The API should be returning the union parameters!
            // $this->updateFromArray ($addReplyArray);
        }

        return $addID;
    }

    //=====================================================
    // API calls.

    /**
     * Get ALL Union fields from API.
     */
    public static function get(string $ID): ?API_Union
    {
        $API_Wrap = API_Wrapper::getInstance();

        // Get only the IDs of the SUB-objects (e.g. children & spouses),
        // and not the full profiles.

        $getURL = "/$ID?only_ids=TRUE";

        // -------------------------------
        $unionArray = $API_Wrap->callAPI($getURL);
        // -------------------------------

        $union = null;
        if ($unionArray != null && array_key_exists('id', $unionArray))
        {
            $union = API_Union::newFromArray($unionArray);
        }

        return $union;
    }

    /**
     * Adds child to union.
     * @return string ID of the added child. NULL if add failed.
     */
    public function addChild(API_Profile &$child, string $modifier = ''): ?string
    {
        $validModifiers = [ '', 'foster', 'adopt' ];

        $childID = null;

        if (in_array($modifier, $validModifiers))
        {
            // -------------------------------
            $childID = $this->add($child, isChild: true, modifier: $modifier);
            // -------------------------------

            $this->children [] = $childID;

            if ($modifier === 'adopt')
            {
                $this->adopted_children [] = $childID;
            }
            elseif ($modifier === 'foster')
            {
                $this->foster_children  [] = $childID;
            }
        }
        else
        {
            // TODO Throw something.
        }

        return $childID;
    }

    /**
     * Adds partner to union.
     * @return string ID of the added partner. NULL if add failed.
     */
    public function addPartner(API_Profile &$partner): ?string
    {
        $partnerID         = $this->add($partner, isChild: false, modifier: '');
        $this->partners [] = $partnerID;

        return $partnerID;
    }

    /**
     * Updates union.
     */
    public function update(?API_Event $marriage = null, ?API_Event $divorce = null): bool
    {
        $API_Wrap = API_Wrapper::getInstance();

        if ($marriage == null && $divorce == null)
        {
            return false;
        }

        $updateParams = [];

        if ($marriage != null)
        {
            $updateParams ['marriage'] = $marriage->makeParamArray();
        }

        if ($divorce != null)
        {
            $updateParams ['divorce'] = $divorce->makeParamArray();
        }

        $updateURL = '/' . $this->getID() . '/update';

        // -------------------------------
        $updateArray = $API_Wrap->callAPI_POST($updateURL, $updateParams);
        // -------------------------------

        if (array_key_exists('id', $updateArray))
        {
            $this->updateFromArray($updateArray);

            return true;
        }
        else
        {
            return false;
        }
    }

    //------------------------------------------------------

    /**
     * Returns the ID of the partner that is NOT the ID in the parameter.
     * @param  string $partner1 ID of one partner.
     * @return string           ID of the other partner.
     */
    public function getOtherPartner(string $partner1): string
    {
        $partner1Array = [ $partner1 ];

        $partnerIDArray = array_diff($this->partners, $partner1Array);
        $partner2ID     = $partnerIDArray [array_key_first($partnerIDArray)];

        return $partner2ID;
    }

    //=====================================================
    // Getter functions for union values.
    // There are NO Setter functions. All changes to the object are made
    // through the API functions.

    public function getID(): string
    {
        return $this->ID;
    }

    /** @return array<string> */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** @return array<string> */
    public function getAdoptedChildren(): array
    {
        return $this->adopted_children;
    }

    /** @return array<string> */
    public function getFosterChildren(): array
    {
        return $this->foster_children;
    }

    /** @return array<string> */
    public function getPartners(): array
    {
        return $this->partners;
    }

    public function getMarriage(): ?API_Event
    {
        return $this->marriage;
    }

    public function getDivorce(): ?API_Event
    {
        return $this->divorce;
    }

    /**
     * @return string 'spouse' or 'ex_spouse'.
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
