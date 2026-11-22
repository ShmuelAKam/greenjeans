<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * Read-only struct containing information about SINGLE step in relationship path
 * between two people as returned by profile/path-to. It does NOT have any API calls
 * of its own.
 *
 * Links to API documentation:
 *      API_Relation - https://www.geni.com/platform/developer/help/api?path=relation&version=1
 */
class API_Relation
{
    /**
     * The profile's name as it appears on the site to the current user.
     * @var string
     */
    private string $name;

    /**
     * English gendered relationship to previous profile in the array (if any).
     * One of: father, mother, parent, brother, sister, sibling, husband,
     * wife, spouse, son, daughter, child.
     * @var string
     */
    private string $relation;

    /**
     * URL to access profile through the API
     * @var string
     */
    private string $url;

    //------------------------------------------------------

    /**
     * Constructor.
     */
    private function __construct() {}

    /**
     * Update current object with values returned from the Server.
     * It does NOT validate the data fields, because that would
     * reject already existing data.
     *
     * @param array<string, string> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        if (isset($arrayValues ['name']))
        {
            $this->name = $arrayValues ['name'];
        }

        if (isset($arrayValues ['relation']))
        {
            $this->relation = $arrayValues ['relation'];
        }

        if (isset($arrayValues ['url']))
        {
            $this->url = $arrayValues ['url'];
        }
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, string> $arrayValues
     * @return API_Relation
     */
    public static function newFromArray(array $arrayValues): API_Relation
    {
        $relationObj = new API_Relation();

        $relationObj->updateFromArray($arrayValues);

        return $relationObj;
    }

    /**
     * Creates array to send to Server in API calls. This object is never sent. Hmmm.
     * @return array <string, string>
     */
    public function makeParamArray  (): array
    {
        $paramArray = [
            'name'     => $this->name,
            'relation' => $this->relation,
            'url'      => $this->url,
        ];

        return $paramArray;
    }

    //------------------------------------------------------
    // Getters.

    public function getName(): string
    {
        return $this->name;
    }

    public function getRelation(): string
    {
        return $this->relation;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
