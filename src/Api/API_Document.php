<?php

namespace ShmuelAKam\Greenjeans\Api;

use ShmuelAKam\Greenjeans\Api\Core\API_ListBase;
use ShmuelAKam\Greenjeans\Api\Core\API_ListObjects;
use ShmuelAKam\Greenjeans\Api\Core\API_ListProfiles;
use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_DocumentImageSizeMap;

/**
 * API_Document - describes a document, including dates & location.
 * This class derives from {@link API_MediaBase}, which implements most of its API calls.
 * Once created most parameters of the document can NOT be changed. The few
 * changes allowed are through the {@link API_Document::update} function.
 * The object also includes three lists of related objects:
 *
 *      o   User comments on the document. See {@link API_Document::comment}
 *
 *      o   Projects the document is associated with. There is NO API to
 *          modify this list. It can only be done via website.
 *
 *      o   Profiles (i.e. people) "tagged" in the document.
 *          See {@link API_Document::tag} and {@link API_Document::untag}.
 *
 * See the comment for {@link API_Document::add} regarding the two categories
 * of documents that can be created.
 *
 * The ONLY way to update the labels on the document is by using {@link API_Document::update}.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=document&version=1 API_Document}
 */
class API_Document extends API_MediaBase
{
    /**
     * The document's labels (sent from Server as comma separated list).
     * @var array <string>
     */
    private array   $labels;

    /**
     * Number of pages in the document
     * @var int|NULL
     */
    private ?int    $page_count;

    /**
     * URLs to the document's sizes
     * @var API_DocumentImageSizeMap|NULL
     */
    private ?API_DocumentImageSizeMap $sizes;

    /**
     * Source URL for the document
     * @var string|NULL
     */
    private ?string $source_url;

    /**
     * Text to create document from
     * @var string|NULL
     */
    private ?string $text;

    //-------------------------------------------------
    // Relational ID Collections

    /**
     * List of project IDs.
     * @var API_ListObjects|NULL
     */
    private ?API_ListObjects $projects;

    //======================================================

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->labels     = [];
        $this->page_count = null; // int
        $this->sizes      = null; // API_DocumentImageSizeMap
        $this->source_url = null;
        $this->text       = null;

        // This member is an API_ListObjects object, and
        // is set to NULL until the specific API call are made.

        $this->projects = null;
    }

    /**
     * Hydration logic: Set fields based on API or Database array keys.
     */
    public function updateFromArray(array $arrayValues, bool $fromDB = false): void
    {
        parent::updateFromArray($arrayValues, $fromDB);

        // This sets ONLY fields that were changed.

        $this->page_count = $arrayValues['page_count'] ?? $this->page_count;

        if (isset($arrayValues['source_url']))
        {
            $this->source_url = urldecode($arrayValues['source_url']);
        }

        if (isset($arrayValues['text']))
        {
            // API uses 'text'
            $this->text = $arrayValues['text'];
        }

        //--------------------------------------------------
        // Object fields.

        if (isset($arrayValues['sizes']))
        {
            $this->sizes = API_DocumentImageSizeMap::newFromArray($arrayValues['sizes']);
        }

        //------------------------------------------------------------
        // Lists.

        // Handle Tags/Labels (Comma-separated or Arrays)
        if (!empty($arrayValues['labels']))
        {
            // Is an array or a comma separated list.

            $this->labels = self::parseArrayOrCommaList($arrayValues['labels']);
            $this->setLoaded('labels');
        }

        if ($fromDB)
        {
            // The $projects and $comments fields are NOT sent as part of the generic API
            // reply. They must be requested separately, using the specific API calls:
            // getProjects/ getComments.
            // So this was added here for the persistence layer, which can add these values
            // in order to restore the projects /comments from the DB.

            if (isset($arrayValues ['comments']) && count($arrayValues ['comments']) > 0)
            {
                $listName = 'API_Document/comments';

                $this->comments = API_ListObjects::newFromDB($arrayValues ['comments'], API_Comment::class, $listName);
                $this->setLoaded('comments');
            }

            if (isset($arrayValues ['projects']) && count($arrayValues ['projects']) > 0)
            {
                $listName = 'API_Document/projects';

                $this->projects = API_ListObjects::newFromDB($arrayValues ['projects'], API_Project::class, $listName);
                $this->setLoaded('projects');
            }
        }
    }

    // =====================================================
    // API calls.

    /**
     * Add new document file.
     * It is possible to create two types of file:
     *
     *      o   From a binary blob. This requires using the following 3 parameters:
     *          $file, $file_name and $content_type.

     *      o   File created from simple $text. In which case $file MUST be NULL.
     *
     * @param string    $title        Document's title. The ONLY mandatory parameter.
     * @param string    $content_type Content type of the file, required if file is provided.
     * @param API_Date  $date         Document's date in JSON form.
     * @param string    $description  Document's description.
     * @param string    $file         Binary contents of file as read. (Will be Base64 encoded
     *                                by THIS function).
     * @param string    $file_name    Name of the file, required if file is provided.
     * @param string    $labels       The document's comma separated labels.
     * @param string    $source_url   Source URL for the document.
     * @param string    $text         Text to create document from.
     * @return API_Document|NULL      The document that was added, as returned by the Server.
     */
    public static function add(
        string    $title,
        ?string   $content_type = null,
        ?API_Date $date = null,
        ?string   $description = null,
        ?string   $file = null,
        ?string   $file_name = null,
        ?string   $labels = null,
        ?string   $source_url = null,
        ?string   $text = null,
    ): ?API_Document {
        $API_Wrap = API_Wrapper::getInstance();

        $addURL = '/document/add';

        /**
         * API call parameters.
         * @var array<string, string> $addParam
         */
        $addParam           = [];
        $addParam ['title'] = $title;   // Mandatory parameter.

        if ($date != null)
        {
            $addParam['date'] = $date->makeParamArray();
        }

        if ($description != null)
        {
            $addParam['description'] = $description;
        }

        if ($labels != null)
        {
            // Verify that there are no ', ' or ' ,' in string.

            $labelArray = explode(',', $labels);
            foreach ($labelArray as $key => $value)
            {
                $labelArray[$key] = trim($value);
            }
            $fixedLabels = implode(',', $labelArray);

            $addParam['labels'] = $fixedLabels;
        }

        if ($source_url != null)
        {
            $addParam['source_url'] = $source_url;
        }

        // Two types of file: A binary blob, which requires a $file_name and
        // $content_type, or a file created from simple $text.

        if ($file != null)  // Binary BLOB.
        {
            if ($file_name != null && $content_type != null)
            {
                $fileBase64 = base64_encode($file);

                $addParam['file']         = $fileBase64;
                $addParam['file_name']    = $file_name;
                $addParam['content_type'] = $content_type;
            }
            else
            {
                // Missing required parameters.

                return null;
            }
        }
        else
        {
            // $file == NULL. Check $text.

            if ($text != null)
            {
                $addParam['text'] = $text;
            }
            else
            {
                // Error - No $file and no $text!

                return null;
            }
        }

        //-------------------------------
        $addArray = $API_Wrap->callAPI_POST($addURL, $addParam);
        //-------------------------------

        $document = null;
        if ($addArray != null && array_key_exists('id', $addArray))
        {
            $document = API_Document::newFromArray($addArray);
        }

        return $document;
    }

    /**
     * Update values of this document.
     * After being created, only some fields can be updated. This function will
     * only update the paramters that are not NULL. NONE of the parameters are
     * mandatory.
     *
     * @param  string   $title
     * @param  API_Date $date   Date field OF the document (NOT the date of the
     *                          update. That is the updated_at field).
     * @param  string   $description
     * @param  NULL|string|array<string> $labels applies only to document objects.
     * @return bool     TRUE on success.
     */
    public function update(
        ?string   $title = null,
        ?API_Date $date = null,
        ?string   $description = null,
        array|string|null $labels = null,
    ): bool {
        return parent::_update($title, $date, $description, $labels);
    }

    //-------------------------------------------------
    // Inherited functions - The functions call the base class functions,
    // so that the complete API of the class is visible in one place.

    /**
     * {@inheritDoc}
     */
    public static function get(string $ID): ?static
    {
        return parent::get($ID);
    }

    /**
     * {@inheritDoc}
     */
    public function comment(string $text, ?string $title = null): bool
    {
        return parent::comment($text, $title);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(): bool
    {
        return parent::delete();
    }

    /**
     * {@inheritDoc}
     */
    public function tag(string $profileID): bool
    {
        return parent::tag($profileID);
    }

    /**
     * {@inheritDoc}
     */
    public function untag(string $profileID): bool
    {
        return parent::untag($profileID);
    }

    //-------------------------------------------------
    // Get functions for list values. The API call is made by the constructor
    // of the API_List classes.

    /**
     * {@inheritDoc}
     */
    public function getComments(
        bool $getAll = false,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListObjects {
        return parent::getComments($getAll, $force, $pageSize);
    }

    /**
     * Get list of projects asssociated with the document.
     * A document is associated with a project using the project/add_documents API.
     *
     * @param bool $getAll When set, the returned list will include ALL of the
     *                     projects. When false, will only return the first page.
     *                     Since a page in 50 items long, it is unlikely there will
     *                     be that many projects for a single document.
     * @param bool $IDsOnly
     * @param bool $force
     * @param int  $pageSize
     * @return API_ListObjects
     */
    public function getProjects(
        bool $getAll = false,
        bool $IDsOnly = false,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListObjects {
        if ($force || !$this->isLoaded('projects'))
        {
            $getProjectsURL = '/' . $this->getID() . '/projects';

            $this->projects = new API_ListObjects($getProjectsURL, API_Project::class, $getAll, $IDsOnly, fromAPICall: true, pageSize: $pageSize);
            $this->setLoaded('projects');
        }

        return $this->projects;
    }

    /**
     * {@inheritDoc}
     */
    public function getTags(
        bool $getAll = false,
        bool $IDsOnly = true,
        bool $force = false,
        int  $pageSize = API_ListBase::SIZE_OF_BATCH,
    ): API_ListProfiles {
        return parent::getTags($getAll, $IDsOnly, $force, $pageSize);
    }

    //=====================================================
    // Getter functions.
    // There are NO setter functions. The limited number of changes are
    // made using the update function.

    /** @return array <string> */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getPageCount(): ?int
    {
        return $this->page_count;
    }

    public function getSizes(): ?API_DocumentImageSizeMap
    {
        return $this->sizes;
    }

    public function getSourceUrl(): ?string
    {
        return $this->source_url;
    }

    public function getText(): ?string
    {
        return $this->text;
    }
}
