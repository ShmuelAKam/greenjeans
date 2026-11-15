<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

/**
 * API_Revision - Is a read-only struct containing information about revisions.
 * The ONLY API call it has is Get.
 *
 * It is NOT CLEAR what purpose this object serves, because I can't find ANY reference to it
 * in other classes. So where do revision IDs come from?
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=revision&version=1 API_Revision}
 */
class API_Revision extends API_Base
{
    /**
     * Action of the revision.
     * @var string
     */
    private string $action;

    /**
     * Date of the revision in the local date.
     * @var string
     */
    private string $date_local;

    /**
     * Revision GUID.
     * @var string
     */
    private string $GUID;

    /**
     * Revision ID.
     * @var string
     */
    private string $ID;

    /**
     * HTML story containing full revision description.
     * @var string
     */
    private string $story;

    /**
     * Time of the revision in the local time.
     * @var string
     */
    private string $time_local;

    /**
     * Server time of the revision.
     * @var string
     */
    private string $timestamp;

    //------------------------------------------------------

    /**
     * Constructor.
     */
    private function __construct()
    {
        parent::__construct();

        $this->action     = '';
        $this->date_local = '';
        $this->GUID       = '';
        $this->ID         = '';
        $this->story      = '';
        $this->time_local = '';
        $this->timestamp  = '';
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     * @param array<string, string> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        $this->action     = $arrayValues['action'] ?? '';
        $this->date_local = $arrayValues['date_local'] ?? '';
        $this->GUID       = $arrayValues['guid'] ?? '';
        $this->ID         = $arrayValues['id'] ?? '';
        $this->story      = $arrayValues['story'] ?? '';
        $this->time_local = $arrayValues['time_local'] ?? '';
        $this->timestamp  = $arrayValues['timestamp'] ?? '';
    }

    /**
     * Factory method: Create instance from array
     * @param array<string, string> $arrayValues
     */
    public static function newFromArray(array $arrayValues): API_Revision
    {
        $revision = new API_Revision();

        $revision->updateFromArray($arrayValues);

        return $revision;
    }

    //------------------------------------------------------
    // The only API call.

    /**
     * Get revision information from Server.
     *
     * @param string $ID
     *
     * @return NULL|API_Revision
     */
    public static function get(string $ID): ?API_Revision
    {
        $API_Wrap = API_Wrapper::getInstance();

        $GetID_URL = "/revision-$ID";

        // -------------------------------
        $RevisionArray = $API_Wrap->callAPI($GetID_URL);
        // -------------------------------

        $revision = null;
        if ($RevisionArray != null && array_key_exists('id', $RevisionArray))
        {
            $revision = API_Revision::newFromArray($RevisionArray);
        }

        return $revision;
    }

    //------------------------------------------------------
    // Getters.

    public function getAction(): string
    {
        return $this->action;
    }

    public function getDateLocal(): string
    {
        return $this->date_local;
    }

    public function getGUID(): string
    {
        return $this->GUID;
    }

    public function getID(): string
    {
        return $this->ID;
    }

    public function getStory(): string
    {
        return $this->story;
    }

    public function getTimeLocal(): string
    {
        return $this->time_local;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }
}
