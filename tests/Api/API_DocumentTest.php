<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Base;
use ShmuelAKam\Greenjeans\Api\API_Document;
use ShmuelAKam\Greenjeans\Api\API_Project;
use ShmuelAKam\Greenjeans\Api\Data\API_Comment;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDocuments;

/**
 * API_Document test case.
 * Most of these tests follow this pattern:
 * o Create a Document object on the server.
 * o Do an operation on the server object.
 * o Create ANOTHER copy of the server object.
 * o Verify on the copy that the tested operation worked.
 * This ensures that the operation also modified the server.
 */
class API_DocumentTest extends API_BaseTest
{
    public static function setUpBeforeClass(): void {}

    public static function tearDownAfterClass(): void {}

    /**
     * Prepares the environment before running each test.
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // =====================================================
    // Test providers.

    /**
     * Provider for test documents. These only contain field names.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideTestDocumentFieldnames(): array
    {
        return FactoryDocuments::TEST_DOC_FIELDS;
    }

    /**
     * Provider for test documents.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideTestDocumentBadFields(): array
    {
        return FactoryDocuments::TEST_DOC_BAD_FIELDS;
    }

    /**
     * Provider for test documents. These only contain field names.
     *
     * @return array <string, array <string|NULL|BOOL>>
     */
    public static function provideUpdateFieldData(): array
    {
        return FactoryDocuments::TEST_DOC_UPDATE_FIELDS;
    }

    /**
     * Provider for test documents.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideTestDocument(): array
    {
        return FactoryDocuments::TEST_DOCS;
    }

    /**
     * Provider for documents from FILE.
     *
     * @return array <string, array<int|string>>
     */
    public static function provideFileDocument(): array
    {
        return FactoryDocuments::DOCS_TO_CREATE_FROM;
    }

    /**
     * Provider for documents from FILE on the Geni server.
     *
     * @return array <string, array <string>>
     */
    public static function provideUploadedDocument(): array
    {
        return FactoryDocuments::DOCUMENTS_UPLOADED_FROM_URL;
    }

    /**
     * Provider for existing documents.
     *
     * @return array <string, array <string|array <string>>> of string doc-IDs.
     */
    public static function provideDocumentProjects(): array
    {
        return FactoryDocuments::DOCUMENTS_UPLOADED_PROJECTS;
    }

    /**
     * Provider for existing documents.
     *
     * @return array <int, array <array <string>>> of string doc-IDs.
     */
    public static function provideProjects(): array
    {
        return FactoryDocuments::PROJECTS_FOR_DOCUMENTS;
    }

    /**
     * Provider single comments.
     *
     * @return array <array <string, string>>
     */
    public static function provideComment(): array
    {
        return FactoryDocuments::TEST_DOC_COMMENTS;
    }

    /**
     * Provider for MULTIPLE comments.
     *
     * @return array <array <int|array <array <string, string>>>>
     */
    public static function provideComments(): array
    {
        $comments = [
            [ 1, FactoryDocuments::TEST_DOC_COMMENTS ],
            [ 2, FactoryDocuments::TEST_DOC_COMMENTS ],
            [ 3, FactoryDocuments::TEST_DOC_COMMENTS ],
            [ 5, FactoryDocuments::TEST_DOC_COMMENTS ],
            [ 5, FactoryDocuments::TEST_DOC_COMMENTS ],
        ];

        return $comments;
    }

    /**
     * @return array <array <int>>
     */
    public static function provideTagCount(): array
    {
        return [
            [ 2 ],
            [ 3 ],
            [ 5 ],
        ];
    }

    /**
     * @return array <array <int>>
     */
    public static function provideUntagCount(): array
    {
        return [
            // Tag, untag.
            [ 1,  1 ],
            [ 2,  1 ],
            [ 3,  2 ],
            [ 5,  2 ],
            [ 5,  5 ],
        ];
    }

    // =====================================================
    // Helper functions.

    /**
     * @param string $title
     * @param string $description
     * @param string $file_name
     * @param string $file
     * @param string $labels
     * @param string $text
     * @return array <string, string>
     */
    public function makeTestArray(?string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): array
    {
        $testArray = [];

        if ($title !== null)
        {
            $testArray ['title'] = $title;
        }

        if ($description !== null)
        {
            $testArray ['description'] = $description;
        }

        if ($file_name !== null)
        {
            $testArray ['file_name'] = $file_name;
        }

        if ($labels !== null)
        {
            $testArray ['labels'] = $labels;
        }

        if ($text !== null)
        {
            $testArray ['text'] = $text;
        }

        return $testArray;
    }

    private function assertTestDoc(API_Document $new, string $title, ?string $description, ?string $labels, ?string $text): void
    {
        if ($description != null)
        {
            $this->assertEquals($description, $new->getDescription());
        }

        if ($title != null)
        {
            $this->assertEquals($title, $new->getTitle());
        }

        if ($labels != null)
        {
            $labelsArray    = explode(',', $labels);
            $trimmed_array  = array_map('trim', $labelsArray);
            $getLabelsArray = $new->getLabels();

            $this->assertLabels($trimmed_array, $getLabelsArray);
        }
    }

    /**
     * @param array <string> $expectedLabels
     * @param array <string> $actualLabels
     */
    private function assertLabels(array $expectedLabels, array $actualLabels): void
    {
        // Some "cheating" because Geni returns SOME of the values with
        // Capital letters made lowercase. So convering all to lowercase
        // before comparing.

        // Convert both arrays to lowercase for a case-insensitive comparison
        $expectedLabels = array_map('strtolower', $expectedLabels);
        $actualLabels   = array_map('strtolower', $actualLabels);

        sort($expectedLabels);
        sort($actualLabels);

        $this->assertEquals($expectedLabels, $actualLabels);
    }

    /**
     * @param array <array <string, string>> $expectedComments
     * @param array <string, \ShmuelAKam\Greenjeans\Api\Data\API_Comment> $actualComments
     * @param int   $expectedCount
     */
    private function assertComments(array $expectedComments, array $actualComments, int $expectedCount): void
    {
        $this->assertCount($expectedCount, $actualComments);

        // The two Arrays of set values and returned results are NOT in the
        // same order. So for each set value we need to scan the results,
        // looking for the matching comment. When found compare, if the same,
        // remove it from results.

        foreach ($expectedComments as $expected)
        {
            $expectedTitle   = $expected['title'];
            $expectedComment = $expected['comment'];

            foreach ($actualComments as $resultKey => $result)
            {
                $actualComment = $result->getComment();

                if ($expectedComment == $actualComment)
                {
                    // No need to assert the Comment itself, because this IF just did that!

                    self::assertEquals($expectedTitle, $result->getTitle());

                    unset($actualComments [$resultKey]);
                    break;
                }
            }
        }

        // If the results array is NOT EMPTY after removing all of the matches,
        // then test has failed.

        self::assertCount(0, $actualComments);
    }

    /**
     * @param  bool $makeString When set, return result as a single string.
     * @return array<string>|string
     */
    private function getRandomLabels(bool $makeString): array|string
    {
        $documentEvaluations = [
            0 => ['perfectly concise', 'rather vague', 'deeply insightful'],
            1 => ['highly professional', 'outdated info', 'well structured', 'too long'],
            2 => ['crystal clear', 'badly formatted'],
            3 => ['extremely boring'],
            4 => ['super helpful', 'purely academic', 'strictly confidential'],
            5 => ['mostly fluff', 'quite messy', 'poorly written'],
            6 => ['totally accurate'],
            7 => ['exceptionally detailed', 'very good', 'visually appealing', 'well structured'],
            8 => ['rather vague', 'highly professional'],
            9 => ['badly formatted', 'crystal clear', 'super helpful', 'deeply insightful']
        ];

        $randomInd = array_rand($documentEvaluations);
        $labels    = $documentEvaluations [$randomInd];

        if ($makeString)
        {
            $labels = implode(',', $labels);
        }

        return $labels;
    }

    // =====================================================
    // Test functions.

    /**
     * Tests API_Document::add()
     *
     * @ dataProvider provideTestDocumentFieldnames
     * @dataProvider provideTestDocument
     */
    public function testAdd_FromFields(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        $documentFactory = $GLOBALS['documentFactory'];

        // --------------------------------------------------
        $newDoc = API_Document::add(
            title: $title,
            content_type: 'text/plain',
            date: null,
            description: $description,
            file: $file,
            file_name: $file_name,
            labels: $labels,
            source_url: null,
            text: $text,
        );
        // --------------------------------------------------

        $this->assertNotNull($newDoc);

        $documentFactory->logCreate($newDoc->getID());

        $this->assertTestDoc($newDoc, $title, $description, $labels, $text);
    }

    /**
     * Tests API_Document::add()
     *
     * @dataProvider provideTestDocumentBadFields
     */
    public function testAdd_BadFields(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        // --------------------------------------------------
        $newDoc = API_Document::add(
            title: $title,
            content_type: null,
            date: null,
            description: $description,
            file: $file,
            file_name: $file_name,
            labels: null,
            source_url: null,
            text: $text,
        );
        // --------------------------------------------------

        $this->assertNull($newDoc);
    }

    /**
     * Tests API_Document::add()
     * Can not have text together with fileName.
     */
    public function testAdd_BadText(): void
    {
        $title     = 'Ignore Text';
        $file      = 'File';
        $file_name = 'File Name';

        // --------------------------------------------------
        $newDoc = API_Document::add(title: $title, file: $file, file_name: $file_name, text: 'Text');
        // --------------------------------------------------

        $this->assertNull($newDoc);
    }

    /**
     * Tests API_Document::add()
     *
     * @dataProvider provideFileDocument
     */
    public function testAdd_FromFiles(?string $fileName, ?string $title, ?int $pageCount, ?string $contentType, ?string $text): void
    {
        global $documentFactory;

        $fileName     = $documentFactory->fullFileName($fileName);
        $fileContents = file_get_contents($fileName);

        // --------------------------------------------------
        $addedDoc = API_Document::add(title: $title, content_type: $contentType, file_name: $fileName, file: $fileContents);
        // --------------------------------------------------

        $this->assertNotNull($addedDoc);

        $documentFactory->logCreate($addedDoc->getID());
    }

    // =====================================================

    /**
     * Tests API_Document->updateFromArray()
     *
     * @dataProvider provideTestDocumentFieldnames
     * @dataProvider provideTestDocument
     */
    public function testUpdateFromArray(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory;

        $updateArray = $this->makeTestArray($title, $description, $file_name, $file, $labels, $text);

        $doc = $documentFactory->makeRandomDocument();

        // --------------------------------------------------
        $doc->updateFromArray($updateArray);
        // --------------------------------------------------

        $this->assertTestDoc($doc, $title, $description, $labels, $text);
    }

    /**
     * Tests API_Document::newFromArray()
     *
     * @dataProvider provideTestDocumentFieldnames
     * @dataProvider provideTestDocument
     */
    public function testNewFromArray(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        $updateArray = $this->makeTestArray($title, $description, $file_name, $file, $labels, $text);

        // --------------------------------------------------
        $doc = API_Document::newFromArray($updateArray);
        // --------------------------------------------------

        $this->assertTestDoc($doc, $title, $description, $labels, $text);
    }

    // /**
    // * Tests API_Document->makeParamArray()
    // *
    // * @dataProvider provideTestDocumentFieldnames
    // * @dataProvider provideTestDocument
    // */
    // public function testMakeParamArray (string $title, ?string $description, ?string $file_name,
    // ?string $file, ?string $labels, ?string $text): void
    // {
    // global $documentFactory;
    //
    // $doc = $documentFactory->makeDocument ($title, $description, $file_name, $file, $labels, $text);
    //
    // //--------------------------------------------------
    // $paramArray = $doc->makeParamArray ();
    // //--------------------------------------------------
    //
    // $this->assertEquals ($title, $paramArray ['title']);
    // $this->assertEquals ($description, $paramArray ['description']);
    // $this->assertEquals ($file_name, $paramArray ['file_name']);
    // $this->assertEquals ($file, $paramArray ['file']);
    // $this->assertEquals ($labels, $paramArray ['labels']);
    //
    // if ($file == NULL) {
    // $this->assertEquals ($text, $paramArray ['text']);
    // }
    // }

    /**
     * Tests API_Document::get()
     * @dataProvider provideRunCount
     */
    public function testGet(int $ignore): void
    {
        global $documentFactory;

        $doc   = $documentFactory->makeRandomDocument();
        $docID = $doc->getID();

        // --------------------------------------------------
        $getDoc = API_Document::get($docID);
        // --------------------------------------------------

        $this->assertNotNull($getDoc);
        $this->assertNotEmpty($getDoc->getTitle());
    }

    /**
     * Tests API_Document->comment()
     *
     * @dataProvider provideComment
     */
    public function testComment(string $title, string $comment): void
    {
        global $documentFactory;

        $doc = $documentFactory->makeRandomDocument();

        // --------------------------------------------------
        $success = $doc->comment($comment, $title);
        // --------------------------------------------------

        $this->assertTrue($success, 'doc->comment returned FAIL.');

        // Get another copy of the document, and isolate the first comment (there's only 1).
        $docID = $doc->getID();

        $checkerDoc   = API_Document::get($docID);
        $commentsList = $checkerDoc->getComments();

        /** @var API_Comment[] $commentsArray */
        $commentsArray = $commentsList->getList();

        $this->assertCount(1, $commentsArray, 'Wrong number of comments');
        $this->assertEquals($title, $commentsArray[0]->getTitle(), 'Title does not match');
        $this->assertEquals($comment, $commentsArray[0]->getComment(), 'Comment does not match');
    }

    /**
     * Tests API_Document->comment()
     * This test is identical to the test for testGetComments
     *
     * @dataProvider provideComments
     * @param int $count
     * @param array <array <string, string>> $comments
     */
    public function testComment_Multiple(int $count, array $comments): void
    {
        global $documentFactory;

        $doc   = $documentFactory->makeRandomDocument();
        $docID = $doc->getID();

        $setComments = [];

        for ($i = 0; $i < $count; $i++)
        {
            $commentKey = array_rand($comments, 1);
            $comment    = $comments  [$commentKey];

            // --------------------------------------------------
            $commentSuccess = $doc->comment($comment['comment'], $comment['title']);
            // --------------------------------------------------

            $this->assertTrue($commentSuccess);

            $setComments [] = $comment;
            unset($comments [$commentKey]);
        }

        // --------------------------------------------------

        $checkerDoc = API_Document::get($docID);

        $resultList     = $checkerDoc->getComments();
        $resultComments = $resultList->getList();

        $this->assertComments($setComments, $resultComments, $count);
    }

    /**
     * Tests API_Document->delete()
     * @dataProvider provideRunCount
     */
    public function testDelete(int $ignore): void
    {
        global $documentFactory;

        $doc   = $documentFactory->makeRandomDocument();
        $docID = $doc->getID();

        // --------------------------------------------------
        $success = $doc->delete();
        // --------------------------------------------------

        $this->assertTrue($success);

        $checkerDoc = API_Document::get($docID);

        $this->assertNull($checkerDoc);

        $documentFactory->logDelete($docID);
    }

    /**
    * Tests API_Document->delete()
    * @dataProvider provideRunCount
    *
    * It is NOT possible to test delete for fail. Geni ALWAYS returns
    * 'deleted' regardless of the object's status as deleted or even
    * if it never existed.
    */
    //     public function testDelete_Fail (int $ignore): void
    //     {
    //         global $documentFactory;
    //
    //         // Create document and DELETE it. We need a 'valid' ID. Now
    //         // delete it. The test tries to delete it a SECOND time. The
    //         // Test should fail.
    //
    //         $doc = $documentFactory->makeRandomDocument();
    //         $doc->delete();
    //         $docID = $doc->getID();
    //
    //         // --------------------------------------------------
    //         $success = $doc->delete();
    //         // --------------------------------------------------
    //
    //         $this->assertFalse ($success);
    //
    //         $documentFactory->logDelete($docID);
    //     }

    /**
     * Tests API_Document->tag()
     * @dataProvider provideRunCount
     */
    public function testTag(int $ignore): void
    {
        global $documentFactory;
        global $profileFactory;

        $doc   = $documentFactory->makeRandomDocument();
        $tagee = $profileFactory->makeRandomProfile();

        $docID   = $doc->getID();
        $tageeID = $tagee->getID();

        // --------------------------------------------------
        $tagged = $doc->tag($tageeID);
        // --------------------------------------------------

        $this->assertTrue($tagged, 'Tag call failed');

        $checkerDoc = API_Document::get($docID);
        $this->assertNotNull($checkerDoc);

        $tagList = $checkerDoc->getTags(true);

        $tagArray = $tagList->getList();

        if ($tagArray != null)
        {
            $this->assertCount(1, $tagArray, 'Tag array is empty');
            $this->assertEquals($tageeID, array_key_first($tagArray), 'Tagee ID not as expected');
        }
        else
        {
            $this->fail('No array returned');
        }
    }

    /**
     * Tests API_Document->tag()
     *
     * @dataProvider provideTagCount
     */
    public function testTag_Multiple(int $count): void
    {
        global $documentFactory;
        global $profileFactory;

        $doc    = $documentFactory->makeRandomDocument();
        $tagees = $profileFactory->makeProfiles($count);
        $docID  = $doc->getID();

        // --------------------------------------------------

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID    = $toTag->getID();
            $tageeIDs[] = $tageeID;

            // --------------------------------------------------
            $success = $doc->tag($tageeID);
            // --------------------------------------------------

            $this->assertTrue($success);
        }

        // --------------------------------------------------

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        $tagList  = $checkerDoc->getTags(true);

        $tagArray = array_keys($tagList->getList());

        if ($tagArray != null)
        {
            $this->assertCount($count, $tagArray, "Tag array wrong size (expected: $count, actual" . count($tagArray));

            $this->assertEquals($tageeIDs, $tagArray, 'Tags not equal');
        }
        else
        {
            $this->fail('No array returned');
        }
    }

    /**
     * Tests API_Document->untag()
     * @dataProvider provideRunCount
     */
    public function testUntag(int $ignore): void
    {
        global $documentFactory;
        global $profileFactory;

        $doc   = $documentFactory->makeRandomDocument();
        $tagee = $profileFactory->makeRandomProfile();

        $docID   = $doc->getID();
        $tageeID = $tagee->getID();

        $doc->tag($tageeID);

        // Get copy of doc, and UNTAG it.

        $docCopy = API_Document::get($docID);

        if ($docCopy != null)
        {
            // --------------------------------------------------
            $untagSuccess = $docCopy->untag($tageeID);
            // --------------------------------------------------
        }
        else
        {
            $this->fail("Couldn't get test copy of document.");
        }

        $this->assertTrue($untagSuccess, 'Untag call failed');

        // --------------------------------------------------
        // Get copy of doc, and verify that it is untagged.

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        $tagList  = $checkerDoc->getTags(true);
        $tagArray = $tagList->getList();

        $this->assertCount(0, $tagArray, 'Tag array NOT is empty');
    }

    /**
     * Tests API_Document->tag()
     *
     * @dataProvider provideUntagCount
     */
    public function testUntag_Multiple(int $tagCount, int $untagCount): void
    {
        global $documentFactory;
        global $profileFactory;

        $doc   = $documentFactory->makeRandomDocument();
        $docID = $doc->getID();

        $tagees = $profileFactory->makeProfiles($tagCount);

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID    = $toTag->getID();
            $tageeIDs[] = $tageeID;

            $success = $doc->tag($tageeID);
            $this->assertTrue($success, 'Test failed before it started. Couldn\'t tag');
        }

        // --------------------------------------------------
        // Get copy of doc, and UNTAG it in random order.

        $docCopy = API_Document::get($docID);

        if ($docCopy != null)
        {
            for ($i = 0; $i < $untagCount; $i++)
            {
                $randomKey = array_rand($tagees);

                $untagee    = $tagees[$randomKey];
                $untageeID  = $untagee->getID();
                $untageeKey = array_search($untageeID, $tageeIDs);

                // --------------------------------------------------
                $untagSuccess = $docCopy->untag($untageeID);
                // --------------------------------------------------

                $this->assertTrue($untagSuccess, 'Untag call failed');

                unset($tagees[$randomKey]);
                unset($tageeIDs[$untageeKey]);
            }
        }
        else
        {
            $this->fail("Couldn't get copy of document.");
        }

        // --------------------------------------------------
        // Create a second copy of doc, and verify that it is untagged.

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc, 'No checker document');

        $tagList  = $checkerDoc->getTags(getAll: true);
        $tagArray = $tagList->getList();

        // Convert the array values into string keys.

        $tagArray = array_map('strval', array_keys($tagArray));
        sort($tageeIDs);
        sort($tagArray);

        $expectedCount = $tagCount - $untagCount;

        $this->assertCount($expectedCount, $tagArray);

        $this->assertEquals($tageeIDs, $tagArray, 'Tags not equal');
    }

    /**
     * Tests API_Document->update()
     * Send labels parameter to update as string.
     *
     * @dataProvider provideUpdateFieldData
     */
    public function testUpdate(?string $title, ?string $description, bool $labels, bool $makeString): void
    {
        global $documentFactory, $dateFactory;

        if ($labels)
        {
            $labelsArray = $this->getRandomLabels(makeString: $makeString);
        }
        else
        {
            $labelsArray = null;
        }

        $doc   = $documentFactory->makeRandomDocument();
        $docID = $doc->getID();

        // Get labels (if any) from created test document.
        $labelsBeforeUpdate = $doc->getLabels();

        $dateObj = $dateFactory->makeRandomDate();

        // --------------------------------------------------
        // Update document with NEW >>additional<< labels (from provider).

        $success = $doc->update($title, $dateObj, $description, $labelsArray);
        // --------------------------------------------------

        $this->assertTrue($success, 'update API call failed.');

        // Get copy of document, to verify against.

        $updatedDoc = API_Document::get($docID);

        $this->assertNotNull($updatedDoc);

        $this->assertEquals($dateObj, $updatedDoc->getDate());

        if ($description != null)
        {
            $this->assertEquals($description, $updatedDoc->getDescription());
        }

        if ($labelsArray != null)
        {
            // Convert provider labels to array, merge with existing labels, and
            // make sure the labels do not repeat (are unique).

            $labelsArrayArray = API_Base::parseArrayOrCommaList($labelsArray);

            $mergedLabels = array_merge($labelsArrayArray, $labelsBeforeUpdate);
            $mergedLabels = array_unique($mergedLabels);
            sort($mergedLabels);

            // Get ALL labels from document. This should include the initial
            // $labels AND the labels we just added, i.e. identical to merged
            // array above.

            $updatedLabels = $updatedDoc->getLabels();
            sort($updatedLabels);

            $this->assertEquals($mergedLabels, $updatedLabels);
        }

        if ($title != null)
        {
            $this->assertEquals($title, $updatedDoc->getTitle());
        }
    }

    /**
     * Tests API_Document->getComments()
     * This test is identical to the test for testComment_Multiple
     *
     * @dataProvider provideComments
     * @param int $count
     * @param array <array <string, string>> $comments
     */
    public function testGetComments(int $count, array $comments): void
    {
        global $documentFactory;

        $doc   = $documentFactory->makeRandomDocument();
        $docID = $doc->getID();

        $setComments = [];

        for ($i = 0; $i < $count; $i++)
        {
            $commentKey = array_rand($comments, 1);
            $comment    = $comments  [$commentKey];

            $success = $doc->comment($comment['comment'], $comment['title']);

            $this->assertTrue($success, 'Failed to add comment.');

            $setComments [] = $comment;
            unset($comments [$commentKey]);
        }

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $resultList = $checkerDoc->getComments();
        // --------------------------------------------------

        $resultComments = $resultList->getList();

        $this->assertComments($setComments, $resultComments, $count);
    }

    /**
     * Tests API_Document->getProjects()
     * This test depends on EXISTING documents, that had projects added to
     * them manually.
     *
     * @dataProvider provideDocumentProjects
     * @param string $ID
     * @param array <string> $projects
     */
    public function testGetProjects(string $ID, array $projects): void
    {
        $doc = API_Document::get($ID);

        // --------------------------------------------------
        $projList = $doc->getProjects(IDsOnly: false);
        // --------------------------------------------------

        $projArray = $projList->getList();
        $projKeys  = array_keys($projArray);

        $this->assertCount(count($projects), $projArray);
        $this->assertEquals($projects, $projKeys);
    }

    /**
     * Tests API_Document->getProjects()
     * This test depends on EXISTING documents, that had projects added to
     * them manually.
     *
     * @dataProvider provideDocumentProjects
     * @param string $ID
     * @param array <string> $projects
     */
    public function testGetProjects_IDsOnly(string $ID, array $projects): void
    {
        $doc = API_Document::get($ID);

        // --------------------------------------------------
        $projList = $doc->getProjects(IDsOnly: true);
        // --------------------------------------------------

        $projArray = $projList->getList();
        $projKeys  = array_keys($projArray);

        $this->assertCount(count($projects), $projArray);
        $this->assertEquals($projects, $projKeys);
    }

    /**
     * Tests API_Document->getProjects()
     * This test creates a document, adds it to project(s) using the
     * project/add_documents API, and then tests the document.
     *
     * @dataProvider provideDocumentProjects
     * @param string $ignore
     * @param array <string> $projects
     */
    public function testGetProjects_DynamicAdd(string $ignore, array $projects): void
    {
        global $documentFactory;

        // Create document.

        $doc   = $documentFactory->makeRandomDocument();
        $docID = [ $doc->getID() ];

        // Add created document to each project in list. This should make the PROJECT
        // appear on the document object, when getProjects is called on it.

        $projectsObjects = [];
        foreach ($projects as $projID)
        {
            $proj = API_Project::get($projID);
            $proj->addDocuments($docID);

            $projectsObjects [$projID] = $proj;
        }

        // --------------------------------------------------
        $projList = $doc->getProjects();
        // --------------------------------------------------

        $projArray = $projList->getList();

        // Compare contents of the two arrays.

        $compareArrays = true;
        foreach ($projectsObjects as $projID => $proj)
        {
            $compareArrays = API_Project::__equals($proj, $projArray [$projID]);
            if (!$compareArrays)
            {
                break;
            }
        }

        $this->assertCount(count($projects), $projArray);
        $this->assertTrue($compareArrays);
    }

    /**
     * Tests API_Document->getTags()
     * This test is identical to testTag_Multiple.
     *
     * @dataProvider provideTagCount
     */
    public function testGetTags(int $count): void
    {
        global $documentFactory;
        global $profileFactory;

        $doc    = $documentFactory->makeRandomDocument();
        $tagees = $profileFactory->makeProfiles($count);
        $docID  = $doc->getID();

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID     = $toTag->getID();
            $tageeIDs [] = $tageeID;

            $doc->tag($tageeID);
        }

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        // Must force call because tags already gotten when $checkerDoc was created.

        $tagList = $checkerDoc->getTags(getAll: true, force: true);
        // --------------------------------------------------

        $tagArray = array_keys($tagList->getList());

        if ($tagArray != null)
        {
            $this->assertCount($count, $tagArray, "Tag array wrong size (expected: $count, actual" . count($tagArray));

            $this->assertEquals($tageeIDs, $tagArray, 'Tags not equal');
        }
        else
        {
            $this->fail('No array returned');
        }
    }

    /**
     * Tests API_Document->getAttribution()
     *
     * @dataProvider provideUploadedDocument
     */
    public function testGetAttribution(string $name, string $ID, string $GUID, string $city, string $country, string $date, string $sourceURL, string $attribution): void
    {
        $doc = API_Document::get($ID);

        $this->assertNotNull($doc);

        // --------------------------------------------------
        $docAttribution = $doc->getAttribution();
        // --------------------------------------------------

        if ($attribution != null)
        {
            $this->assertNotNull($docAttribution);
        }

        $this->assertEquals($attribution, $docAttribution);
    }

    /**
     * Tests API_Document->getContentType()
     *
     * @dataProvider provideTestDocumentFieldnames
     */
    public function testGetContentType(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory;

        $doc         = $documentFactory->makeDocument($title, $description, $file_name, $file, $labels, $text);
        $docID       = $doc->getID();
        $contentType = $doc->getContentType();

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $checkerContentType = $checkerDoc->getContentType();
        // --------------------------------------------------

        $this->assertEquals($contentType, $checkerContentType);
    }

    /**
     * Tests API_Document->getContentType()
     *
     * @dataProvider provideFileDocument
     */
    public function testGetContentType_FromFile(?string $fileName, ?string $title, ?int $pageCount, ?string $contentType, ?string $text): void
    {
        global $documentFactory;

        $fileName     = $documentFactory->fullFileName($fileName);
        $fileContents = file_get_contents($fileName);

        $doc   = $documentFactory->makeDocumentFromFile($title, $contentType, $fileContents, $fileName);
        $docID = $doc->getID();

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $checkerContentType = $checkerDoc->getContentType();
        // --------------------------------------------------

        $this->assertEquals($contentType, $checkerContentType);
    }

    /**
     * Tests API_Document->getCreatedAt()
     *
     * @dataProvider provideTestDocument
     */
    public function testGetCreatedAt(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory;

        $timeNow = time();

        $doc   = $documentFactory->makeDocument($title, $description, $file_name, $file, $labels, $text);
        $docID = $doc->getID();

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $createDate = $checkerDoc->getCreatedAt();
        // --------------------------------------------------

        $this->assertEqualsWithDelta($timeNow, $createDate, 60, 'Time lapse too long');
    }

    /**
     * Tests API_Document->getDate()
     *
     * @dataProvider provideTestDocumentFieldnames
     */
    public function testGetDate(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory, $dateFactory;

        $createDate = $dateFactory->makeRandomDate();
        $doc        = $documentFactory->makeDocument($title, $description, $file, $file_name, $labels, $text, $createDate);
        $docID      = $doc->getID();

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $checkDate = $checkerDoc->getDate();
        // --------------------------------------------------

        $this->assertNotNull($checkDate);

        $this->assertTrue(API_Date::__equals($createDate, $checkDate));
    }

    /**
     * Tests API_Document->getDescription()
     *
     * @dataProvider provideTestDocument
     */
    public function testGetDescription(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory;

        $doc   = $documentFactory->makeDocument($title, $description, $file_name, $file, $labels, $text);
        $docID = $doc->getID();

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $checkDescription = $checkerDoc->getDescription();
        // --------------------------------------------------

        $this->assertEquals($description, $checkDescription);
    }

    /**
     * Tests API_Document->getGUID()
     *
     * @dataProvider provideUploadedDocument
     */
    public function testGetGUID(string $name, string $ID, string $GUID, string $city, string $country, string $date, string $sourceURL, string $attribution): void
    {
        $doc = API_Document::get($ID);

        $this->assertNotNull($doc);

        // --------------------------------------------------
        $docGUID = $doc->getGUID();
        // --------------------------------------------------

        // Get document using ID, and verifying that it has the same GUID.

        $checkerDoc = API_Document::get($ID);

        $this->assertNotNull($checkerDoc);

        $checkerGUID = $checkerDoc->getGUID();

        $this->assertEquals($docGUID, $checkerGUID);
    }

    /**
     * Tests API_Document->getID()
     *
     * @dataProvider provideTestDocument
     */
    public function testGetID(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory;

        $doc = $documentFactory->makeDocument($title, $description, $file_name, $file, $labels, $text);

        // --------------------------------------------------
        $docID = $doc->getID();
        // --------------------------------------------------

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $checkerID = $checkerDoc->getID();
        // --------------------------------------------------

        $this->assertEquals($docID, $checkerID);
    }

    /**
     * Tests API_Document->getLabels()
     *
     * @dataProvider provideTestDocument
     */
    public function testGetLabels(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory;

        $doc   = $documentFactory->makeDocument($title, $description, $file_name, $file, $labels, $text);
        $docID = $doc->getID();

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        $parsedArray = API_Base::parseArrayOrCommaList($labels);

        // --------------------------------------------------
        $labelArray = $checkerDoc->getLabels();
        // --------------------------------------------------

        $this->assertLabels($parsedArray, $labelArray);
    }

    /**
     * Tests API_Document->getLocation()
     *
     * @dataProvider provideUploadedDocument
     */
    public function testGetLocation(string $name, string $ID, string $GUID, string $city, string $country, string $date, string $sourceURL, string $attribution): void
    {
        $doc = API_Document::get($ID);

        $this->assertNotNull($doc, "Couldn't get document! Test failed before it started.");

        // --------------------------------------------------
        $docLocation = $doc->getLocation();
        // --------------------------------------------------

        if ($city == '' && $country == '')
        {
            // NO location.
            $this->assertNull($docLocation, 'Location did NOT return NULL');
        }
        else
        {
            $this->assertNotNull($docLocation, 'Location returned NULL');

            $this->assertEquals($city, $docLocation->getCity());
            $this->assertEquals($country, $docLocation->getCountry());
        }
    }

    /**
     * Tests API_Document->getPageCount()
     *
     * @dataProvider provideFileDocument
     */
    public function testGetPageCount(?string $fileName, ?string $title, ?int $pageCount, ?string $contentType, ?string $text): void
    {
        global $documentFactory;

        $fileName     = $documentFactory->fullFileName($fileName);
        $fileContents = file_get_contents($fileName);

        $doc   = $documentFactory->makeDocumentFromFile($title, $contentType, $fileContents, $fileName);
        $docID = $doc->getID();

        // --------------------------------------------------

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $checkerPageCount = $checkerDoc->getPageCount();
        // --------------------------------------------------

        $this->assertEquals($pageCount, $checkerPageCount);
    }

    // /**
    // * Tests API_Document->getSizes()
    // * @dataProvider provideTestDocument
    // */
    // public function testGetSizes(string $title, ?string $description, ?string $file_name,
    // ?string $file, ?string $labels, ?string $text): void
    // {
    // global $documentFactory;
    //
    // $doc = $documentFactory->makeDocument ($title, $description, $file_name, $file, $labels, $text);
    // $docID = $doc->getID ();
    //
    // //--------------------------------------------------
    //
    // $checkerDoc = API_Document::get($docID);
    //
    // $this->assertNotNull ($checkerDoc);
    //
    // //--------------------------------------------------
    // $sizes = $checkerDoc->getSizes();
    // //--------------------------------------------------
    //
    // // TODO Auto-generated API_DocumentTest->testGetSizes()
    // $this->markTestIncomplete("getSizes test not completed");
    // }

    /**
     * Tests API_Document->getSourceURL()
     * This test requires a test document ALREADY created on the server.
     *
     * @dataProvider provideUploadedDocument
     */
    public function testGetSourceURL(
        string $name,
        string $ID,
        string $GUID,
        string $city,
        string $country,
        string $date,
        string $sourceURL,
        string $attribution,
    ): void {
        $doc = API_Document::get($ID);

        // --------------------------------------------------
        $docSourceURL = $doc->getSourceURL();
        // --------------------------------------------------

        $this->assertEquals($sourceURL, $docSourceURL);
    }

    /**
     * Tests API_Document->getTitle()
     *
     * @dataProvider provideTestDocument
     */
    public function testGetTitle_FromDocument(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory, $dateFactory;

        $todayDate = $dateFactory->makeTodayDate();
        $title     = "Test getTitle - " . $todayDate->toTimestamp();

        $doc   = $documentFactory->makeDocument($title, $description, $file_name, $file, $labels, $text);
        $docID = $doc->getID();

        // --------------------------------------------------

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $actualTitle = $checkerDoc->getTitle();
        // --------------------------------------------------

        $this->assertEquals($title, $actualTitle);
    }

    /**
     * Tests API_Document->getTitle()
     *
     * @dataProvider provideFileDocument
     */
    public function testGetTitle_FromFileDocument(?string $fileName, ?string $title, ?int $pageCount, ?string $contentType, ?string $text): void
    {
        global $documentFactory;

        $fileName     = $documentFactory->fullFileName($fileName);
        $fileContents = file_get_contents($fileName);

        $doc   = $documentFactory->makeDocumentFromFile($title, $contentType, $fileContents, $fileName);
        $docID = $doc->getID();

        // --------------------------------------------------

        $checkerDoc = API_Document::get($docID);

        $this->assertNotNull($checkerDoc);

        // --------------------------------------------------
        $checkerTitle = $checkerDoc->getTitle();
        // --------------------------------------------------

        $this->assertEquals($title, $checkerTitle);
    }

    /**
     * Tests API_Document->getUpdatedAt()
     *
     * @dataProvider provideTestDocument
     */
    public function testGetUpdatedAt(string $title, ?string $description, ?string $file_name, ?string $file, ?string $labels, ?string $text): void
    {
        global $documentFactory, $dateFactory;

        $randomDate   = $dateFactory->makeRandomDate();
        $delaySeconds = rand(5, 15);
        $doc          = $documentFactory->makeDocument($title, $description, $file_name, $file, $labels, $text);
        $docID        = $doc->getID();

        $nowTime = time();

        sleep($delaySeconds);

        $doc->update('Testitle', $randomDate, 'Testing getUpdatedAt');

        $updatedDoc = API_Document::get($docID);

        $this->assertNotNull($updatedDoc);

        // --------------------------------------------------
        $updatedDate = $updatedDoc->getUpdatedAt();
        // --------------------------------------------------

        $this->assertLessThan(10, $updatedDate - ($nowTime + $delaySeconds));
    }

    /**
     * Tests API_Document->getURL()
     * This test requires a test document ALREADY created on the server.
     *
     * @dataProvider provideUploadedDocument
     */
    public function testGetURL(
        string $name,
        string $ID,
        string $GUID,
        string $city,
        string $country,
        string $date,
        string $sourceURL,
        string $attribution,
    ): void {
        $doc = API_Document::get($ID);

        $this->assertNotNull($doc);

        $expectedURL = 'https://www.geni.com/api/' . $ID;

        // --------------------------------------------------
        $docURL = $doc->getURL();
        // --------------------------------------------------

        $this->assertEquals($expectedURL, $docURL);
    }
}
