<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_CommentMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_DateMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_DocumentImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_LocationMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_TagMapper;

/**
 * DB_DocumentMapper
 * Handles persistence for API_Document.
 */
class DB_DocumentMapper extends DB_BaseMapper
{
    private DB_CommentMapper   $commentMapper;
    private DB_DateMapper      $dateMapper;
    private DB_LinkTableMapper $linkMapper;
    private DB_LocationMapper  $locationMapper;
    private DB_ProjectMapper   $projectMapper;
    private DB_TagMapper       $tagMapper;
    private DB_DocumentImageSizeMapMapper $sizeMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $querySelectIDs;
    private \PDOStatement $queryUpdate;

    //------------------------------------------------------

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_documents', 'doc_id');

        $this->commentMapper  = $persistor->getMapper(DB_CommentMapper::class);
        $this->dateMapper     = $persistor->getMapper(DB_DateMapper::class);
        $this->linkMapper     = $persistor->getMapper(DB_LinkTableMapper::class);
        $this->locationMapper = $persistor->getMapper(DB_LocationMapper::class);
        $this->projectMapper  = $persistor->getMapper(DB_ProjectMapper::class);
        $this->tagMapper      = $persistor->getMapper(DB_TagMapper::class);

        $this->sizeMapper = $persistor->getMapper(DB_DocumentImageSizeMapMapper::class);

        //----------------------------------------

        $sql = "INSERT INTO geni_documents (
                    doc_id, title, description, text_content, attribution,
                    content_type, guid, page_count, source_url, url,
                    date_id, created_at, updated_at, location_id, size_id, run_id
                ) VALUES (
                    :doc_id, :title, :desc, :text, :attr,
                    :ctype, :guid, :pages, :src, :url,
                    :date_id, :created_at, :updated_at, :loc_id, :size_id, :run_id
                )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        //----------------------------------------

        $sql = "SELECT date_id, location_id, size_id FROM geni_documents
                    WHERE doc_id = :doc_id AND run_id = :run_id";

        $this->querySelectIDs = $pdo->prepare($sql);
        $this->querySelectIDs->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        //----------------------------------------

        $sql = "UPDATE geni_documents SET
                    title = :title, description = :desc, text_content = :text,
                    attribution = :attr, content_type = :ctype, guid = :guid,
                    page_count = :pages, source_url = :src, url = :url,
                    date_id = :date_id, created_at = :created_at,
                    updated_at = :updated_at, location_id = :loc_id, size_id = :size_id
                WHERE doc_id = :doc_id AND run_id = :run_id";

        $this->queryUpdate = $pdo->prepare($sql);
        $this->queryUpdate->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Entry point for persistence.
     */
    public function save(API_Document $doc): string
    {
        if ($this->exists($doc->getId()))
        {
            $this->update($doc);
        }
        else
        {
            $this->create($doc);
        }

        return $doc->getID();
    }

    /**
     * Create: Persist sub-objects first, then the parent.
     */
    protected function create(API_Document $doc): void
    {
        $docID = $doc->getId();

        $dateId     = $doc->getDate() ? $this->dateMapper->create($doc->getDate(), $docID) : null;
        $locationId = $doc->getLocation() ? $this->locationMapper->create($doc->getLocation(), $docID) : null;
        $sizeMapId  = $doc->getSizes() ? $this->sizeMapper->create($doc->getSizes()) : null;

        $createParams = [
            'doc_id'     => $docID,
            'title'      => $doc->getTitle(),
            'desc'       => $doc->getDescription(),
            'text'       => $doc->getText(),
            'attr'       => $doc->getAttribution(),
            'ctype'      => $doc->getContentType(),
            'guid'       => $doc->getGuid(),
            'pages'      => $doc->getPageCount(),
            'src'        => $doc->getSourceUrl(),
            'url'        => $doc->getUrl(),
            'date_id'    => $dateId,
            'created_at' => $doc->getCreatedAt(),
            'updated_at' => $doc->getUpdatedAt(),
            'loc_id'     => $locationId,
            'size_id'    => $sizeMapId
        ];

        $this->executeWithBinds($this->queryInsert, $createParams);

        $this->saveCollections($doc, clear: false);
    }

    public function read(string $docID, bool $fullRead): ?API_Document
    {
        static $docColumnMap = [
            'doc_id'       => 'id',
            'text_content' => 'text',
        ];

        $docRow = $this->readArray($docID);

        if ($docRow === null)
        {
            return null;
        }

        $docRow = $this->mapColumns($docRow, $docColumnMap);

        // Read sub-objects as arrays, and put them in object array.

        if (isset($docRow ['date_id']))
        {
            $docRow ['date'] = $this->dateMapper->readArray($docRow ['date_id']);
        }

        if (isset($docRow ['location_id']))
        {
            $docRow ['location'] = $this->locationMapper->readArray($docRow ['location_id']);
        }

        if (isset($docRow ['size_id']))
        {
            $docRow ['sizes'] = $this->sizeMapper->readArray($docRow ['size_id']);
        }

        unset($docRow ['date_id']);
        unset($docRow ['location_id']);
        unset($docRow ['size_id']);
        unset($docRow ['run_id']);

        if ($fullRead)
        {
            $collections = $this->readCollections($docID);

            $docRow = array_merge($docRow, $collections);
        }

        return API_Document::newFromArray($docRow, fromDB: true);
    }

    /**
     * Update: Strict lifecycle for sub-objects.
     */
    protected function update(API_Document $doc): void
    {
        $docID = $doc->getId();

        $this->cleanupSubIDs($docID);

        // 2. Create new sub-objects
        $dateId     = $doc->getDate() ? $this->dateMapper->create($doc->getDate(), $docID) : null;
        $locationId = $doc->getLocation() ? $this->locationMapper->create($doc->getLocation(), $docID) : null;
        $sizeMapId  = $doc->getSizes() ? $this->sizeMapper->create($doc->getSizes()) : null;

        // 3. Update parent
        $this->executeWithBinds($this->queryUpdate, [
            'title'      => $doc->getTitle(),
            'desc'       => $doc->getDescription(),
            'text'       => $doc->getText(),
            'attr'       => $doc->getAttribution(),
            'ctype'      => $doc->getContentType(),
            'guid'       => $doc->getGuid(),
            'pages'      => $doc->getPageCount(),
            'src'        => $doc->getSourceUrl(),
            'url'        => $doc->getUrl(),
            'date_id'    => $dateId,
            'created_at' => $doc->getCreatedAt(),
            'updated_at' => $doc->getUpdatedAt(),
            'loc_id'     => $locationId,
            'size_id'    => $sizeMapId,
            'doc_id'     => $docID
        ]);

        $this->saveCollections($doc, clear: false);
    }

    /**
     * Delete: Removes parent and child sub-objects.
     */
    public function delete(string $docID): void
    {
        $this->cleanupSubIDs($docID);

        $this->deleteById($docID);

        $this->deleteCollections($docID);
    }

    //------------------------------------------------------

    /**
     * Handles updating LinkTable collections: Tags, Labels, Comments, and Projects.
     * All getters are guarded by isLoaded() to prevent unneeded SDK network calls.
     */
    private function saveCollections(API_Document $doc, bool $clear): void
    {
        $docID = $doc->getId();

        // Labels (simple strings)
        if ($doc->isLoaded('labels'))
        {
            $this->linkMapper->saveSequence('geni_document_labels', 'doc_id', $docID, 'label', 'insert_order', $doc->getLabels());
        }

        // Tags (Profile IDs)
        if ($doc->isLoaded('tags') || $doc->isLoaded('tag-IDs'))
        {
            /** @var array<int, string>|array<string, TRUE> $tagArray */
            $tagArray = $doc->getTags()->getList();

            $this->tagMapper->createAll($tagArray, $docID);
        }

        // Comments
        if ($doc->isLoaded('comments'))
        {
            $this->commentMapper->createAll($doc->getComments()->getList(), $docID);
        }

        // Projects (IDs)
        if ($doc->isLoaded('projects'))
        {
            // Does NOT save the actual projects, only sets the link-table.
            $projectIDs = $doc->getProjects()->getListIDs();

            $this->linkMapper->save('geni_document_projects', 'doc_id', $docID, 'project_id', $projectIDs, $clear);
        }
    }

    private function deleteCollections(string $docID): void
    {
        $this->linkMapper->clear('geni_document_labels', 'doc_id', $docID);
        $this->linkMapper->clear('geni_document_projects', 'doc_id', $docID);

        $this->tagMapper->deleteAll($docID);
        $this->commentMapper->deleteAll($docID);
    }

    /**
     * @param string $docID
     * @return array <string, array <string|\ShmuelAKam\Greenjeans\Api\Data\API_Comment|\ShmuelAKam\Greenjeans\Api\API_Project>>
     */
    private function readCollections(string $docID): array
    {
        /**
         * @var array <string, array <string|\ShmuelAKam\Greenjeans\Api\Data\API_Comment|\ShmuelAKam\Greenjeans\Api\API_Project>> $collections
         */
        $collections = [];

        // Comments, Tags, Projects.

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $comments */
        $comments = $this->commentMapper->readAll($docID);

        if (count($comments) > 0)
        {
            $collections ['comments'] = $comments;
        }

        /** @var array <string> $tags */
        $tags = $this->tagMapper->readAll($docID);

        if (count($tags) > 0)
        {
            $collections ['tags'] = $tags;
        }

        /** array <string> */
        $collections ['labels'] = $this->linkMapper->readCol('geni_document_labels', 'doc_id', $docID, 'label', 'insert_order');

        /** @var array <string> $projectIDs */
        $projectIDs = $this->linkMapper->readCol('geni_document_projects', 'doc_id', $docID, 'project_id');

        if (count($projectIDs) > 0)
        {
            /** @var array <\ShmuelAKam\Greenjeans\Api\API_Project> $projects */
            $projects = [];
            foreach ($projectIDs as $ID)
            {
                $project = $this->projectMapper->read($ID, fullRead: true);

                if ($project != null)
                {
                    $projects [] = $project;
                }
            }

            $collections ['projects'] = $projects;
        }

        return $collections;
    }

    /**
     * Pulls IDs from the parent and wipes children.
     */
    private function cleanupSubIDs(string $docID): void
    {
        $this->executeWithBinds($this->querySelectIDs, ['doc_id' => $docID]);
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
