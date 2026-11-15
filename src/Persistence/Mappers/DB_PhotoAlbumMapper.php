<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_PhotoAlbum;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_PhotoImageSizeMapMapper;

/**
 * DB_PhotoAlbumMapper
 * Handles persistence for the API_PhotoAlbum primary object.
 * Delegates cover photo sizes to DB_PhotoImageSizeMapMapper.
 */
class DB_PhotoAlbumMapper extends DB_BaseMapper
{
    private DB_PhotoImageSizeMapMapper $sizeMapper;
    private DB_PhotoMapper             $photoMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $querySelectIDs;
    private \PDOStatement $queryUpdate;

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_photo_albums', 'album_id');

        $this->sizeMapper  = $persistor->getMapper(DB_PhotoImageSizeMapMapper::class);
        $this->photoMapper = $persistor->getMapper(DB_PhotoMapper::class);

        $sql = "INSERT INTO geni_photo_albums (
                    album_id, name, description, photos_count, url,
                    cover_sizes_id, run_id
                ) VALUES (
                    :id, :name, :desc, :count, :url,
                    :cover_id, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "SELECT cover_sizes_id FROM geni_photo_albums WHERE album_id = :album_id AND run_id = :run_id";

        $this->querySelectIDs = $pdo->prepare($sql);
        $this->querySelectIDs->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "UPDATE geni_photo_albums SET
                    name = :name, description = :desc, photos_count = :count,
                    url = :url, cover_sizes_id = :cover_id
                WHERE album_id = :id AND run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    //------------------------------------------------------

    /**
     * Entry point for persistence.
     */
    public function save(API_PhotoAlbum $album, bool $fullSave = true): string
    {
        if ($this->exists($album->getID()))
        {
            $this->update($album, $fullSave);
        }
        else
        {
            $this->create($album, $fullSave);
        }

        return $album->getID();
    }

    /**
     * Create: Persist sub-objects first, then parent.
     */
    protected function create(API_PhotoAlbum $album, bool $fullSave): void
    {
        $coverId = $this->sizeMapper->create($album->getCoverPhoto());

        $this->executeWithBinds($this->queryInsert, [
            'id'       => $album->getID(),
            'name'     => $album->getName(),
            'desc'     => $album->getDescription(),
            'count'    => $album->getPhotosCount(),
            'url'      => $album->getURL(),
            'cover_id' => $coverId
        ]);

        if ($fullSave)
        {
            $this->saveCollections($album, clear: false);
        }
    }

    public function read(string $albumID, bool $fullRead): ?API_PhotoAlbum
    {
        static $albumColumnMap = [
            'album_id' => 'id',
        ];

        $albumRow = parent::readArray($albumID);

        if ($albumRow === null)
        {
            return null;
        }

        $albumRow = $this->mapColumns($albumRow, $albumColumnMap);

        if (isset($albumRow ['cover_sizes_id']))
        {
            $albumRow ['cover_photo'] = $this->sizeMapper->readArray($albumRow ['cover_sizes_id']);
            unset($albumRow ['cover_sizes_id']);
        }

        if ($fullRead)
        {
            $expectedCount = (int) $albumRow ['photos_count'];

            if ($expectedCount > 0)
            {
                $photosInDB = $this->countTableRows('geni_photos', 'album_id', $albumID);

                if ($photosInDB === $expectedCount)
                {
                    $albumRow ['photos'] = $this->photoMapper->readAll($albumID);
                }
            }
        }

        return API_PhotoAlbum::newFromArray($albumRow, true);
    }

    /**
     * Update: Strict lifecycle for sub-objects.
     */
    protected function update(API_PhotoAlbum $album, bool $fullSave): void
    {
        $albumID = $album->getID();

        // 1. Wipe old sub-objects first
        $this->cleanupSubIDs($albumID);

        // 2. Create replacement cover size map
        $coverId = $this->sizeMapper->create($album->getCoverPhoto());

        // 3. Update parent record
        $this->executeWithBinds($this->queryUpdate, [
            'name'     => $album->getName(),
            'desc'     => $album->getDescription(),
            'count'    => $album->getPhotosCount(),
            'url'      => $album->getURL(),
            'cover_id' => $coverId,
            'id'       => $albumID
        ]);

        if ($fullSave)
        {
            $this->saveCollections($album, clear: true);
        }
    }

    /**
     * Delete: Remove parent and cleanup sub-objects.
     */
    public function delete(string $albumID, bool $fullDelete = true): void
    {
        $this->cleanupSubIDs($albumID);

        $this->deleteById($albumID);

        if ($fullDelete)
        {
            $this->photoMapper->deleteAll($albumID);
        }
    }

    //------------------------------------------------------

    /**
     * Collection Delegation: Links photos to the album.
     * Only mutates or clears relational photos when the photos collection is explicitly loaded.
     */
    private function saveCollections(API_PhotoAlbum $album, bool $clear): void
    {
        $albumID = $album->getID();

        if ($album->isLoaded('photos'))
        {
            $photos = $album->getPhotos()->getList();
            $this->photoMapper->createAll($photos, $albumID);
        }
    }

    /**
     * Pulls foreign key for the given album ID and deletes child cover size record.
     */
    private function cleanupSubIDs(string $albumID): void
    {
        $this->executeWithBinds($this->querySelectIDs, ['album_id' => $albumID]);
        $row = $this->querySelectIDs->fetch(PDO::FETCH_ASSOC);

        if (!$row)
        {
            return;
        }

        if ($row['cover_sizes_id'])
        {
            $this->sizeMapper->delete((int) $row['cover_sizes_id']);
        }
    }
}
