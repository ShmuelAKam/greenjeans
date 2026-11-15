<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * API_Comment - describes user comment on various media objects.
 *
 * This basic data structure does NOT have API of its own.
 * {@link https://www.geni.com/platform/developer/help/api?path=comment API_Comment}
 */
class API_Comment
{
    /**
     * Comment text.
     * @var string
     */
    private string $comment;

    /**
     * Comment's creation date as a timestamp.
     * @var string
     */
    private string $created_at;

    /**
     * Comment title.
     * @var string
     */
    private string $title;

    // ------------------------------------------------------

    /**
     * Constructor.
     * @param string $comment
     * @param string $created_at
     * @param string $title
     */
    public function __construct(string $comment, string $created_at, string $title)
    {
        $this->comment    = $comment;
        $this->created_at = $created_at;
        $this->title      = $title;
    }

    // ------------------------------------------------------

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

        if (array_key_exists('comment', $arrayValues))
        {
            $this->comment = $arrayValues['comment'];
        }

        if (array_key_exists('created_at', $arrayValues))
        {
            $this->created_at = $arrayValues['created_at'];
        }

        if (array_key_exists('title', $arrayValues))
        {
            $this->title = $arrayValues['title'];
        }
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, int<1, max>|string> $arrayValues
     * @return API_Comment
     */
    public static function newFromArray(array $arrayValues): API_Comment
    {
        // This sets ALL fields that were changed. Fields not changed have default value.

        $comment    = $arrayValues['comment'] ?? '';
        $created_at = $arrayValues['created_at'] ?? '';
        $title      = $arrayValues['title'] ?? '';

        $commentObj = new API_Comment($comment, $created_at, $title);

        return $commentObj;
    }

    /**
     * Creates array to send to Server in API calls.
     * @return array<string>
     */
    public function makeParamArray(): array
    {
        $paramArray = [
            'comment'    => $this->comment,
            'created_at' => $this->created_at,
            'title'      => $this->title
        ];

        return $paramArray;
    }

    // ------------------------------------------------------
    // Getters.

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
