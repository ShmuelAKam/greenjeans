<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

class API_UnionMocker extends API_BaseMocker
{
    //======================================================

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    //======================================================
    // API calls.

    /**
     * GET /union
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        $union = $this->getSubjectObject();

        if (isset($API_Params ['id']))
        {
            $id    = $API_Params ['id'];
            $union = $this->getTestObject($id);

            if ($union == [])
            {
                // No test-object exists for this ID.

                $union = $this->createObjectByID($id);
            }

            $this->setSubjectObject($union);
        }

        // "Move" the data in the foster and adopted lists.
        // This does NOT change the union test-object, because that was already set.

        if (isset($union ['foster']))
        {
            $union ['foster_children'] = $union ['foster'];
            unset($union ['foster']);
        }

        if (isset($union ['adopt']))
        {
            $union ['adopted_children'] = $union ['adopt'];
            unset($union ['adopt']);
        }

        return $union;
    }

    /**
     * POST /union/add_child
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_child(array $API_Params, array $urlActions = []): array
    {
        // Get optional relationship modifier and clear from object, because we
        // don't want it to become a field in the profile.

        $modifier = $API_Params ['relationship_modifier'] ?? '';
        unset($API_Params ['relationship_modifier']);

        // Create child array (from API parameters).

        /** @var API_ProfileMocker $profileMocker */
        $profileMocker = $this->getMockerByClass(API_ProfileMocker::class);
        $childProfile  = $profileMocker->createObject($API_Params, isNew: true);

        $this->setTestObject($childProfile, 'profile');

        // Add child to union object.

        $union = $this->getSubjectObject();

        // Add the child to the union (first verify not already there).

        $childID = $childProfile['id'];

        if (!in_array($childID, $union['children'] ?? []))
        {
            $union['children'][] = $childID;
        }

        if ($modifier != '')
        {
            $fieldName = API_ProfileMocker::getChildFieldName($modifier);

            if (!in_array($childID, $union[$fieldName] ?? []))
            {
                $union[$fieldName][] = $childID;
            }
        }

        $this->setSubjectObject($union);
        $this->setTestObject($childProfile, 'profile');

        return $childProfile;
    }

    /**
     * POST /union/add_partner
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function add_partner(array $API_Params, array $urlActions = []): array
    {
        // Create array of partner parameters.

        /** @var API_ProfileMocker $profileMocker */
        $profileMocker  = $this->getMockerByClass(API_ProfileMocker::class);
        $partnerProfile = $profileMocker->createObject($API_Params, isNew: true);

        $this->setTestObject($partnerProfile, 'profile');

        // Add partner to union object.

        $union                = $this->getSubjectObject();
        $union ['partners'][] = $partnerProfile ['id'];
        $this->setSubjectObject($union);

        return $partnerProfile;
    }

    /**
     * POST /union/update
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function update(array $API_Params, array $urlActions = []): array
    {
        $union    = $this->getSubjectObject();
        $modified = false;

        if (isset($API_Params ['marriage']))
        {
            $union ['marriage'] = $API_Params ['marriage'];
            $union ['status']   = 'spouse';
            $modified           = true;
        }

        if (isset($API_Params ['divorce']))
        {
            $union ['divorce'] = $API_Params ['divorce'];
            $union ['status']  = 'ex_spouse';
            $modified          = true;
        }

        if ($modified)
        {
            $this->setSubjectObject($union);
        }

        return $union;
    }

    //------------------------------------------------------

    /**
     * @param  array <string, mixed|array <string, mixed>> $partner1
     * @param  array <string, mixed|array <string, mixed>> $partner2
     * @param  array <string, mixed|array <string, mixed>> $marriage
     * @param  array <string, mixed|array <string, mixed>> $divorce
     * @return array <string, mixed|array <string, mixed>>
     */
    public static function makeUnionPartner(
        array  $partner1,
        ?array $partner2,
        ?array $marriage,
        ?array $divorce,
    ): array {
        $unionID = FactoryBase::generatePrefixedId('union');
        $union   = [
            'id'        => $unionID,
            'partners'  => [
                $partner1 ['id'],
            ],
            'status'    => 'spouse',
            'url'       => 'https://www.geni.com/api/' . $unionID,
        ];

        if ($partner2 != null)
        {
            $union ['partners'][] = $partner2 ['id'];
        }

        if ($marriage != null)
        {
            $union ['marriage'] = $marriage;
        }

        if ($divorce != null)
        {
            $union ['divorce'] = $divorce;
            $union ['status']  = 'ex_spouse';
        }

        //         $union ['children']
        //         $union ['adopted_children']
        //         $union ['foster_children']

        return $union;
    }

    //------------------------------------------------------

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array <string, mixed|array <string, mixed>>
     */
    public function createObjectByID(string $ID): array
    {
        $union = [
            'id'               => $ID,
            'partners'         => [],
            'children'         => [],
            'adopted_children' => [],
            'foster_children'  => [],
            'status'           => '',
            'marriage'         => [],    // Event
            'divorce'          => [],    // Event
        ];

        return $union;
    }
}
