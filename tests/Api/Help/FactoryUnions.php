<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\API_Union;

class FactoryUnions extends FactoryBase
{
    /**
     * @var array<string>
     */
    private array $children;

    /**
     * @var array<string>
     */
    private array $adopted_children;

    /**
     * @var array<string>
     */
    private array $foster_children;

    public function __construct()
    {
        // No Shredder needed.

        parent::__construct(ShredderDummy::class);
    }

    public function __destruct() {}

    public function reset(): void
    {
        $this->children         = [];
        $this->adopted_children = [];
        $this->foster_children  = [];
    }

    /**
     * @return array <string>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array <string>
     */
    public function getAdopted(): array
    {
        return $this->adopted_children;
    }

    /**
     * @return array <string>
     */
    public function getFoster(): array
    {
        return $this->foster_children;
    }

    public function addChild(string $ID): void
    {
        $this->children[] = $ID;
    }

    public function addAdopted(string $ID): void
    {
        $this->adopted_children[] = $ID;
    }

    public function addFoster(string $ID): void
    {
        $this->foster_children[] = $ID;
    }

    // ------------------------------------------------------

    /**
     * Create profile and add it as child to the $base profile on the Server.
     * This creates a union. Reduces the count for the relevnt type of child.
     *
     * @param API_Profile $parent
     * @param int $biological
     * @param int $adopted
     * @param int $foster
     */
    public function addFirstChild(API_Profile &$parent, int &$biological = 0, int &$adopted = 0, int &$foster = 0): ?API_Profile
    {
        global $profileFactory;

        if ($biological == 0 && $adopted == 0 && $foster == 0)
        {
            // Error situation.
            return null;
        }

        list($child, ) = $profileFactory->makeRandomShell();

        if ($biological > 0)
        {
            $child = $parent->addChild($child);

            $this->addChild($child->getID());
            $biological--;
        }
        elseif ($adopted > 0)
        {
            $child = $parent->addChild($child, 'adopt');

            $this->addAdopted($child->getID());
            $adopted--;
        }
        elseif ($foster > 0)
        {
            $child = $parent->addChild($child, 'foster');

            $this->addFoster($child->getID());
            $foster--;
        }

        return $child;
    }

    /**
     * Log all profiles created for the Union (except for the base partner,
     * who is already logged by the makeRandomProfile call).
     *
     * @param API_Union $union
     *            Object to log from.
     * @param API_Profile $partner
     */
    private function logUnion(API_Union $union, ?API_Profile $partner): void
    {
        global $profileFactory;

        if ($partner != null)
        {
            $profileFactory->logCreate($partner->getID());
        }

        $children = $union->getChildren();

        foreach ($children as $childID)
        {
            $profileFactory->logCreate($childID);
        }
    }

    // ------------------------------------------------------

    /**
     * Create union
     * A union MUST contain at least another partner, or a child.
     *
     * @param bool $addPartner
     * @param bool $empty       When TRUE this function does NOT get any union/ API.
     *                          So it returns the union-ID instead.
     * @param int  $biological
     * @param int  $adopted
     * @param int  $foster
     * @return array<string|API_Profile|API_Union|NULL>|NULL contains the created UnionID,
     *      and the two partners.
     */
    public function makeUnion(bool $addPartner, bool $empty = false, int $biological = 0, int $adopted = 0, int $foster = 0): ?array
    {
        global $profileFactory;

        $makeChildren = false;
        if ($biological > 0 || $adopted > 0 || $foster > 0)
        {
            $makeChildren = true;
        }

        if (!$addPartner && !$makeChildren)
        {
            // Can't create an empty union (No partner & no children). Error situation.
            return null;
        }

        $parentProfile = $profileFactory->makeRandomProfile();

        if ($addPartner)
        {
            list($partnerShell, ) = $profileFactory->makeRandomShell();
            $partner              = $parentProfile->addPartner($partnerShell);
        }
        else
        {
            // NO partner. Need to add a child to the $base profile to
            // create the union object on the profile object on the Server.

            $partner = null;

            $this->addFirstChild($parentProfile, $biological, $adopted, $foster);
        }

        // After adding a partner OR a child, the $parentProfile profile has a union.

        $unionArray = $parentProfile->getUnions(force: true);
        $unionID    = reset($unionArray);

        $union = null;

        if (!$empty)
        {
            $union = API_Union::get($unionID);

            if ($makeChildren)
            {
                $this->makeChildren($union, $biological);
                $this->makeChildren($union, $adopted, 'adopt');
                $this->makeChildren($union, $foster, 'foster');
            }

            $this->logUnion($union, $partner);
        }

        return [
            $union,         // API_Union or NULL
            $unionID,       // string
            $parentProfile, // API_Profile
            $partner        // API_Profile or NULL
        ];
    }

    /**
     * Create and add children to the union object (updates the server).
     *
     * @param API_Union $union Object to update.
     * @param int       $count Number of children to add.
     * @param string    $modifier Category of children: none, 'adopt' or 'foster'.
     * @return array <string> IDs of the children.
     */
    public function makeChildren(API_Union &$union, int $count, string $modifier = ''): array
    {
        global $profileFactory;

        $childrenIDs = [];

        for ($i = 0; $i < $count; $i++)
        {
            list($childShell, ) = $profileFactory->makeRandomShell();

            $childID       = $union->addChild($childShell, $modifier);
            $childrenIDs[] = $childID;

            $profileFactory->logCreate($childID);
        }

        return $childrenIDs;
    }
}
