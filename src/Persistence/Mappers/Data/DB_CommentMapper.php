<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_CommentMapper
 * Handles persistence for the API_Comment sub-object.
 * Sub-objects use INT AUTO_INCREMENT IDs and are stored in geni_comments.
 */
class DB_CommentMapper extends DB_BaseMapper
{
    private \PDOStatement $queryInsert;

    /**
     * @param DB_Persistor $persistor
     * @param PDO $pdo
     * @param int $run_id
     * @phpstan-ignore constructor.unusedParameter
     */
    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_comments', 'id');

        $sql = "INSERT INTO geni_comments (owner_id, comment, created_at, title, run_id)
                    VALUES (:owner, :comment, :created_at, :title, :run_id)";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    public function save(API_Comment $comment, string $ownerID): int
    {
        return $this->create($comment, $ownerID);
    }

    /**
     * Persist a new comment.
     * @param API_Comment $comment
     * @param string      $ownerID ID string of object that is commented on.
     * @return int        The generated ID.
     */
    public function create(API_Comment $comment, string $ownerID): int
    {
        $this->executeWithBinds($this->queryInsert, [
            'owner'      => $ownerID,
            'comment'    => $comment->getComment(),
            'created_at' => (int) $comment->getCreatedAt(),
            'title'      => $comment->getTitle()
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    /**
     * Read a comment by its internal INT ID.
     * @param int $id
     * @return API_Comment|NULL
     */
    public function read(int $id): ?API_Comment
    {
        $commentArray = $this->readArray($id);

        if ($commentArray !== null)
        {
            return API_Comment::newFromArray($commentArray);
        }
        else
        {
            return null;
        }
    }

    /**
     * Delete a comment by its internal INT ID.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->deleteById($id);
    }

    //------------------------------------------------------

    /**
     * Save array of comments belonging to object.
     * @param array <API_Comment>  $comments
     * @param string $ownerID
     */
    public function createAll(array  $comments, string $ownerID): void
    {
        $this->deleteAll($ownerID);

        foreach ($comments as $comment)
        {
            $this->create($comment, $ownerID);
        }
    }

    /**
     * Read all comments belonging to owner.
     * @param  string $ownerID
     * @return array <API_Comment> objects (or empty).
     */
    public function readAll(string $ownerID): array
    {
        $rows = $this->readAllBy('owner_id', $ownerID, 'created_at');

        return array_map(fn($row) => API_Comment::newFromArray($row), $rows);
    }

    /**
     * Delete all comments belonging to object.
     * @param string $ownerID
     */
    public function deleteAll(string $ownerID): void
    {
        $this->deleteBy('owner_id', $ownerID);
    }
}
