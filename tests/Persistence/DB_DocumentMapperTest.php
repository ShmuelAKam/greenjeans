<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_DocumentImageSizeMap;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_DocumentMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperDocuments;

class DB_DocumentMapperTest extends DB_MapperTestBase
{
    private DB_DocumentMapper $mapper;

    /**
     * Prepares the environment before running each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper(DB_DocumentMapper::class);
    }

    // =====================================================
    // Test providers.

    /**
     * @return array<string, array<string|int|array<int, int>|null>>
     */
    public static function provideDocument(): array
    {
        return FactoryMapperDocuments::DOC_LIST;
    }

    /**
     * @return  array <string, array <string, string|int|NULL|array<string|int>>>
     */
    public static function provideUpdate(): array
    {
        static $docUpdates = [
        [  'field' => 'Attribution',  'update' => 'Isaac Newton'      ],
        [  'field' => 'ContentType',  'update' => 'XXX/mpeg'          ],
        [  'field' => 'CreatedAt',    'update' => (int) 0             ],
        [  'field' => 'Description',  'update' => 'Boring'            ],
        [  'field' => 'Title',        'update' => 'Sir Whodunnit'     ],
        [  'field' => 'URL',          'update' => 'geni.com/notfound' ],
        [  'field' => 'PageCount',    'update' => '2'                          ],
        [  'field' => 'SourceUrl',    'update' => 'www.nevermore.com/never'    ],
        [  'field' => 'Text',         'update' => 'Lorem ipsum dolor sit amet' ],
        [  'field' => 'Labels',       'update' => [ 'Front', 'Side', 'Back' ]  ],
        ];

        $docList         = FactoryMapperDocuments::DOC_LIST;
        $updateDocuments = [];

        foreach ($docUpdates as $update)
        {
            foreach ($docList as $docKey => $docParam)
            {
                $compositeKey                   = $docKey . ' - ' . $update['field'];
                $updateDocuments[$compositeKey] = array_merge($docParam, $update);
            }
        }

        return $updateDocuments;
    }

    // =====================================================
    // Helper functions.

    protected static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    // =====================================================
    // Custom assert functions.

    /**
     * Assert that two API_Document documents are the same, with option to ignore
     * specific fields.
     * @param API_Document $expected
     * @param API_Document $actual
     * @param string|array<string> $ignoreFields name(s) of fields to NOT compare.
     *          This is useful for tests that update only specific fields.
     */
    protected function assertEqualsDocument(
        API_Document $expected,
        API_Document $actual,
        string|array $ignoreFields = [],
    ): void {
        static $DOCUMENT_FIELDS = [
            // Fields from API_Media base class.
            'Attribution',
            'ContentType',
            'CreatedAt',
            'Description',
            'GUID',
            'ID',
            'Title',
            'UpdatedAt',
            'URL',
            // Fields from API_Document.
            'Labels',
            'PageCount',
            'SourceUrl',
            'Text',
        ];

        if (is_string($ignoreFields))
        {
            $ignoreFields = [ $ignoreFields ]; // Convert to array.
        }

        foreach ($DOCUMENT_FIELDS as $fieldName)
        {
            if (!in_array($fieldName, $ignoreFields))
            {
                $methodName  = 'get' . $fieldName;
                $expectedVal = $expected->$methodName();
                $actualVal   = $actual->$methodName();

                if (is_array($expectedVal) || is_object($expectedVal))
                {
                    $errMessage = $this->objectsErrMsg($expectedVal, $actualVal, $fieldName);
                }
                else
                {
                    $errMessage = "$fieldName does not match ($expectedVal, $actualVal).";
                }

                $this->assertEquals($expectedVal, $actualVal, $errMessage);
            }
        }

        // Sub-objects using non-standard assert functions.

        if (!in_array('Date', $ignoreFields))
        {
            $expectedDate = $expected->getDate();
            $actualDate   = $actual->getDate();

            if ($expectedDate !== null && $actualDate !== null)
            {
                $errMessage = $this->objectsErrMsg($expectedDate, $actualDate, 'Date');
                $this->assertTrue(API_Date::__equals($expectedDate, $actualDate), $errMessage);
            }
            elseif (!($expectedDate === null && $actualDate === null))
            {
                $this->fail('Only one date has value');
            }
        }

        if (!in_array('Location', $ignoreFields))
        {
            $expectedLocation = $expected->getLocation();
            $actualLocation   = $actual->getLocation();

            if ($expectedLocation !== null && $actualLocation !== null)
            {
                $errMessage = $this->objectsErrMsg($expectedLocation, $actualLocation, 'Location');
                $this->assertTrue(API_Location::__equals($expectedLocation, $actualLocation), $errMessage);
            }
            elseif (!($expectedLocation === null && $actualLocation === null))
            {
                $this->fail('Only one location has value');
            }
        }

        if (!in_array('SizeMap', $ignoreFields))
        {
            $expectedSizeMap = $expected->getSizes();
            $actualSizeMap   = $actual->getSizes();

            if ($expectedSizeMap !== null && $actualSizeMap !== null)
            {
                $this->assertEqualsSizeMap($expectedSizeMap, $actualSizeMap);
            }
            elseif (!($expectedSizeMap === null && $actualSizeMap === null))
            {
                $this->fail('Only one document has SizeMap.');
            }
        }
    }

    /**
     * Assert that two API_DocumentImageSizeMap are identical.
     * @param API_DocumentImageSizeMap $expected
     * @param API_DocumentImageSizeMap $actual
     */
    protected function assertEqualsSizeMap(API_DocumentImageSizeMap $expected, API_DocumentImageSizeMap $actual): void
    {
        $this->assertEquals($expected->getLarge(), $actual->getLarge(), 'large  field does not match.');
        $this->assertEquals($expected->getOrigin(), $actual->getOrigin(), 'origin field does not match.');
        $this->assertEquals($expected->getThumb(), $actual->getThumb(), 'thumb  field does not match.');
    }

    /**
     * Assert that documents' collections have expected number of table rows.
     * @param string $docID
     * @param string $stage
     * @param int    $numLabels
     * @param int    $numProjects
     * @param int    $numTags
     * @param int    $numComments
     */
    protected function assertCollections(
        string $docID,
        string $stage,
        int    $numLabels,
        int    $numProjects,
        int    $numTags,
        int    $numComments,
    ): void {
        $countLabels   = $this->mapper->countTableRows('geni_document_labels', 'doc_id', $docID);
        $countProjects = $this->mapper->countTableRows('geni_document_projects', 'doc_id', $docID);
        $countTags     = $this->mapper->countTableRows('geni_media_tags', 'owner_id', $docID);

        $countComments = $this->mapper->countTableRows('geni_comments', 'owner_id', $docID);

        $this->assertEquals($numLabels, $countLabels, "$stage - Labels");
        $this->assertEquals($numProjects, $countProjects, "$stage - Projects");
        $this->assertEquals($numTags, $countTags, "$stage - Tags");

        $this->assertEquals($numComments, $countComments, "$stage - Comments");
    }

    // =====================================================
    // Test functions.

    /**
     * Basic Create & Read.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $text
     * @param string $attribution
     * @param array  <int> $date
     * @param int    $numLabels
     * @param int    $numProjects
     * @param int    $numTags
     * @param int    $numComments
     * @dataProvider provideDocument
     */
    public function testCreateAndRead(
        string  $title,
        ?string $description,
        ?string $fileName,
        string  $text,
        ?string $attribution,
        ?array  $date,
        int     $numLabels,
        int     $numProjects,
        int     $numTags,
        int     $numComments,
    ): void {
        /** @var FactoryMapperDocuments $docMapFactory */
        $docMapFactory = FactoryMapperBase::getFactory(API_Document::class);

        $doc = $docMapFactory->makeMapDocument(
            $title,
            $description,
            $fileName,
            $text,
            $attribution,
            $date,
            $numLabels,
            $numProjects,
            $numTags,
            $numComments,
            full: true,
        );

        $docID = $doc->getID();

        //-------------------------------
        $this->mapper->save($doc);

        $retrieved = $this->mapper->read($docID, fullRead: true);
        //-------------------------------

        $this->assertNotNull($retrieved, 'Failed to retrieve.');
        $this->assertEqualsDocument($doc, $retrieved);
    }

    /**
     * Update Path
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $text
     * @param string $attribution
     * @param array  <int> $date
     * @param int    $numLabels
     * @param int    $numProjects
     * @param int    $numTags
     * @param string $field   in the camel-case (e.g. "CreatedAt") name format.
     * @param mixed  $update (int, string or array).
     *
     * @dataProvider provideUpdate
     */
    public function testUpdateDocument(
        string  $title,
        ?string $description,
        ?string $fileName,
        string  $text,
        ?string $attribution,
        ?array  $date,
        int     $numLabels,
        int     $numProjects,
        int     $numTags,
        int     $numComments,
        string  $field,
        mixed   $update,
    ): void {
        /** @var FactoryMapperDocuments $docMapFactory */
        $docMapFactory = FactoryMapperBase::getFactory(API_Document::class);

        // Create initial document from parameters.
        $docParam = $docMapFactory->makeParamArray(
            $title,
            $description,
            $fileName,
            $text,
            $attribution,
            $date,
            $numLabels,
            $numProjects,
            $numTags,
            $numComments,
            full: false,
        );

        $doc = API_Document::newFromArray($docParam, false);

        // Save initial document.
        $this->mapper->save($doc);

        // Update the document parameters, and create an object from it.
        // This is the same object, because it has the same ID value.
        $fixedFieldName = self::camelToSnake($field);
        $fieldGetter    = 'get' . $field;

        $docParam [$fixedFieldName] = $update;

        $updatedDoc = API_Document::newFromArray($docParam, fromDB: false);

        //-------------------------------
        // Update the object by saving it again.
        $this->mapper->save($updatedDoc);
        //-------------------------------

        // Read the document, to verify it was updated.
        $retrieved = $this->mapper->read($doc->getID(), fullRead: true);

        $this->assertNotNull($retrieved, "Document not retrieved");
        $this->assertEqualsDocument($updatedDoc, $retrieved, $field);
        $this->assertEquals($updatedDoc->$fieldGetter(), $retrieved->$fieldGetter(), "$field is different");
    }

    /**
     * Cascade Delete & Link Clearing.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $text
     * @param string $attribution
     * @param array  <int> $date
     * @param int    $numLabels
     * @param int    $numProjects
     * @param int    $numTags
     * @param int    $numComments
     * @dataProvider provideDocument
     */
    public function testDeletesAndClear(
        string  $title,
        ?string $description,
        ?string $fileName,
        string  $text,
        ?string $attribution,
        ?array  $date,
        int     $numLabels,
        int     $numProjects,
        int     $numTags,
        int     $numComments,
    ): void {
        /** @var FactoryMapperDocuments $docMapFactory */
        $docMapFactory = FactoryMapperBase::getFactory(API_Document::class);

        $doc = $docMapFactory->makeMapDocument(
            $title,
            $description,
            $fileName,
            $text,
            $attribution,
            $date,
            $numLabels,
            $numProjects,
            $numTags,
            $numComments,
            full: true,
        );

        $docID = $doc->getID();

        $this->mapper->save($doc);

        //-------------------------------
        $this->mapper->delete($docID);
        //-------------------------------

        $deleted = $this->mapper->read($docID, fullRead: false);

        // Assert parent deleted
        $this->assertNull($deleted);

        // Assert link tables rows deleted

        $this->assertCollections($docID, 'Deleted', 0, 0, 0, 0);
    }

    /**
     *Non-Existent Record Lookup
     */
    public function testReadNotExist(): void
    {
        $doesntExist = $this->mapper->read('does-not-exist', fullRead: false);

        $this->assertNull($doesntExist);
    }

    // =====================================================
    // Complex Edge Case Tests.

    /**
     * This test verifies that:
     * 1. Saving a partially hydrated document (where collections like tags/comments were
     *    never loaded from the database or API) preserves existing relational links in geni_media_tags.
     * 2. The mapper bypasses collection getters when isLoaded('tags') is FALSE, avoiding
     *    unwanted SDK network calls.
     * @param string $title
     * @param string $description
     * @param string $fileName
     * @param string $text
     * @param string $attribution
     * @param array  <int> $date
     * @param int    $numLabels
     * @param int    $numProjects
     * @param int    $numTags
     * @param int    $numComments
     * @dataProvider provideDocument
     */
    public function testPartialHydrationPreservesUnloadedCollections(
        string  $title,
        ?string $description,
        ?string $fileName,
        string  $text,
        ?string $attribution,
        ?array  $date,
        int     $numLabels,
        int     $numProjects,
        int     $numTags,
        int     $numComments,
    ): void {
        /** @var FactoryMapperDocuments $docMapFactory */
        $docMapFactory = FactoryMapperBase::getFactory(API_Document::class);

        // 1. Build initial document parameters and force fromDB: TRUE so collections
        // are marked as loaded

        $initialParams = $docMapFactory->makeParamArray(
            $title,
            $description,
            $fileName,
            $text,
            $attribution,
            $date,
            $numLabels,
            $numProjects,
            $numTags,
            $numComments,
            full: true,
        );

        $initialDoc = API_Document::newFromArray($initialParams, fromDB: true);
        $docID      = $initialDoc->getID();

        $this->mapper->save($initialDoc);

        // Verify initial state in DB

        $this->assertCollections($docID, 'Setup', $numLabels, $numProjects, $numTags, $numComments);

        // 2. Simulate working with a partial document instance where collections were NOT loaded.

        $docParam = $docMapFactory->makeParamArray(
            "$title Partial",
            $description,
            $fileName,
            $text,
            $attribution,
            $date,
            0,
            0,
            0,
            0,
            full: true,
        );
        $docParam ['id']   = $docID;
        $updatedPartialDoc = API_Document::newFromArray($docParam, fromDB: false);

        //-------------------------------
        // Saving partial doc must update scalar fields BUT leave collection tables untouched.
        $this->mapper->save($updatedPartialDoc);
        //-------------------------------

        $this->assertCollections($docID, 'Test', $numLabels, $numProjects, $numTags, $numComments);
    }

    /**
     * This test verifies that:
     * 1. Child sub-objects (Date, Location, SizeMap) associated with a parent document
     *    are properly cleaned up and deleted from child tables when omitted during an update.
     * 2. Updating sub-objects replaces foreign key references without leaving orphan
     *    rows in child tables.
     */
    public function testSubObjectMutationAndOrphanCleanup(): void
    {
        /** @var FactoryMapperDocuments $docMapFactory */
        $docMapFactory = FactoryMapperBase::getFactory(API_Document::class);

        // 1. Create parameter array with sub-objects (Date, Location, SizeMap)
        $docParam = $docMapFactory->makeParamArray(
            'Sub-Object Test',
            'Desc',
            'file.pdf',
            'Text',
            'Author',
            ['year' => 2026, 'month' => 9, 'day' => 27],
            numLabels: 0,
            numProjects: 0,
            numTags: 0,
            full: true,
        );

        $doc   = API_Document::newFromArray($docParam, fromDB: false);
        $docID = $doc->getId();

        // Persist initial document
        $this->mapper->save($doc);

        // Fetch child foreign keys stored on the parent record
        $sql  = "SELECT date_id, location_id, size_id FROM geni_documents WHERE doc_id = :id AND run_id = :rid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $docID, ':rid' =>$this->runID]);
        $initialIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($initialIDs['date_id'], 'Date ID should exist.');
        $this->assertNotEmpty($initialIDs['location_id'], 'Location ID should exist.');
        $this->assertNotEmpty($initialIDs['size_id'], 'Size ID should exist.');

        // 2. Modify parameter array to clear Location and SizeMap
        unset($docParam['location']);
        unset($docParam['sizes']);

        // Instantiate updated document object from modified array parameters
        $updatedDoc = API_Document::newFromArray($docParam, fromDB: false);

        // Save updated document
        $this->mapper->save($updatedDoc);

        // Assert orphaned child rows in geni_locations (using 'id') and geni_document_sizes were deleted
        $this->assertEquals(
            0,
            $this->mapper->countTableRows('geni_locations', 'id', $initialIDs['location_id']),
            'Old location row must be deleted on removal.',
        );

        $this->assertEquals(
            0,
            $this->mapper->countTableRows('geni_document_sizes', 'id', $initialIDs['size_id']),
            'Old size map row must be deleted on removal.',
        );

        // Assert parent foreign keys were updated to NULL while date_id remains populated
        $stmt->execute([':id' => $docID, ':rid' =>$this->runID]);
        $updatedIDs = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNull($updatedIDs['location_id']);
        $this->assertNull($updatedIDs['size_id']);
        $this->assertNotEmpty($updatedIDs['date_id']);
    }

    /**
     * This test verifies that:
     * Multi-table write operations rolled back within a database transaction context
     * leave no partial writes or orphaned records in the database if an exception occurs
     * mid-save.
     */
    public function testSaveRollbackOnException(): void
    {
        /** @var FactoryMapperDocuments $docMapFactory */
        $docMapFactory = FactoryMapperBase::getFactory(API_Document::class);

        $doc = $docMapFactory->makeMapDocument(
            'Rollback Test',
            'Desc',
            'file.pdf',
            'Text',
            'Author',
            null,
            numLabels: 2,
            numProjects: 0,
            numTags: 0,
            full: true,
        );
        $docID = $doc->getID();

        // Begin manual transaction block to test atomicity
        $this->pdo->beginTransaction();

        try
        {
            // Save initial valid state
            $this->mapper->save($doc);

            // Force a duplicate primary key constraint violation
            $sql  = "INSERT INTO geni_documents (doc_id, run_id) VALUES (:id, :rid)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $docID, ':rid' =>$this->runID]);

            $this->pdo->commit();
        }
        catch (\PDOException $e)
        {
            $this->pdo->rollBack();
        }

        // Verify database rolled back completely to clean state
        $retrieved = $this->mapper->read($docID, fullRead: false);
        $this->assertNull($retrieved, 'Database changes must roll back completely if execution fails mid-save.');
    }
}
