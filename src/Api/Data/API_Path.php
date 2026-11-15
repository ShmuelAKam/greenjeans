<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * Read-only struct containing information about COMPLETE relationship path between
 * two people as returned by profile/path-to. It does NOT have any API calls.
 *
 * {@link ??? Unclear where this object comes from. It is NOT connected to API_Path}
 *           API_Relation or the Profile path-to call. I know that I did not
 *           invent it myself.
 */
class API_Path
{
    private string $inlaw_distance;
    private string $relationship;
    private string $status;
    private string $step_count;
    private string $url;

    /**
     * Array of API_Relation single step objects.
     * @var array<API_Relation>
     */
    private array  $relations;

    //------------------------------------------------------

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->inlaw_distance = '';
        $this->relationship   = '';
        $this->status         = '';
        $this->step_count     = '';
        $this->url            = '';

        $this->relations = [];
    }

    /**
     * Update current object with values returned from the Server.
     * It does NOT validate the data fields, because that would
     * reject already existing data.
     *
     * @param array<string, string|array<string, string>> $arrayValues
     * @param array<string, string|array<int, array<string, string>>> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        $this->inlaw_distance = $arrayValues ['inlaw_distance'] ?? '';
        $this->relationship   = $arrayValues ['relationship'] ?? '';
        $this->status         = $arrayValues ['status'] ?? '';
        $this->step_count     = $arrayValues ['step_count'] ?? '';
        $this->url            = $arrayValues ['url'] ?? '';

        if (isset($arrayValues['relations']) && is_array($arrayValues['relations']))
        {
            $pathSteps = [];

            foreach ($arrayValues['relations'] as $step)
            {
                $pathSteps[] = API_Relation::newFromArray($step);
            }

            $this->relations = $pathSteps;
        }
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, string|array<string, string>> $arrayValues
     * @return API_Path
     */
    public static function newFromArray(array $arrayValues): API_Path
    {
        $pathObj = new API_Path();
        $pathObj->updateFromArray($arrayValues);

        return $pathObj;
    }

    //------------------------------------------------------
    // Getters (object is read-only).

    public function getInlaw_distance(): string
    {
        return $this->inlaw_distance;
    }

    /** @return array<API_Relation> */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getRelationship(): string
    {
        return $this->relationship;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStep_count(): string
    {
        return $this->step_count;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
