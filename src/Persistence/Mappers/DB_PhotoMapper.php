<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_CommentMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_DateMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_LocationMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_PhotoImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_TagMapper;

/**
 * DB_PhotoMapper
 * Handles persistence for API_Photo.
 */
class DB_PhotoMapper extends DB_BaseMapper
{
    private DB_CommentMapper   $commentMapper;
    private DB_DateMapper      $dateMapper;
    private DB_LocationMapper  $locationMapper;
    private DB_TagMapper       $tagMapper;
    private DB_PhotoImageSizeMapMapper $sizeMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $querySelectIDs;
    private \PDOStatement $queryUpdate;

    //------------------------------------------------------

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_photos', 'photo_id');

        $this->commentMapper  = $persistor->getMapper(DB_CommentMapper::class);
        $this->dateMapper     = $persistor->getMapper(DB_DateMapper::class);
        $this->locationMapper = $persistor->getMapper(DB_LocationMapper::class);
        $this->tagMapper      = $persistor->getMapper(DB_TagMapper::class);

        $this->sizeMapper = $persistor->getMapper(DB_PhotoImageSizeMapMapper::class);

        $sql = "INSERT INTO geni_photos (
                    photo_id, album_id, title, description, attribution,
                    content_type, guid, url, created_at, updated_at,
                    date_id, location_id, size_id, run_id
                ) VALUES (
                    :id, :album, :title, :desc, :attr,
                    :ctype, :guid, :url, :created_at, :updated_at,
                    :date_id, :loc_id, :size_id, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql
        = $sql = "SELECT date_id, location_id, size_id FROM geni_photos WHERE photo_id = :photo_id AND run_id = :run_id";

        $this->querySelectIDs = $pdo->prepare($sql);
        $this->querySelectIDs->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "UPDATE geni_photos SET
                    album_id = :album, title = :title, description = :desc,
                    attribution = :attr, content_type = :ctype, guid = :guid,
                    url = :url, created_at = :created_at, updated_at = :updated_at,
                    date_id = :date_id, location_id = :loc_id, size_id = :size_id
                WHERE photo_id = :id AND run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Entry point for persistence.
     */
    public function save(API_Photo $photo): string
    {
        if ($this->exists($photo->getId()))
        {
            $this->update($photo);
        }
        else
        {
            $this->create($photo);
        }

        return $photo->getID();
    }

    /**
     * Create: Persist sub-objects first, then the parent.
     */
    protected function create(API_Photo $photo): void
    {
        $photoID = $photo->getId();

        $dateId     = $photo->getDate() ? $this->dateMapper->create($photo->getDate(), $photoID) : null;
        $locationId = $photo->getLocation() ? $this->locationMapper->create($photo->getLocation(), $photoID) : null;
        $sizeMapId  = $photo->getSizes() ? $this->sizeMapper->create($photo->getSizes()) : null;

        $this->executeWithBinds($this->queryInsert, [
            'id'          => $photoID,
            'album'       => $photo->getAlbumId(),
            'title'       => $photo->getTitle(),
            'desc'        => $photo->getDescription(),
            'attr'        => $photo->getAttribution(),
            'ctype'       => $photo->getContentType(),
            'guid'        => $photo->getGuid(),
            'url'         => $photo->getUrl(),
            'created_at'  => $photo->getCreatedAt(),
            'updated_at'  => $photo->getUpdatedAt(),
            'date_id'     => $dateId,
            'loc_id'      => $locationId,
            'size_id'     => $sizeMapId
        ]);

        $this->saveCollections($photo, clear: false);
    }

    public function read(string $photoID, bool $fullRead): ?API_Photo
    {
        static $photoColumnMap = [
            'photo_id'       => 'id',
        ];

        $photoRow = parent::readArray($photoID);

        if ($photoRow === null)
        {
            return null;
        }

        $photoRow = $this->mapColumns($photoRow, $photoColumnMap);

        // Read sub-objects as arrays, and put them in object array.

        if (isset($photoRow ['date_id']))
        {
            $photoRow ['date'] = $this->dateMapper->readArray($photoRow ['date_id']);
            unset($photoRow ['date_id']);
        }

        if (isset($photoRow ['location_id']))
        {
            $photoRow ['location'] = $this->locationMapper->readArray($photoRow ['location_id']);
            unset($photoRow ['location_id']);
        }

        if (isset($photoRow ['size_id']))
        {
            $photoRow ['sizes'] = $this->sizeMapper->readArray($photoRow ['size_id']);
            unset($photoRow ['size_id']);
        }

        if ($fullRead)
        {
            // Comments and Tags.

            $comments = $this->commentMapper->readAll($photoID);

            if (count($comments) > 0)
            {
                $photoRow ['comments'] = $comments;
            }

            $tags = $this->tagMapper->readAll($photoID);

            if (count($tags) > 0)
            {
                $photoRow ['tags'] = $tags;
            }
        }

        return API_Photo::newFromArray($photoRow, fromDB: true);
    }

    /**
     * Update: Strict lifecycle for sub-objects.
     */
    protected function update(API_Photo $photo): void
    {
        $photoID = $photo->getId();

        // 1. Wipe previous sub-objects first
        $this->cleanupSubIDs($photoID);

        // 2. Create new sub-objects
        $dateId     = $photo->getDate() ? $this->dateMapper->create($photo->getDate(), $photoID) : null;
        $locationId = $photo->getLocation() ? $this->locationMapper->create($photo->getLocation(), $photoID) : null;
        $sizeMapId  = $photo->getSizes() ? $this->sizeMapper->create($photo->getSizes()) : null;

        // 3. Update parent
        $this->executeWithBinds($this->queryUpdate, [
            'album'       => $photo->getAlbumId(),
            'title'       => $photo->getTitle(),
            'desc'        => $photo->getDescription(),
            'attr'        => $photo->getAttribution(),
            'ctype'       => $photo->getContentType(),
            'guid'        => $photo->getGuid(),
            'url'         => $photo->getUrl(),
            'created_at'  => $photo->getCreatedAt(),
            'updated_at'  => $photo->getUpdatedAt(),
            'date_id'     => $dateId,
            'loc_id'      => $locationId,
            'size_id'     => $sizeMapId,
            'id'          => $photoID
        ]);

        $this->saveCollections($photo, clear: true);
    }

    /**
     * Delete: Removes parent and child sub-objects.
     */
    public function delete(string $photoID): void
    {
        $this->cleanupSubIDs($photoID);

        $this->deleteById($photoID);

        $this->tagMapper->deleteAll($photoID);

        $this->commentMapper->deleteAll($photoID);
    }

    /**
     * Collection Delegation: Tags and Comments.
     */
    private function saveCollections(API_Photo $photo, bool $clear): void
    {
        $photoID = $photo->getId();

        // Tags (Profile IDs)
        if ($photo->isLoaded('tags') || $photo->isLoaded('tag-IDs'))
        {
            /** @var array<int, string>|array<string, TRUE> $tagArray */
            $tagArray = $photo->getTags()->getList();

            $this->tagMapper->createAll($tagArray, $photoID);
        }

        if ($photo->isLoaded('comments'))
        {
            $this->commentMapper->createAll($photo->getComments()->getList(), $photoID);
        }
    }

    /**
     * Pulls IDs from the parent and wipes children.
     */
    private function cleanupSubIDs(string $photoID): void
    {
        $this->executeWithBinds($this->querySelectIDs, ['photo_id' => $photoID]);
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

    //------------------------------------------------------

    /**
     * Save array of photos belonging to album.
     * @param array <API_Photo> $photos
     * @param string      $albumID
     */
    public function createAll(array  $photos, string $albumID): void
    {
        $this->deleteAll($albumID);

        foreach ($photos as $photo)
        {
            $this->create($photo);
        }
    }

    /**
     * Read all photos belonging to album.
     * @param  string $albumID
     * @return array <API_Photo> objects (or empty).
     */
    public function readAll(string $albumID): array
    {
        $allPhotoRows = parent::readAllBy('album_id', $albumID, 'created_at');

        $photoArray = [];
        foreach ($allPhotoRows as $row)
        {
            $photoArray [] = API_Photo::newFromArray($row);
        }

        return $photoArray;
    }

    /**
     * Delete all photos belonging to object.
     * @param string $albumID
     */
    public function deleteAll(string $albumID): void
    {
        $this->deleteBy('album_id', $albumID);
    }
}
