<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;

abstract class API_MediaMocker extends API_BaseMocker
{
    private string $objectType;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function reset(): void
    {
        parent::reset();
    }

    protected function setObjectType(string $objectType): void
    {
        $this->objectType = $objectType;
    }

    protected function getObjectType(): string
    {
        return $this->objectType;
    }

    //======================================================
    // Shared API calls

    /**
     * GET {media-type}/
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function get(array $API_Params, array $urlActions = []): array
    {
        $ownerID = $this->getSubject();

        // Call derived class function to try and get (or create) media object.
        $media = static::createObjectByID($ownerID);

        if ($media == [])
        {
            // Object NOT yet in memory.

            // Get fixture template baseline
            $media = $this->getFixture();

            // Generate deterministic ID and GUID
            // Note: Using $API_Params['name'] as seed for ID stability
            $media ['id']   = $this->getSubject();
            $media ['guid'] = FactoryBase::generateGuidFromId($media ['id']);

            $updatedAt = time();

            $media ['created_at'] = $updatedAt - 300;
            $media ['updated_at'] = $updatedAt;
        }
        else
        {
            // Create 'tags' field from list of profiles, to array of IDs.

            $tagList = $this->getList($ownerID, 'tags');

            if ($tagList != null)
            {
                $tagObjArray = $tagList->getListObjects();
                $tagIDs      = [];
                $first       = reset($tagObjArray);
                $isObj       = is_array($first);

                foreach ($tagObjArray as $tagObj)
                {
                    if ($isObj)
                    {
                        $tagIDs [] = $tagObj ['id'];
                    }
                    else
                    {
                        $tagIDs [] = $tagObj;
                    }
                }

                $media['tags'] = $tagIDs;
            }
        }

        $media ['url'] = 'https://www.geni.com/api/' . $this->getSubject();

        $this->setSubjectObject($media);

        return $media;
    }

    /**
     * POST {media-type}/comment
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function comment(array $API_Params, array $urlActions = []): array
    {
        // The input 'comment' uses 'text' for the comment content.
        // BUT the reply uses 'comment'. So much change key before
        // saving so that OK for all replies.

        $comment = [
            'comment'    => $API_Params ['text'] ?? '',
            'title'      => $API_Params ['title'] ?? '',
            'created_at' => time(),
        ];

        $this->updateList($this->getSubject(), 'comments', $API_Params, 'comment', [ $comment ]);

        return $comment;
    }

    /**
     * GET {media-type}/comments
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function comments(array $API_Params, array $urlActions = []): array
    {
        $commentsList = $this->getList($this->getSubject(), 'comments');
        $comments     = $commentsList->generatePageReply($API_Params);

        return $comments;
    }

    /**
     * POST {media-type}/delete
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function delete(array $API_Params, array $urlActions = []): array
    {
        $this->removeSubjectObject();

        // The server ALWAYS returns this reply. Even in cases that should fail.

        $reply = [ 'result' => 'Deleted' ];

        return $reply;
    }

    /**
     * POST {media-type}/tag
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function tag(array $API_Params, array $urlActions = []): array
    {
        $ownerID = $this->getSubject();
        $owner   = $this->getSubjectObject();
        $tagID   = reset($urlActions);
        $tagee   = $this->getTestObject($tagID);

        if ($tagee == null)
        {
            $tagee = $tagID;
        }

        $objectType = $this->getObjectType();
        $listName   = $objectType . 's';

        $this->updateList($ownerID, 'tags', $API_Params, 'profile', [ $tagee ]);
        $this->updateList($tagID, $listName, $API_Params, $objectType, [ $owner ]);

        $this->setUpdateAt();

        return $this->makeTagReply();
    }

    /**
     * GET {media-type}/tags
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function tags(array $API_Params, array $urlActions = []): array
    {
        return $this->makeTagReply();
    }

    /**
     * POST {media-type}/untag
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function untag(array $API_Params, array $urlActions = []): array
    {
        $untagID = $urlActions [array_key_first($urlActions)] ?? null;

        if ($untagID != null)
        {
            $ownerID  = $this->getSubject();
            $listName = $this->getObjectType() . 's';

            $this->removeFromList($ownerID, 'tags', $untagID);
            $this->removeFromList($untagID, $listName, $ownerID);
        }

        $reply = $this->makeTagReply();

        $this->setUpdateAt();

        return $reply;
    }

    /**
     * POST {media-type}/update
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $urlActions   Operations to do with params.
     * @return array <string, mixed|array <string, mixed>> Reply to API call.
     */
    protected function update(array $API_Params, array $urlActions = []): array
    {
        $fixtureValues = $this->getSubjectObject();

        // Set keys exist in the request, and remove those not in it.

        $this->setFixtureField($fixtureValues, 'date', $API_Params);
        $this->setFixtureField($fixtureValues, 'description', $API_Params);
        $this->setFixtureField($fixtureValues, 'title', $API_Params);

        // Save updated object
        $this->setSubjectObject($fixtureValues);

        $this->setUpdateAt();

        return $fixtureValues;
    }

    //------------------------------------------------------

    /**
     * This is a HELPER function, NOT a complete impementation of 'add'. That's why it's called _add
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @param  array <string> $validFields list of fields to add.
     * @return array <string, mixed|array <string, mixed>> added fixture object.
     */
    protected function _add(array $API_Params, array $validFields): array
    {
        // Initialize a fresh object (Base values seeded via API_MediaMocker)
        $fixtureValues = $this->createObject($API_Params);

        // Patch with Request Data
        foreach ($validFields as $field)
        {
            if (isset($API_Params[$field]))
            {
                $fixtureValues [$field] = $API_Params[$field];
            }
            else
            {
                unset($fixtureValues [$field]);
            }
        }

        if (isset($API_Params ['date']))
        {
            if (is_array($API_Params ['date']))
            {
                $dateObj = API_Date::newFromArray($API_Params ['date']);
            }
            else
            {
                // Convert JSON value into date array.
                $dateObj = API_Date::fromJSON($API_Params ['date']);
            }

            $dateArray              = $dateObj->makeParamArray();
            $fixtureValues ['date'] = $dateArray;
        }

        // Persist to static state.
        $this->setSubject($fixtureValues ['id']);
        $this->setSubjectObject($fixtureValues);

        return $fixtureValues;
    }

    /**
     * Format reply for tag, tags and untag API calls.
     * @return array <string, mixed|array <string, mixed>> reply.
     */
    protected function makeTagReply(): array
    {
        $ownerID = $this->getSubject();
        $tagList = $this->getList($ownerID, 'tags');

        $taggedIDs = [];
        if ($tagList != null)
        {
            $taggedProfiles = $tagList->getListObjects();
            $firstProfile   = reset($taggedProfiles);
            $isObject       = (is_array($firstProfile));

            if (count($taggedProfiles) > 0)
            {
                foreach ($taggedProfiles as $tagee)
                {
                    if ($isObject)
                    {
                        $taggedIDs [] = [ 'id' => $tagee ['id'] ];
                    }
                    else
                    {
                        $taggedIDs [] = [ 'id' => $tagee ];
                    }
                }
            }
        }

        $reply = [
            'results'     => $taggedIDs,
            'total_count' => count($taggedIDs),
        ];

        return $reply;
    }

    /**
     * @param  array <string, string|array <string, string>> $API_Params Parameters of API call.
     * @return array <string, mixed|array <string, mixed>> Created object.
     */
    protected function createObject(array $API_Params): array
    {
        // Get fixture template baseline
        $media = $this->getFixture();

        // Generate deterministic ID and GUID
        // Note: Using $API_Params['name'] as seed for ID stability

        $objectID = FactoryBase::generatePrefixedId($this->objectType, $API_Params['name'] ?? null);

        $media ['id']   = $objectID;
        $media ['guid'] = FactoryBase::generateGuidFromId($objectID);

        $createdAt = (string) time();

        $media ['created_at'] = $createdAt;
        $media ['updated_at'] = $createdAt;

        // Fix urls in fields: [url] and [sizes][url]

        if (isset($media ['url']))
        {
            $baseURL       = $media ['url'];
            $fixed         = substr($baseURL, 0, strpos($baseURL, $this->objectType)) . $objectID;
            $media ['url'] = $fixed;

            if (isset($media ['sizes']))
            {
                $media ['sizes']['url'] = $fixed;
            }
        }

        return $media;
    }

    /**
     * {@inheritDoc}
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array <string, mixed|array <string, mixed>>
     */
    public function createObjectByID(string $ID): array
    {
        $fileList = $this->getFileList();

        if (isset($fileList [$ID]))
        {
            // ID is one of the predefined objects that exist on the server.
            // Create from fixture.

            $obj       = $this->loadFixture($ID, true);
            $mediaType = $this->getResourceType();

            $this->setTestObject($obj, $mediaType);
        }
        else
        {
            // Some other ID, probably dynamically created.
            // If so, it should already be in the test-objects.

            $obj = $this->getTestObject($ID);
        }

        return $obj;
    }

    /**
     * Return lookup table of predefined media files.
     * @return array <string, string|TRUE>
     */
    abstract protected function getFileList(): array;
}
