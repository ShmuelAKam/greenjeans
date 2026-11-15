<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_CommentMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_DateMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_LocationMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_TagMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_VideoImageSizeMapMapper;

/**
 * DB_VideoMapper
 * Handles persistence for API_Video.
 */
class DB_VideoMapper extends DB_BaseMapper
{
    private DB_CommentMapper   $commentMapper;
    private DB_DateMapper      $dateMapper;
    private DB_LocationMapper  $locationMapper;
    private DB_TagMapper       $tagMapper;
    private DB_VideoImageSizeMapMapper $sizeMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $querySelectIDs;
    private \PDOStatement $queryUpdate;

    //------------------------------------------------------

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_videos', 'video_id');

        $this->commentMapper  = $persistor->getMapper(DB_CommentMapper::class);
        $this->dateMapper     = $persistor->getMapper(DB_DateMapper::class);
        $this->locationMapper = $persistor->getMapper(DB_LocationMapper::class);
        $this->tagMapper      = $persistor->getMapper(DB_TagMapper::class);

        $this->sizeMapper = $persistor->getMapper(DB_VideoImageSizeMapMapper::class);

        $sql = "INSERT INTO geni_videos (
                    video_id, title, description, attribution,
                    content_type, guid, url, created_at, updated_at,
                    date_id, location_id, size_id, run_id
                ) VALUES (
                    :id, :title, :desc, :attr,
                    :ctype, :guid, :url, :created_at, :updated_at,
                    :date_id, :loc_id, :size_id, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "SELECT date_id, location_id, size_id FROM geni_videos
                    WHERE video_id = :video_id AND run_id = :run_id";

        $this->querySelectIDs = $pdo->prepare($sql);
        $this->querySelectIDs->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "UPDATE geni_videos SET
                    title = :title, description = :desc, attribution = :attr,
                    content_type = :ctype, guid = :guid, url = :url,
                    created_at = :created_at, updated_at = :updated_at,
                    date_id = :date_id, location_id = :loc_id, size_id = :size_id
                WHERE video_id = :id AND run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Entry point for persistence.
     */
    public function save(API_Video $video): string
    {
        if ($this->exists($video->getId()))
        {
            $this->update($video);
        }
        else
        {
            $this->create($video);
        }

        return $video->getID();
    }

    /**
     * Create: Persist sub-objects first, then the parent.
     */
    protected function create(API_Video $video): void
    {
        $videoID = $video->getId();

        $dateId     = $video->getDate() ? $this->dateMapper->create($video->getDate(), $videoID) : null;
        $locationId = $video->getLocation() ? $this->locationMapper->create($video->getLocation(), $videoID) : null;
        $sizeMapId  = $video->getSizes() ? $this->sizeMapper->create($video->getSizes()) : null;

        $this->executeWithBinds($this->queryInsert, [
            'id'          => $videoID,
            'title'       => $video->getTitle(),
            'desc'        => $video->getDescription(),
            'attr'        => $video->getAttribution(),
            'ctype'       => $video->getContentType(),
            'guid'        => $video->getGuid(),
            'url'         => $video->getUrl(),
            'created_at'  => $video->getCreatedAt(),
            'updated_at'  => $video->getUpdatedAt(),
            'date_id'     => $dateId,
            'loc_id'      => $locationId,
            'size_id'     => $sizeMapId
        ]);

        $this->saveCollections($video, clear: false);
    }

    public function read(string $videoID, bool $fullRead): ?API_Video
    {
        static $videoColumnMap = [
            'video_id'       => 'id',
        ];

        /** @var array <string, int|string>|NULL $videoRow */
        $videoRow = $this->readArray($videoID);

        if ($videoRow === null)
        {
            return null;
        }

        $videoRow = $this->mapColumns($videoRow, $videoColumnMap);

        if (isset($videoRow ['date_id']))
        {
            $videoRow ['date'] = $this->dateMapper->readArray($videoRow ['date_id']);
            unset($videoRow ['date_id']);
        }

        if (isset($videoRow ['location_id']))
        {
            $videoRow ['location'] = $this->locationMapper->readArray($videoRow ['location_id']);
            unset($videoRow ['location_id']);
        }

        if (isset($videoRow ['size_id']))
        {
            $videoRow ['sizes'] = $this->sizeMapper->readArray($videoRow ['size_id']);
            unset($videoRow ['size_id']);
        }

        if ($fullRead)
        {
            $comments = $this->commentMapper->readAll($videoID);

            if (count($comments) > 0)
            {
                $videoRow ['comments'] = $comments;
            }

            $tags = $this->tagMapper->readAll($videoID);

            if (count($tags) > 0)
            {
                $videoRow ['tags'] = $tags;
            }
        }

        return API_Video::newFromArray($videoRow, fromDB: true);
    }

    /**
     * Update: Strict lifecycle for sub-objects.
     */
    protected function update(API_Video $video): void
    {
        $videoID = $video->getId();

        // 1. Clean up old sub-objects upfront
        $this->cleanupSubIDs($videoID);

        // 2. Create replacement sub-objects
        $dateId     = $video->getDate() ? $this->dateMapper->create($video->getDate(), $videoID) : null;
        $locationId = $video->getLocation() ? $this->locationMapper->create($video->getLocation(), $videoID) : null;
        $sizeMapId  = $video->getSizes() ? $this->sizeMapper->create($video->getSizes()) : null;

        // 3. Update parent record
        $this->executeWithBinds($this->queryUpdate, [
            'title'       => $video->getTitle(),
            'desc'        => $video->getDescription(),
            'attr'        => $video->getAttribution(),
            'ctype'       => $video->getContentType(),
            'guid'        => $video->getGuid(),
            'url'         => $video->getUrl(),
            'created_at'  => $video->getCreatedAt(),
            'updated_at'  => $video->getUpdatedAt(),
            'date_id'     => $dateId,
            'loc_id'      => $locationId,
            'size_id'     => $sizeMapId,
            'id'          => $videoID
        ]);

        $this->saveCollections($video, clear: true);
    }

    /**
     * Delete: Removes parent and child sub-objects.
     */
    public function delete(string $videoID): void
    {
        $this->cleanupSubIDs($videoID);

        $this->deleteById($videoID);

        $this->tagMapper->deleteAll($videoID);

        $this->commentMapper->deleteAll($videoID);
    }

    /**
     * Collection Delegation: Tags and Comments.
     */
    private function saveCollections(API_Video $video, bool $clear): void
    {
        $videoID = $video->getId();

        if ($video->isLoaded('tags') || $video->isLoaded('tag-IDs'))
        {
            /** @var array<int, string>|array<string, TRUE> $tagArray */
            $tagArray = $video->getTags()->getList();

            $this->tagMapper->createAll($tagArray, $videoID);
        }

        if ($video->isLoaded('comments'))
        {
            $this->commentMapper->createAll($video->getComments()->getList(), $videoID);
        }
    }

    /**
     * Pulls foreign keys for the given parent ID and deletes child sub-objects.
     */
    private function cleanupSubIDs(string $videoID): void
    {
        $this->executeWithBinds($this->querySelectIDs, ['video_id' => $videoID]);
        $row = $this->querySelectIDs->fetch(PDO::FETCH_ASSOC);

        if (!$row)
        {
            return;
        }

        if ($row['date_id'])
        {
            $this->dateMapper->delete((int) $row['date_id']);
        }

        if ($row['location_id'])
        {
            $this->locationMapper->delete((int) $row['location_id']);
        }

        if ($row['size_id'])
        {
            $this->sizeMapper->delete((int) $row['size_id']);
        }
    }
}
