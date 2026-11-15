<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Video;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryVideos;

/**
 * API_Video test case.
 */
class API_VideoTest extends API_BaseTest
{
    public const FOUR_HOURS = 14400;

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
     * Provider for test Videos. These only contain field names.
     *
     * @return array<string, array <string|NULL>>
     */
    public static function provideTestVideoFieldnames(): array
    {
        return FactoryVideos::TEST_VIDEO_FIELDS;
    }

    /**
     * Provider for test videos.
     * Provider for test videos. These only contain field names.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideUpdateFieldData(): array
    {
        return FactoryVideos::TEST_VIDEO_UPDATE_FIELDS;
    }

    /**
     * Provider for videos from FILE on the Geni server.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideUploadedVideo(): array
    {
        return FactoryVideos::VIDEOS_UPLOADED_FROM_URL;
    }

    /**
     * Provider single comments.
     *
     * @return array <array <string>>
     */
    public static function provideComment(): array
    {
        return FactoryVideos::TEST_VIDEO_COMMENTS;
    }

    /**
     * Provider for MULTIPLE comments.
     *
     * @return array <array <int, int|list<list<string>>>>
     */
    public static function provideComments(): array
    {
        $comments = [
            [ 1, FactoryVideos::TEST_VIDEO_COMMENTS ],
            [ 2, FactoryVideos::TEST_VIDEO_COMMENTS ],
            [ 3, FactoryVideos::TEST_VIDEO_COMMENTS ],
            [ 5, FactoryVideos::TEST_VIDEO_COMMENTS ],
            [ 7, FactoryVideos::TEST_VIDEO_COMMENTS ],
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
     * @return array <array <int, int>>
     */
    public static function provideUntagCount(): array
    {
        return [
        // Tag, untag.
        [ 1,    1 ],
        [ 2,    1 ],
        [ 3,    2 ],
        [ 5,    2 ],
        [ 5,    5 ],
        ];
    }

    // =====================================================
    // Helper functions.

    /**
     * @param string $title
     * @param string $dateJSON
     * @param string $description
     * @return array <string, string|array <string, int>>
     */
    private function makeTestArray(string $title, ?string $dateJSON, ?string $description): array
    {
        $testArray = [];

        $testArray ['id'] = FactoryBase::generatePrefixedId('video');

        if ($title != null)
        {
            $testArray ['title'] = $title;
        }

        if ($description != null)
        {
            $testArray ['description'] = $description;
        }

        if ($dateJSON != null)
        {
            $testArray ['date'] = $this->makeDateArrayFromJSON($dateJSON);
        }

        return $testArray;
    }

    /**
     * @param string $dateJSON
     * @return array <string, int>
     */
    private function makeDateArrayFromJSON(string $dateJSON): array
    {
        $dateFields = ['year', 'month', 'day'];

        $dateVals  = explode('-', $dateJSON);
        $dateArray = [];

        foreach ($dateVals as $index => $field)
        {
            $dateArray [$dateFields [$index]] = (int) $field;
        }

        return $dateArray;
    }

    private static function assertTestVideo(
        API_Video $new,
        ?string   $title,
        ?string   $dateJSON,
        ?API_Date $date,
        ?string   $description,
    ): void {
        if ($title != null)
        {
            self::assertEquals($title, $new->getTitle());
        }

        if ($dateJSON != null)
        {
            $newDate = API_Date::fromJSON($dateJSON);

            self::assertEquals($newDate, $new->getDate(), "JSON Date doesn't match");
        }
        elseif ($date != null)
        {
            self::assertEquals($date, $new->getDate(), "Date doesn't match");
        }

        if ($description != null)
        {
            self::assertEquals($description, $new->getDescription(), "Description doesn't match");
        }
    }

    private static function assertVideoEpochTime(API_Video $video, int $base = null): void
    {
        $created = (int) $video->getCreatedAt();
        $updated = (int) $video->getUpdatedAt();

        if ($base == null)
        {
            $base = time();
        }

        self::assertTrue($created <= $updated);
        self::assertLessThan(API_VideoTest::FOUR_HOURS, abs($created - $base));
    }

    // =====================================================
    // Test functions.

    /**
     * Tests API_Video::add()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testAdd(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory, $dateFactory;

        if ($dateJSON != null)
        {
            // Date in test variable is string stored in JSON format.
            $dateObj = API_Date::fromJSON($dateJSON);
        }
        else
        {
            $dateObj = $dateFactory->makeRandomDate();
        }

        if ($fileName != null)
        {
            $fullFileName = $videoFactory->fullFileName($fileName);

            // Get binary value from file.

            $fileBinary = file_get_contents($fullFileName);
        }
        else
        {
            $fileBinary = null;
        }

        // --------------------------------------------------
        $newVideo = API_Video::add($title, $dateObj, $description, $fileBinary, $fileName, $fileType);
        // --------------------------------------------------

        self::assertNotNull($newVideo, "Failed to ADD video - $fileName");

        $videoFactory->logCreate($newVideo->getID());

        self::assertTestVideo($newVideo, $title, $dateJSON, null, $description);
    }

    /**
     * Tests API_Video->updateFromArray()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testUpdateFromArray(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory;

        $updateArray = $this->makeTestArray($title, $dateJSON, $description);

        $video = $videoFactory->makeRandomVideo();

        self::assertNotNull($video);

        // --------------------------------------------------
        $video->updateFromArray($updateArray);
        // --------------------------------------------------

        self::assertTestVideo($video, $title, $dateJSON, null, $description);
    }

    /**
     * Tests API_Video::newFromArray()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testNewFromArray(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        //* makeTestArray @return array <string, int|string|array <string, int>>

        $updateArray = $this->makeTestArray($title, $dateJSON, $description);

        $todayEpoch = time();

        $updateArray['updated_at'] = $todayEpoch;

        // --------------------------------------------------
        $newVideo = API_Video::newFromArray($updateArray);
        /**
         * expects array<string, array<string, int|string>|string>,
                   array<string, array<string, int>|int|string> given.
         */
        // --------------------------------------------------

        self::assertTestVideo($newVideo, $title, $dateJSON, null, $description);
    }

    /**
     * Tests API_Video::get()
     * This test can NOT use a provider because the video IDs
     * are created at run time. So it must depend on the testAdd test.
     *
     * @dataProvider provideRunCount
     */
    public function testGet(int $ignore): void
    {
        global $videoFactory;

        $videoID = $videoFactory->getRandomID();

        // --------------------------------------------------
        $getVideo = API_Video::get($videoID);
        // --------------------------------------------------

        self::assertNotNull($getVideo);
        self::assertNotEmpty($getVideo->getTitle());

        self::assertVideoEpochTime($getVideo);
    }

    /**
     * Tests API_Video->comment()
     *
     * @dataProvider provideComment
     */
    public function testComment(string $title, string $comment): void
    {
        global $videoFactory;

        $video   = $videoFactory->makeRandomVideo();
        $videoID = $video->getID();

        // --------------------------------------------------
        $success = $video->comment($comment, $title);
        // --------------------------------------------------

        self::assertTrue($success, 'comment returned FAIL.');

        // Get another copy of the Video, and isolate the first comment (there's only 1).

        $checkerVideo = API_Video::get($videoID);

        $commentsList = $checkerVideo->getComments();

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $commentsArray */
        $commentsArray = $commentsList->getList();

        self::assertCount(1, $commentsArray);
        self::assertEquals($title, $commentsArray[0]->getTitle());
        self::assertEquals($comment, $commentsArray[0]->getComment());
    }

    /**
     * Tests API_Video->comment()
     *
     * @dataProvider provideComments
     * @param int $count
     * @param array <array <string>> $comments
     */
    public function testComment_Multiple(int $count, array $comments): void
    {
        global $videoFactory;

        $video   = $videoFactory->makeRandomVideo();
        $videoID = $video->getID();

        $setComments = [];

        for ($i = 0; $i < $count; $i++)
        {
            $commentKey = array_rand($comments, 1);
            $comment    = $comments  [$commentKey];

            // --------------------------------------------------
            $commentSuccess = $video->comment($comment[1], $comment[0]);
            // --------------------------------------------------

            self::assertTrue($commentSuccess);

            $setComments [] = $comment;
            unset($comments [$commentKey]);
        }

        // --------------------------------------------------

        $checkerVideo = API_Video::get($videoID);

        $resultList = $checkerVideo->getComments();

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $resultComments */
        $resultComments = $resultList->getList();

        self::assertCount($count, $resultComments);

        // The two Arrays of set values and returned results are NOT in the
        // same order. So for each set value we need to scan the results,
        // looking for the matching comment. When found compare, if the same,
        // remove it from results.

        foreach ($setComments as $expected)
        {
            $expectedTitle   = $expected[0];
            $expectedComment = $expected[1];

            foreach ($resultComments as $resultKey => $result)
            {
                $actualComment = $result->getComment();

                if ($expectedComment == $actualComment)
                {
                    // No need to assert the Comment itself, because this IF just did that!

                    self::assertEquals($expectedTitle, $result->getTitle());

                    unset($resultComments[$resultKey]);
                    break;
                }
            }
        }

        // If the results array is NOT EMPTY after removing all of the matches,
        // then test has failed.

        self::assertCount(0, $resultComments);
    }

    /**
     * Tests API_Video->delete()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testDelete(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory;

        $video   = $videoFactory->makeRandomVideo();
        $videoID = $video->getID();

        // --------------------------------------------------
        $success = $video->delete();
        // --------------------------------------------------

        self::assertTrue($success);

        $checkerVideo = API_Video::get($videoID);

        self::assertNull($checkerVideo);

        $videoFactory->logDelete($videoID);
    }

    /**
     * Tests API_Video->tag()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testTag(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory;
        global $profileFactory;

        $video = $videoFactory->makeRandomVideo();
        $tagee = $profileFactory->makeRandomProfile();

        $videoID = $video->getID();
        $tageeID = $tagee->getID();

        // --------------------------------------------------
        $success = $video->tag($tageeID);
        // --------------------------------------------------

        self::assertTrue($success, 'Tag call failed');

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        $tagList = $checkerVideo->getTags(getAll: true);

        if ($tagList != null)
        {
            $tagArray = $tagList->getList();

            if ($tagArray != null)
            {
                self::assertCount(1, $tagArray, 'Tag array is empty');
                self::assertEquals($tageeID, array_key_first($tagArray));
            }
            else
            {
                $this->fail('No array returned');
            }
        }
        else
        {
            $this->fail('No list returned');
        }
    }

    /**
     * Tests API_Video->tag()
     *
     * @dataProvider provideTagCount
     */
    public function testTag_Multiple(int $count): void
    {
        global $videoFactory;
        global $profileFactory;

        $video   = $videoFactory->makeRandomVideo();
        $tagees  = $profileFactory->makeProfiles($count);
        $videoID = $video->getID();

        // --------------------------------------------------

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID    = $toTag->getID();
            $tageeIDs[] = $tageeID;

            // --------------------------------------------------
            $success = $video->tag($tageeID);
            // --------------------------------------------------

            self::assertTrue($success);
        }

        // --------------------------------------------------

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        $tagList = $checkerVideo->getTags(true);

        if ($tagList != null)
        {
            $tagArray = array_keys($tagList->getList());

            if ($tagArray != null)
            {
                self::assertCount($count, $tagArray, "Tag array wrong size (expected: $count, actual" . count($tagArray));

                self::assertEquals($tageeIDs, $tagArray, 'Tags not equal');
            }
            else
            {
                $this->fail('No array returned');
            }
        }
        else
        {
            $this->fail('No list returned');
        }
    }

    /**
     * Tests API_Video->untag()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testUntag(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory;
        global $profileFactory;

        $video = $videoFactory->makeRandomVideo();
        $tagee = $profileFactory->makeRandomProfile();

        $videoID = $video->getID();
        $tageeID = $tagee->getID();

        $video->tag($tageeID);

        // Create copy of video, and UNTAG it.

        $videoCopy = API_Video::get($videoID);

        if ($videoCopy != null)
        {
            // --------------------------------------------------
            $untagSuccess = $videoCopy->untag($tageeID);
            // --------------------------------------------------
        }
        else
        {
            $this->fail("Couldn't get videoCopy.");
        }

        self::assertTrue($untagSuccess, 'Untag call failed');

        // --------------------------------------------------
        // Create copy of video, and verify that it is untagged.

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        $tagList = $checkerVideo->getTags(true);

        $tagArray = $tagList->getList();

        self::assertCount(0, $tagArray, 'Tag array NOT is empty');
    }

    /**
     * Tests API_Video->tag()
     *
     * @dataProvider provideUntagCount
     */
    public function testUntag_Multiple(int $tagCount, int $untagCount): void
    {
        global $videoFactory;
        global $profileFactory;

        $video   = $videoFactory->makeRandomVideo();
        $videoID = $video->getID();
        $tagees  = $profileFactory->makeProfiles($tagCount);

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID     = $toTag->getID();
            $tageeIDs [] = $tageeID;

            $success = $video->tag($tageeID);

            self::assertTrue($success, 'Test failed before it started. Couldn\'t tag');
        }

        // --------------------------------------------------
        // Create copy of video, and UNTAG it in random order.

        $videoCopy = API_Video::get($videoID);

        if ($videoCopy != null)
        {
            for ($i = 0; $i < $untagCount; $i++)
            {
                $randomKey  = array_rand($tagees);
                $untagee    = $tagees [$randomKey];
                $untageeID  = $untagee->getID();
                $untageeKey = array_search($untageeID, $tageeIDs);

                // --------------------------------------------------
                $success = $videoCopy->untag($untageeID);
                // --------------------------------------------------

                self::assertTrue($success);

                unset($tagees  [$randomKey]);
                unset($tageeIDs[$untageeKey]);
            }
        }
        else
        {
            $this->fail("Couldn't get copy of video.");
        }

        // --------------------------------------------------
        // Create a second copy of video, and verify that it is untagged.

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo, 'No checker video');

        $tagList  = $checkerVideo->getTags(getAll: true);
        $tagArray = $tagList->getList();

        // Convert the array values into string keys.

        $tagArray = array_map('strval', array_keys($tagArray));

        sort($tageeIDs);
        sort($tagArray);

        $expectedCount = $tagCount - $untagCount;

        self::assertCount($expectedCount, $tagArray);

        self::assertEquals($tageeIDs, $tagArray, 'Tags not equal');
    }

    // =====================================================

    /**
     * Tests API_Video->update()
     *
     * @dataProvider provideUpdateFieldData
     */
    public function testUpdate(?string $title, ?string $description, ?string $date): void
    {
        global $videoFactory, $dateFactory;

        $video = $videoFactory->makeRandomVideo();

        self::assertNotNull($video, 'Test failed before it started: Failed to create video.');

        $videoID = $video->getID();

        if ($date == null)
        {
            $dateObj = $dateFactory->makeRandomDate();
        }
        else
        {
            $dateObj = API_Date::fromJSON($date);
        }

        // --------------------------------------------------
        $success = $video->update($title, $dateObj, $description);
        // --------------------------------------------------

        self::assertTrue($success, 'update API call failed.');

        $updatedVideo = API_Video::get($videoID);

        self::assertNotNull($updatedVideo);

        self::assertEquals($dateObj, $updatedVideo->getDate());

        if ($description != null)
        {
            self::assertEquals($description, $updatedVideo->getDescription());
        }

        if ($title != null)
        {
            self::assertEquals($title, $updatedVideo->getTitle());
        }
    }

    /**
     * Tests API_Video->getComments()
     *
     * @dataProvider provideComments
     * @param int $count
     * @param array <array <string>> $comments
     */
    public function testGetComments(int $count, array $comments): void
    {
        global $videoFactory;

        $video   = $videoFactory->makeRandomVideo();
        $videoID = $video->getID();

        $setComments = [];

        for ($i = 0; $i < $count; $i++)
        {
            $commentKey = array_rand($comments, 1);
            $comment    = $comments [$commentKey];

            $success = $video->comment($comment[1], $comment[0]);

            self::assertTrue($success);

            $setComments [] = $comment;
            unset($comments [$commentKey]);
        }

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        // --------------------------------------------------
        $resultList = $checkerVideo->getComments();
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $resultComments */
        $resultComments = $resultList->getList();

        self::assertCount($count, $resultComments);

        // The two Arrays of set values and returned results are NOT in the
        // same order. So for each set value we need to scan the results,
        // looking for the matching comment. When found compare, if the same,
        // remove it from results.

        foreach ($setComments as $expected)
        {
            $expectedTitle   = $expected[0];
            $expectedComment = $expected[1];

            foreach ($resultComments as $resultKey => $result)
            {
                $actualComment = $result->getComment();

                if ($expectedComment == $actualComment)
                {
                    // No need to assert the Comment itself, because this IF just did that!

                    self::assertEquals($expectedTitle, $result->getTitle());

                    unset($resultComments [$resultKey]);
                    break;
                }
            }
        }

        // If the results array is NOT EMPTY after removing all if the matches,
        // then test has failed.

        self::assertCount(0, $resultComments);
    }

    /**
     * Tests API_Video->getTags()
     * This test is identical to testTag_Multiple.
     *
     * @dataProvider provideTagCount
     */
    public function testGetTags(int $count): void
    {
        global $videoFactory;
        global $profileFactory;

        $video   = $videoFactory->makeRandomVideo();
        $tagees  = $profileFactory->makeProfiles($count);
        $videoID = $video->getID();

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID     = $toTag->getID();
            $tageeIDs [] = $tageeID;

            $video->tag($tageeID);
        }

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        // --------------------------------------------------
        // Must force call because tags already gotten when $checkerDoc was created.

        $tagList = $checkerVideo->getTags(getAll: true, force: true);
        // --------------------------------------------------

        if ($tagList != null)
        {
            $tagArray = array_keys($tagList->getList());

            if ($tagArray != null)
            {
                self::assertCount($count, $tagArray, "Tag array wrong size (expected: $count, actual" . count($tagArray));

                self::assertEquals($tageeIDs, $tagArray, 'Tags not equal');
            }
            else
            {
                $this->fail('No array returned');
            }
        }
        else
        {
            $this->fail('No list returned');
        }
    }

    /**
     * Tests API_Video->getAttribution()
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetAttribution(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $video = API_Video::get($ID);

        self::assertNotNull($video, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $videoAttribution = $video->getAttribution();
        // --------------------------------------------------

        if ($attribution != null)
        {
            self::assertEquals($attribution, $videoAttribution, "Expected attribution to be same.");
        }
        else
        {
            self::assertNull($videoAttribution, 'Expected NULL attribution.');
        }

        self::assertEquals($attribution, $videoAttribution, 'Attribution not the same');
    }

    // /**
    // * Tests API_Video->getContentType()
    // * @dataProvider provideTestVideoFieldnames
    // */
    // public function testGetContentType (string $title, ?string $dateJSON, ?string $description,
    // ?string $fileName, ?string $fileType): void
    // {
    // global $videoFactory;

    // $video = $videoFactory->makeVideo ($title, $dateJSON, $description, $fileName, $fileType);

    // self::assertNotNull ($video, "Failed to create test video - $fileName.");

    // $videoID = $video->getID ();

    // //--------------------------------------------------

    // $checkerVideo = API_Video::get($videoID);

    // self::assertNotNull ($checkerVideo);

    // $checkerContentType = $checkerVideo->getContentType ();

    // self::assertEquals ($fileType, $checkerContentType);
    // }

    // /**
    // * Tests API_Video->getContentType()
    // * @dataProvider provideUploadedVideo
    // */
    //     public function testGetContentType_ServerFile (string $title, string $ID,      string $GUID, string $contentType,
    //                                                    string $city,  string $country, string $attribution,
    //                                                   ?string $date,  string $created, string $updated): void
    // {
    // $checkerVideo = API_Video::get($ID);

    // self::assertNotNull ($checkerVideo);

    // //--------------------------------------------------

    // $checkerContentType = $checkerVideo->getContentType ();

    // //--------------------------------------------------

    // self::assertEquals ($contentType, $checkerContentType);
    // }

    /**
     * Tests API_Video->getCreatedAt()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testGetCreatedAt(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory;

        $timeNow = time();

        $video = $videoFactory->makeVideo($title, null, $description, $fileName, $fileType);

        self::assertNotNull($video, "Failed to create test video - $fileName");

        $videoID = $video->getID();

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        // --------------------------------------------------
        $createDate = (int) $checkerVideo->getCreatedAt();
        // --------------------------------------------------
        // Verify that video creation was done NOW.

        self::assertEqualsWithDelta($timeNow, $createDate, 60, 'Time lapse too long');
    }

    /**
     * Tests API_Video->getCreatedAt()
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetCreatedAt_ServerFile(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $checkerVideo = API_Video::get($ID);

        self::assertNotNull($checkerVideo, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $createDate = $checkerVideo->getCreatedAt();
        // --------------------------------------------------

        self::assertNotNull($createDate);

        self::assertEquals($created, $createDate);
    }

    /**
     * Tests API_Video->getDate()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testGetDate(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory, $dateFactory;

        $createDate     = $dateFactory->makeRandomDate();
        $createDateJSON = $createDate->toJSON();

        $video   = $videoFactory->makeVideo($title, $createDateJSON, $description, $fileName, $fileType);
        $videoID = $video->getID();

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        // --------------------------------------------------
        $checkDate = $checkerVideo->getDate();
        // --------------------------------------------------

        self::assertNotNull($checkDate);

        self::assertTrue(API_Date::__equals($createDate, $checkDate));
    }

    /**
     * Tests API_Video->getDate()
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetDate_ServerFile(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $checkerVideo = API_Video::get($ID);

        self::assertNotNull($checkerVideo, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $checkDate = $checkerVideo->getDate();
        // --------------------------------------------------

        if ($date != null)
        {
            self::assertNotNull($checkDate);

            $dateJSON = $checkDate->toJSON();

            self::assertEquals($date, $dateJSON);
        }
        else
        {
            self::assertNull($checkDate);
        }
    }

    /**
     * Tests API_Video->getDescription()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testGetDescription(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory;

        $video   = $videoFactory->makeVideo($title, $dateJSON, $description, $fileName, $fileType);
        $videoID = $video->getID();

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $checkDescription = $checkerVideo->getDescription();
        // --------------------------------------------------

        self::assertEquals($description, $checkDescription);
    }

    /**
     * Tests API_Video->getGUID()
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetGUID(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $video = API_Video::get($ID);

        self::assertNotNull($video, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $videoGUID = $video->getGUID();
        // --------------------------------------------------

        // Get video using ID, and verifying that it has the same GUID.

        $checkerVideo = API_Video::get($ID);

        self::assertNotNull($checkerVideo);

        $checkerGUID = $checkerVideo->getGUID();

        self::assertEquals($videoGUID, $checkerGUID);
    }

    /**
     * Tests API_Video->getID()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testGetID(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory;

        $video = $videoFactory->makeVideo($title, $dateJSON, $description, $fileName, $fileType);

        // --------------------------------------------------
        $videoID = $video->getID();
        // --------------------------------------------------

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        // --------------------------------------------------
        $checkerID = $checkerVideo->getID();
        // --------------------------------------------------

        self::assertEquals($videoID, $checkerID);
    }

    /**
     * Tests API_Video->getLocation()
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetLocation(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $video = API_Video::get($ID);

        self::assertNotNull($video, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $videoLocation = $video->getLocation();
        // --------------------------------------------------

        if ($city == '' && $country == '')
        {
            // NO location.
            self::assertNull($videoLocation, 'Location did NOT return NULL');
        }
        else
        {
            self::assertNotNull($videoLocation, 'Location returned NULL');

            self::assertEquals($city, $videoLocation->getCity(), 'City is different');
            self::assertEquals($country, $videoLocation->getCountry(), 'Country is different');
        }
    }

    // /**
    // * Tests API_Video->getSizes()
    // * @dataProvider provideTestVideoFieldnames
    // */
    // public function testGetSizes (string $title, ?string $dateJSON, ?string $description,
    // ?string $fileName, ?string $fileType): void
    // {
    // global $videoFactory;
    //
    // $video = $videoFactory->makeVideo ($title, $dateJSON, $description, $fileName, $fileType);
    // $videoID = $video->getID ();
    //
    // //--------------------------------------------------
    //
    // $checkerVideo = API_Video::get($videoID);
    //
    // self::assertNotNull ($checkerVideo);
    //
    // $sizes = $checkerVideo->getSizes();
    //
    // print_r($sizes);
    //
    // $this->fail ("getSizes test not completed");
    // }

    /**
     * Tests API_Video->getTitle()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testGetTitle(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory, $dateFactory;

        $todayDate = $dateFactory->makeTodayDate();
        $title     = "Test getTitle - " . $todayDate->toTimestamp();

        $video   = $videoFactory->makeVideo($title, $dateJSON, $description, $fileName, $fileType);
        $videoID = $video->getID();

        $checkerVideo = API_Video::get($videoID);

        self::assertNotNull($checkerVideo);

        // --------------------------------------------------
        $actualTitle = $checkerVideo->getTitle();
        // --------------------------------------------------

        self::assertEquals($title, $actualTitle);
    }

    /**
     * Tests API_Video->getTitle()
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetTitle_FileServer(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $checkerVideo = API_Video::get($ID);

        self::assertNotNull($checkerVideo, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $checkTitle = $checkerVideo->getTitle();
        // --------------------------------------------------

        self::assertEquals($title, $checkTitle);
    }

    /**
     * Tests API_Video->getUpdatedAt()
     *
     * @dataProvider provideTestVideoFieldnames
     */
    public function testGetUpdatedAt(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        global $videoFactory, $dateFactory;

        $randomDate   = $dateFactory->makeRandomDate();
        $delaySeconds = rand(5, 15);

        $retryCount = 0;
        do
        {
            $video = $videoFactory->makeVideo($title, $dateJSON, $description, $fileName, $fileType);
        }
        while ($video == null && $retryCount++ < 3);

        $videoID = $video->getID();

        // Can't use 'created_at' value, because that is set to beginning of create,
        // and large videos can take an unknown amount of time to finish. Use current
        // time instead.
        $nowTime = time();

        sleep($delaySeconds);

        $video->update('Testitle', $randomDate, 'Testing getUpdatedAt');

        $updatedVideo = API_Video::get($videoID);

        self::assertNotNull($updatedVideo);

        // --------------------------------------------------
        $updatedDate = (int) $updatedVideo->getUpdatedAt();
        // --------------------------------------------------

        self::assertLessThan(10, $updatedDate - ($nowTime + $delaySeconds));
    }

    /**
     * Tests API_Video->getUpdatedAt()
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetUpdatedAt_ServerFile(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $checkerVideo = API_Video::get($ID);

        self::assertNotNull($checkerVideo, "Couldn't get video! Test failed before it started.");

        // --------------------------------------------------
        $checkDate = $checkerVideo->getUpdatedAt();
        // --------------------------------------------------

        self::assertNotNull($checkDate);

        self::assertEquals($updated, $checkDate);
    }

    /**
     * Tests API_Video->getURL()
     * This test requires a test video ALREADY created on the server.
     *
     * @dataProvider provideUploadedVideo
     */
    public function testGetURL(
        string  $title,
        string  $ID,
        string  $GUID,
        string  $contentType,
        string  $city,
        string  $country,
        ?string $attribution,
        ?string $date,
        string  $created,
        string  $updated,
    ): void {
        $video = API_Video::get($ID);

        self::assertNotNull($video, "Couldn't get video! Test failed before it started.");

        $expectedURL = 'https://www.geni.com/api/' . $ID;

        // --------------------------------------------------
        $videoURL = $video->getURL();
        // --------------------------------------------------

        self::assertEquals($expectedURL, $videoURL);
    }
}
