<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Mockery;

/**
 * This mocker is "related" to the API_Comment class.
 * That class is a PDO, i.e. it doesn't respond to ANY API calls. Yet this mocker
 * is needed because the API_ListMocker class requires it.
 */
class API_CommentMocker extends API_BaseMocker
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    //======================================================
    // There are NO API calls for this object.

    //------------------------------------------------------

    /**
     * @param array <string, string> $API_Params  Parameters of API call.
     * @return array <string, string>
     */
    public function createObject(array $API_Params): array
    {
        $comment = [];

        if (isset($API_Params ['comment']))
        {
            $comment ['comment'] = $API_Params ['comment'];
        }

        if (isset($API_Params ['created_at']))
        {
            $comment ['created_at'] = $API_Params ['created_at'];
        }

        if (isset($API_Params ['title']))
        {
            $comment ['title'] = $API_Params ['title'];
        }

        return $comment;
    }

    /**
     * {@inheritDoc}
     * Comment objects do NOT have an ID value, so the parameter can be ignored.
     * @see \ShmuelAKam\Greenjeans\Tests\Api\Mockery\API_BaseMocker::createObjectByID()
     * @return array <string, mixed|array <string, mixed>>
     */
    public function createObjectByID(string $ID): array
    {
        $now     = (string) time();
        $comment = [
            'comment'    => 'Comment' . $this->getDefaultID('comment'),
            'created_at' => $now,
            'title'      => "Comment created $now",
        ];

        return $comment;
    }
}
