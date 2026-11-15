<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

use ShmuelAKam\Greenjeans\Api\API_Base;

/**
 * API_Name - Describes the FULL name of a profile.
 * This basic data structure does NOT have API of its own.
 * {@link API_Name}
 */
class API_Name
{
    // TODO Where is prefix?

    /**
     * Profile's display name
     * @var string|NULL
     */
    private ?string $display_name;

    /**
     * Profile's first name
     * @var string|NULL
     */
    private ?string $first_name;

    /**
     * Profile's last name
     * @var string|NULL
     */
    private ?string $last_name;

    /**
     * Profile's maiden name
     * @var string|NULL
     */
    private ?string $maiden_name;

    /**
     * Profile's middle name
     * @var string|NULL
     */
    private ?string $middle_name;

    /**
     * "Also known as" (AKA) nicknames.
     * @var string[]
     */
    private array   $nicknames;

    /**
     * Profile's title
     * @var string|NULL
     */
    private ?string $title;

    /**
     * Profile's suffix
     * @var string|NULL
     */
    private ?string $suffix;

    //------------------------------------------------------

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Reset all members. ALL of them are optional.

        $this->display_name = null;
        $this->first_name   = null;
        $this->last_name    = null;
        $this->maiden_name  = null;
        $this->middle_name  = null;
        $this->nicknames    = [];
        $this->suffix       = null;
        $this->title        = null;
    }

    //------------------------------------------------------

    /**
     * Update current object with values returned from the Server.
     * It does NOT validate the data fields, because that would
     * reject already existing data.
     *
     * @param array<string, string> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        // This sets ONLY fields that were changed.

        if (isset($arrayValues ['display_name']))
        {
            $this->display_name = $arrayValues ['display_name'];
        }

        if (isset($arrayValues ['first_name']))
        {
            $this->first_name = $arrayValues ['first_name'];
        }

        if (isset($arrayValues ['last_name']))
        {
            $this->last_name = $arrayValues ['last_name'];
        }

        if (isset($arrayValues ['maiden_name']))
        {
            $this->maiden_name = $arrayValues ['maiden_name'];
        }

        if (isset($arrayValues ['middle_name']))
        {
            $this->middle_name = $arrayValues ['middle_name'];
        }

        if (isset($arrayValues ['suffix']))
        {
            $this->suffix = $arrayValues ['suffix'];
        }

        if (isset($arrayValues ['title']))
        {
            $this->title = $arrayValues ['title'];
        }

        if (isset($arrayValues ['nicknames']))
        {
            // Convert value received to array.

            $nicknamesArray = API_Base::parseArrayOrCommaList($arrayValues ['nicknames']);

            $this->nicknames = $nicknamesArray;
        }
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, string> $arrayValues
     * @return API_Name
     */
    public static function newFromArray(array $arrayValues): API_Name
    {
        $name = new API_Name();

        $name->updateFromArray($arrayValues);

        return $name;
    }

    /**
     * Creates array to send to Server in API calls.
     * @return array<string>
     */
    public function makeParamArray(): array
    {
        $paramArray = [];

        if (isset($this->display_name))
        {
            $paramArray ['display_name'] = $this->display_name;
        }

        if (isset($this->first_name))
        {
            $paramArray ['first_name'  ] = $this->first_name;
        }

        if (isset($this->last_name))
        {
            $paramArray ['last_name'   ] = $this->last_name;
        }

        if (isset($this->maiden_name))
        {
            $paramArray ['maiden_name' ] = $this->maiden_name;
        }

        if (isset($this->middle_name))
        {
            $paramArray ['middle_name' ] = $this->middle_name;
        }

        if (isset($this->suffix))
        {
            $paramArray ['suffix'      ] = $this->suffix;
        }

        if (isset($this->title))
        {
            $paramArray ['title'       ] = $this->title;
        }

        if (count($this->nicknames) > 0)
        {
            // Convert array into comma separated string.

            $paramArray ['nicknames' ] = implode(',', $this->nicknames);
        }

        return $paramArray;
    }

    //------------------------------------------------------
    // Setters & Getters.

    public function setDisplayName(string $display_name): void
    {
        $this->display_name = $display_name;
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    public function setMaidenName(string $maiden_name): void
    {
        $this->maiden_name = $maiden_name;
    }

    public function setMiddleName(string $middle_name): void
    {
        $this->middle_name = $middle_name;
    }

    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Set ALL parts of full name.
     * Will RESET fields that aren't given a value in the parameters. If you want
     * to set (or add) only some of the name parts, and keep the rest, use the
     * above setters for each name part.
     * @param string $first_name
     * @param string $middle_name
     * @param string $last_name
     * @param string $maiden_name
     * @param string $suffix
     * @param string $title
     * @param string $display_name
     */
    public function setFullName(
        ?string $first_name = null,
        ?string $middle_name = null,
        ?string $last_name = null,
        ?string $maiden_name = null,
        ?string $suffix = null,
        ?string $title = null,
        ?string $display_name = null,
    ): void {
        $this->first_name   = $first_name;
        $this->middle_name  = $middle_name;
        $this->last_name    = $last_name;
        $this->maiden_name  = $maiden_name;
        $this->suffix       = $suffix;
        $this->title        = $title;
        $this->display_name = $display_name;
    }

    /**
     * @param array<string> $nicknames
     */
    public function setNicknames(?array $nicknames): void
    {
        $this->nicknames = $nicknames ?? [];
    }

    //------------------------------------------------------

    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getMaidenName(): ?string
    {
        return $this->maiden_name;
    }

    public function getMiddleName(): ?string
    {
        return $this->middle_name;
    }

    /** @return array<string>|NULL */
    public function getNicknames(): ?array
    {
        return $this->nicknames;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
