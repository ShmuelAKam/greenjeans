<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Photo;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryBase;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryDates;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryPhotos;
use ShmuelAKam\Greenjeans\Tests\Api\Help\FactoryProfiles;

/**
 * API_Photo test case.
 */
class API_PhotoTest extends API_BaseTest
{
    public const TWO_HOURS = 7200;

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
     * Provider for test Photos. These only contain field names.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideTestPhotoFieldnames(): array
    {
        return FactoryPhotos::TEST_PHOTO_FIELDS;
    }

    /**
     * Provider for test Photos.
     * @return array <string, array <string>>
     *
     */
    public static function provideTestPhoto(): array
    {
        return FactoryPhotos::TEST_PHOTOS;
    }

    /**
     * Provider for test photos. These only contain field names.
     *
     * @return array <string, array <string|NULL>>
     */
    public static function provideUpdateFieldData(): array
    {
        return FactoryPhotos::TEST_PHOTO_UPDATE_FIELDS;
    }

    /**
     * Provider for photos from FILE on the Geni server.
     *
     * @return array <string, array<string|NULL>>
     */
    public static function provideUploadedPhoto(): array
    {
        return FactoryPhotos::PHOTOS_UPLOADED_FROM_URL;
    }

    /**
     * Provider single comments.
     *
     * @return array <array <string>>
     */
    public static function provideComment(): array
    {
        return FactoryPhotos::TEST_PHOTO_COMMENTS;
    }

    /**
     * Provider for MULTIPLE comments.
     *
     * @return array <array <int|array <array <string>>>>
     */
    public static function provideComments(): array
    {
        $comments = [
            [ 1,    FactoryPhotos::TEST_PHOTO_COMMENTS ],
            [ 2,    FactoryPhotos::TEST_PHOTO_COMMENTS ],
            [ 3,    FactoryPhotos::TEST_PHOTO_COMMENTS ],
            [ 5,    FactoryPhotos::TEST_PHOTO_COMMENTS ],
            [ 7,    FactoryPhotos::TEST_PHOTO_COMMENTS ],
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
     * @return array <string, string|array<string, int>>
     */
    private function makeTestArray(string $title, ?string $dateJSON, ?string $description): array
    {
        $testArray = [];

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

    private function makePhotoID(string $seed): string
    {
        $idPart = abs(crc32($seed)) % 1000000;

        return "photo-{$idPart}";
    }


    // =====================================================
    // Custom assert functions.

    private function assertTestPhoto(
        API_Photo $new,
        ?string   $title,
        ?string   $albumID,
        ?string   $dateJSON,
        ?API_Date $date,
        ?string   $description,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        if ($title != null)
        {
            $this->assertEquals($title, $new->getTitle());
        }

        // The API calls return the GUID of the album. So compare to it.
        $albumID = $photoFactory->getAlbumGUID();

        $this->assertEquals($albumID, $new->getAlbumID(), "Album ID doesn't match");

        if ($dateJSON != null)
        {
            $newDate = API_Date::fromJSON($dateJSON);

            $this->assertEquals($newDate, $new->getDate(), "JSON Date doesn't match");
        }
        elseif ($date != null)
        {
            $this->assertEquals($date, $new->getDate(), "Date doesn't match");
        }

        if ($description != null)
        {
            $this->assertEquals($description, $new->getDescription(), "Description doesn't match");
        }

        $nowEpoch   = time();
        $photoEpoch = (int) $new->getUpdatedAt();

        $this->assertEqualsWithDelta($nowEpoch, $photoEpoch, 5, "'Updated At' doesn't match");
    }

    private function assertPhotoEpochTime(API_Photo $photo, int $base = null): void
    {
        $created = (int) $photo->getCreatedAt();
        $updated = (int) $photo->getUpdatedAt();

        if ($base == null)
        {
            $base = time();
        }

        $this->assertTrue($created <= $updated);
        $this->assertLessThan(API_PhotoTest::TWO_HOURS, abs($created - $base));
    }

    // =====================================================
    // Test functions.

    /**
     * Tests API_Photo::add()
     *
     * @dataProvider provideTestPhotoFieldnames
     * @dataProvider provideTestPhoto
     */
    public function testAdd(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

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
            $fullFileName = $photoFactory->fullFileName($fileName);

            // Get binary value from file.

            $fileBinary = file_get_contents($fullFileName);
        }
        else
        {
            $fileBinary = null;
        }

        $albumID = $photoFactory->getAlbumID();

        // --------------------------------------------------
        $newPhoto = API_Photo::add($title, $albumID, $dateObj, $description, $fileBinary, $fileName, $fileType);
        // --------------------------------------------------

        $this->assertNotNull($newPhoto, "Failed to ADD photo - $fileName");

        $photoFactory->logCreate($newPhoto->getID());

        $this->assertTestPhoto($newPhoto, $title, $albumID, $dateJSON, null, $description);
    }

    /**
     * Tests API_Photo->updateFromArray()
     *
     * @dataProvider provideTestPhotoFieldnames
     * @dataProvider provideTestPhoto
     */
    public function testUpdateFromArray(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $updateArray = $this->makeTestArray($title, $dateJSON, $description);

        $photo = $photoFactory->makeRandomPhoto();

        $this->assertNotNull($photo);

        // --------------------------------------------------
        $photo->updateFromArray($updateArray);
        // --------------------------------------------------

        $this->assertTestPhoto($photo, $title, null, $dateJSON, null, $description);
    }

    /**
     * Tests API_Photo::newFromArray()
     *
     * @dataProvider provideTestPhotoFieldnames
     * @dataProvider provideTestPhoto
     */
    public function testNewFromArray(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $ignore,
        ?string $ignore2,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $updateArray = $this->makeTestArray($title, $dateJSON, $description);

        $updateArray ['id']         = $this->makePhotoID($description ?? 'Junk Data');
        $updateArray ['updated_at'] = time();
        $updateArray ['album_id']   = $photoFactory->getAlbumGUID();

        // --------------------------------------------------
        $newPhoto = API_Photo::newFromArray($updateArray);
        // --------------------------------------------------

        $this->assertTestPhoto($newPhoto, $title, $updateArray ['album_id'], $dateJSON, null, $description);
    }

    /**
     * Tests API_Photo::get()
     * This test can NOT use a provider because the photo IDs
     * are created at run time. So it must depend on the testAdd test.
     *
     * @dataProvider provideRunCount
     */
    public function testGet(int $ignore): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photoID = $photoFactory->getRandomID();

        // --------------------------------------------------
        $getPhoto = API_Photo::get($photoID);
        // --------------------------------------------------

        $this->assertNotNull($getPhoto);
        $this->assertNotEmpty($getPhoto->getTitle());

        $this->assertPhotoEpochTime($getPhoto);
    }

    /**
     * Tests API_Photo->comment()
     *
     * @dataProvider provideComment
     */
    public function testComment(string $title, string $comment): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photo   = $photoFactory->makeRandomPhoto();
        $photoID = $photo->getID();

        // --------------------------------------------------
        $success = $photo->comment($comment, $title);
        // --------------------------------------------------

        $this->assertTrue($success, 'comment returned FAIL.');

        // Get another copy of the Photo, and isolate the first comment (there's only 1).

        $checkerPhoto = API_Photo::get($photoID);
        $commentsList = $checkerPhoto->getComments();

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $commentsArray */
        $commentsArray = $commentsList->getList();

        $this->assertCount(1, $commentsArray);
        $this->assertEquals($title, $commentsArray[0]->getTitle());
        $this->assertEquals($comment, $commentsArray[0]->getComment());
    }

    /**
     * Tests API_Photo->comment()
     *
     * @dataProvider provideComments
     * @param int   $count
     * @param array <array <string>> $comments
     */
    public function testComment_Multiple(int $count, array $comments): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photo   = $photoFactory->makeRandomPhoto();
        $photoID = $photo->getID();

        $setComments = [];

        for ($i = 0; $i < $count; $i++)
        {
            $commentKey = array_rand($comments, 1);
            $comment    = $comments  [$commentKey];

            // --------------------------------------------------
            $commentSuccess = $photo->comment($comment [1], $comment [0]);
            // --------------------------------------------------

            $this->assertTrue($commentSuccess);

            $setComments [] = $comment;
            unset($comments [$commentKey]);
        }

        // --------------------------------------------------

        $checkerPhoto = API_Photo::get($photoID);

        $resultList = $checkerPhoto->getComments();

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $resultComments */
        $resultComments = $resultList->getList();

        $this->assertCount($count, $resultComments);

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

                    $this->assertEquals($expectedTitle, $result->getTitle());

                    unset($resultComments [$resultKey]);
                    break;
                }
            }
        }

        // If the results array is NOT EMPTY after removing all of the matches,
        // then test has failed.

        $this->assertCount(0, $resultComments);
    }

    /**
     * Tests API_Photo->delete()
     *
     * @dataProvider provideTestPhotoFieldnames
     * @dataProvider provideTestPhoto
     */
    public function testDelete(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photo   = $photoFactory->makeRandomPhoto();
        $photoID = $photo->getID();

        // --------------------------------------------------
        $success = $photo->delete();
        // --------------------------------------------------

        $this->assertTrue($success);

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNull($checkerPhoto);

        $photoFactory->logDelete($photoID);
    }

    /**
     * Tests API_Photo->tag()
     *
     * @dataProvider provideTestPhotoFieldnames
     * @dataProvider provideTestPhoto
     */
    public function testTag(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $photo = $photoFactory->makeRandomPhoto();
        $tagee = $profileFactory->makeRandomProfile();

        $photoID = $photo->getID();
        $tageeID = $tagee->getID();

        // --------------------------------------------------
        $success = $photo->tag($tageeID);
        // --------------------------------------------------

        $this->assertTrue($success, 'Tag call failed');

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        $tagList = $checkerPhoto->getTags(getAll: true);

        $tagArray = $tagList->getList();

        if ($tagArray != null)
        {
            $this->assertCount(1, $tagArray, 'Tag array is empty');
            $this->assertEquals($tageeID, array_key_first($tagArray));
        }
        else
        {
            $this->fail('No array returned');
        }
    }

    /**
     * Tests API_Photo->tag()
     *
     * @dataProvider provideTagCount
     */
    public function testTag_Multiple(int $count): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $photo   = $photoFactory->makeRandomPhoto();
        $tagees  = $profileFactory->makeProfiles($count);
        $photoID = $photo->getID();

        // --------------------------------------------------

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID     = $toTag->getID();
            $tageeIDs [] = $tageeID;

            // --------------------------------------------------
            $success = $photo->tag($tageeID);
            // --------------------------------------------------

            $this->assertTrue($success);
        }

        // --------------------------------------------------

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        $tagList = $checkerPhoto->getTags(true);

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
     * Tests API_Photo->untag()
     *
     * @dataProvider provideTestPhotoFieldnames
     * @dataProvider provideTestPhoto
     */
    public function testUntag(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $photo = $photoFactory->makeRandomPhoto();
        $tagee = $profileFactory->makeRandomProfile();

        $photoID = $photo->getID();
        $tageeID = $tagee->getID();

        $photo->tag($tageeID);

        // Create copy of photo, and UNTAG it.

        $photoCopy = API_Photo::get($photoID);

        if ($photoCopy != null)
        {
            // --------------------------------------------------
            $untagSuccess = $photoCopy->untag($tageeID);
            // --------------------------------------------------
        }
        else
        {
            $this->fail("Couldn't get photoCopy.");
        }

        $this->assertTrue($untagSuccess, 'Untag call failed');

        // --------------------------------------------------
        // Create copy of photo, and verify that it is untagged.

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        $tagList  = $checkerPhoto->getTags(true);
        $tagArray = $tagList->getList();

        $this->assertCount(0, $tagArray, 'Tag array NOT is empty');
    }

    /**
     * Tests API_Photo->tag()
     *
     * @dataProvider provideUntagCount
     */
    public function testUntag_Multiple(int $tagCount, int $untagCount): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $photo   = $photoFactory->makeRandomPhoto();
        $photoID = $photo->getID();
        $tagees  = $profileFactory->makeProfiles($tagCount);

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID     = $toTag->getID();
            $tageeIDs [] = $tageeID;

            $success = $photo->tag($tageeID);

            $this->assertTrue($success, 'Test failed before it started. Couldn\'t tag');
        }

        // --------------------------------------------------
        // Create copy of photo, and UNTAG it in random order.

        $photoCopy = API_Photo::get($photoID);

        if ($photoCopy != null)
        {
            for ($i = 0; $i < $untagCount; $i++)
            {
                $randomKey  = array_rand($tagees);
                $untagee    = $tagees [$randomKey];
                $untageeID  = $untagee->getID();
                $untageeKey = array_search($untageeID, $tageeIDs);

                // --------------------------------------------------
                $success = $photoCopy->untag($untageeID);
                // --------------------------------------------------

                $this->assertTrue($success);

                unset($tagees  [$randomKey]);
                unset($tageeIDs[$untageeKey]);
            }
        }
        else
        {
            $this->fail("Couldn't get copy of photo.");
        }

        // --------------------------------------------------
        // Create a second copy of photo, and verify that it is untagged.

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto, 'No checker photo');

        $tagList  = $checkerPhoto->getTags(getAll: true);
        $tagArray = $tagList->getList();

        // Convert the array values into string keys.

        $tagArray = array_map('strval', array_keys($tagArray));

        sort($tageeIDs);
        sort($tagArray);

        $expectedCount = $tagCount - $untagCount;

        $this->assertCount($expectedCount, $tagArray);

        $this->assertEquals($tageeIDs, $tagArray, 'Tags not equal');
    }

    // =====================================================

    /**
     * Tests API_Photo->update()
     *
     * @dataProvider provideUpdateFieldData
     */
    public function testUpdate(?string $title, ?string $description, ?string $date): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $photo = $photoFactory->makeRandomPhoto();

        $this->assertNotNull($photo, 'Test failed before it started: Failed to create photo.');

        $photoID = $photo->getID();

        if ($date == null)
        {
            $dateObj = $dateFactory->makeRandomDate();
        }
        else
        {
            $dateObj = API_Date::fromJSON($date);
        }

        // --------------------------------------------------
        $success = $photo->update($title, $dateObj, $description);
        // --------------------------------------------------

        $this->assertTrue($success, 'update API call failed.');

        $updatedPhoto = API_Photo::get($photoID);

        $this->assertNotNull($updatedPhoto);

        $this->assertEquals($dateObj, $updatedPhoto->getDate());

        if ($description != null)
        {
            $this->assertEquals($description, $updatedPhoto->getDescription());
        }

        if ($title != null)
        {
            $this->assertEquals($title, $updatedPhoto->getTitle());
        }
    }

    /**
     * Tests API_Photo->getComments()
     * This test is identical to the test for testComment_Multiple
     *
     * @dataProvider provideComments
     * @param int   $count
     * @param array <array <string>> $comments
     */
    public function testGetComments(int $count, array $comments): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photo   = $photoFactory->makeRandomPhoto();
        $photoID = $photo->getID();

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $setComments */
        $setComments = [];

        for ($i = 0; $i < $count; $i++)
        {
            $commentKey = array_rand($comments, 1);
            $comment    = $comments [$commentKey];

            $success = $photo->comment($comment [1], $comment [0]);

            $this->assertTrue($success);

            $setComments [] = $comment;
            unset($comments [$commentKey]);
        }

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        // --------------------------------------------------
        $resultList = $checkerPhoto->getComments();
        // --------------------------------------------------

        /** @var array <\ShmuelAKam\Greenjeans\Api\Data\API_Comment> $resultComments */
        $resultComments = $resultList->getList();

        $this->assertCount($count, $resultComments);

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

                    $this->assertEquals($expectedTitle, $result->getTitle());

                    unset($resultComments [$resultKey]);
                    break;
                }
            }
        }

        // If the results array is NOT EMPTY after removing all if the matches,
        // then test has failed.

        $this->assertCount(0, $resultComments);
    }

    /**
     * Tests API_Photo->getTags()
     * This test is identical to testTag_Multiple.
     *
     * @dataProvider provideTagCount
     */
    public function testGetTags(int $count): void
    {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryProfiles $profileFactory */
        $profileFactory = FactoryBase::getFactory(API_Profile::class);

        $photo   = $photoFactory->makeRandomPhoto();
        $tagees  = $profileFactory->makeProfiles($count);
        $photoID = $photo->getID();

        $tageeIDs = [];
        foreach ($tagees as $toTag)
        {
            $tageeID     = $toTag->getID();
            $tageeIDs [] = $tageeID;

            $photo->tag($tageeID);
        }

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        // --------------------------------------------------
        // Must force call because tags already gotten when $checkerDoc was created.

        $tagList = $checkerPhoto->getTags(getAll: true, force: true);
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
     * Tests API_Photo->getAlbumID()
     * @dataProvider provideTestPhotoFieldnames
     */
    public function testGetAlbumID(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photo   = $photoFactory->makePhoto($title, null, $dateJSON, $description, $fileName, $fileType);
        $photoID = $photo->getID();

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        // --------------------------------------------------

        $checkerAlbumID = $checkerPhoto->getAlbumID();

        // --------------------------------------------------

        $expectedAlbumID = $photoFactory->getAlbumGUID();

        $this->assertEquals($expectedAlbumID, $checkerAlbumID);
    }

    /**
     * =====================================================
     */

    /**
     * Tests API_Photo->getAttribution()
     *
     * @dataProvider provideUploadedPhoto
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
        $photo = API_Photo::get($ID);

        $this->assertNotNull($photo, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $photoAttribution = $photo->getAttribution();
        // --------------------------------------------------

        if ($attribution != null)
        {
            $this->assertEquals($attribution, $photoAttribution, "Expected attribution to be same.");
        }
        else
        {
            $this->assertNull($photoAttribution, 'Expected NULL attribution.');
        }

        $this->assertEquals($attribution, $photoAttribution, 'Attribution not the same');
    }

    //     /**
    //      * Tests API_Photo->getContentType()
    //      * @dataProvider provideTestPhotoFieldnames
    //      */
    //     public function testGetContentType (string $title,    ?string $dateJSON, ?string $description,
    //                                        ?string $fileName, ?string $fileType): void
    //     {
    //         /** @var FactoryPhotos $photoFactory */
    //         $photoFactory = FactoryBase::getFactory(API_Photo::class);


    //         $photo    = $photoFactory->makePhoto ($title, NULL, $dateJSON, $description, $fileName, $fileType);

    //         $this->assertNotNull ($photo, "Failed to create test photo - $fileName.");

    //         $photoID  = $photo->getID ();

    //         // --------------------------------------------------

    //         $checkerPhoto = API_Photo::get($photoID);

    //         $this->assertNotNull ($checkerPhoto);

    //         $checkerContentType = $checkerPhoto->getContentType ();

    //         $this->assertEquals ($fileType, $checkerContentType);
    //     }

    //     /**
    //      * Tests API_Photo->getContentType()
    //      * @dataProvider provideUploadedPhoto
    //      */
    //     public function testGetContentType_ServerFile (string $title, string $ID,      string $GUID, string $contentType,
    //                                                    string $city,  string $country, string $attribution,
    //                                                   ?string $date,  string $created, string $updated): void
    //     {
    //         $checkerPhoto = API_Photo::get($ID);

    //         $this->assertNotNull ($checkerPhoto);

    //         // --------------------------------------------------

    //         $checkerContentType = $checkerPhoto->getContentType ();

    //         // --------------------------------------------------

    //         $this->assertEquals ($contentType, $checkerContentType);
    //     }

    /**
     * Tests API_Photo->getCreatedAt()
     * @dataProvider provideTestPhotoFieldnames
     * @dataProvider provideTestPhoto
     */
    public function testGetCreatedAt(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $timeNow = time();

        $photo = $photoFactory->makePhoto($title, null, null, $description, $fileName, $fileType);

        $this->assertNotNull($photo, "Failed to create test photo - $fileName");

        $photoID = $photo->getID();

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        // --------------------------------------------------
        $createDate = (int) $checkerPhoto->getCreatedAt();
        // --------------------------------------------------

        // Verify that photo creation was done NOW.

        $this->assertEqualsWithDelta($timeNow, $createDate, 60, 'Time lapse too long');
    }

    /**
     * Tests API_Photo->getCreatedAt()
     *
     * @dataProvider provideUploadedPhoto
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
        $checkerPhoto = API_Photo::get($ID);

        $this->assertNotNull($checkerPhoto, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $createDate = $checkerPhoto->getCreatedAt();
        // --------------------------------------------------

        $this->assertNotNull($createDate);

        $this->assertEquals($created, $createDate);
    }

    /**
     * Tests API_Photo->getDate()
     *
     * @dataProvider provideTestPhotoFieldnames
     */
    public function testGetDate(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $createDate     = $dateFactory->makeRandomDate();
        $createDateJSON = $createDate->toJSON();

        $photo   = $photoFactory->makePhoto($title, null, $createDateJSON, $description, $fileName, $fileType);
        $photoID = $photo->getID();

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        // --------------------------------------------------
        $checkDate = $checkerPhoto->getDate();
        // --------------------------------------------------

        $this->assertNotNull($checkDate);

        $this->assertTrue(API_Date::__equals($createDate, $checkDate));
    }

    /**
     * Tests API_Photo->getDate()
     *
     * @dataProvider provideUploadedPhoto
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
        $checkerPhoto = API_Photo::get($ID);

        $this->assertNotNull($checkerPhoto, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $checkDate = $checkerPhoto->getDate();
        // --------------------------------------------------

        if ($date != null)
        {
            $this->assertNotNull($checkDate);

            $dateJSON = $checkDate->toJSON();

            // JSON has leading zeroes to pad the number. Removing them.

            $fixedDate = preg_replace('/^(-)0+(?=\d)/', '$1', $dateJSON); // '-320'

            $this->assertEquals($date, $fixedDate);
        }
        else
        {
            $this->assertNull($checkDate);
        }
    }

    /**
     * Tests API_Photo->getDescription()
     *
     * @dataProvider provideTestPhoto
     */
    public function testGetDescription(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photo   = $photoFactory->makePhoto($title, null, $dateJSON, $description, $fileName, $fileType);
        $photoID = $photo->getID();

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $checkDescription = $checkerPhoto->getDescription();
        // --------------------------------------------------

        $this->assertEquals($description, $checkDescription);
    }

    /**
     * Tests API_Photo->getGUID()
     *
     * @dataProvider provideUploadedPhoto
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
        $photo = API_Photo::get($ID);

        $this->assertNotNull($photo, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $photoGUID = $photo->getGUID();
        // --------------------------------------------------

        // Get photo using ID, and verifying that it has the same GUID.

        $checkerPhoto = API_Photo::get($ID);

        $this->assertNotNull($checkerPhoto);

        $checkerGUID = $checkerPhoto->getGUID();

        $this->assertEquals($photoGUID, $checkerGUID);
    }

    /**
     * Tests API_Photo->getID()
     *
     * @dataProvider provideTestPhoto
     */
    public function testGetID(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);


        $photo = $photoFactory->makePhoto($title, null, $dateJSON, $description, $fileName, $fileType);

        // --------------------------------------------------
        $photoID = $photo->getID();
        // --------------------------------------------------

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        // --------------------------------------------------
        $checkerID = $checkerPhoto->getID();
        // --------------------------------------------------

        $this->assertEquals($photoID, $checkerID);
    }

    /**
     * Tests API_Photo->getLocation()
     *
     * @dataProvider provideUploadedPhoto
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
        $photo = API_Photo::get($ID);

        $this->assertNotNull($photo, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $photoLocation = $photo->getLocation();
        // --------------------------------------------------

        if ($city == '' && $country == '')
        {
            // NO location.
            $this->assertNull($photoLocation, 'Location did NOT return NULL');
        }
        else
        {
            $this->assertNotNull($photoLocation, 'Location returned NULL');

            $this->assertEquals($city, $photoLocation->getCity(), 'City is different');
            $this->assertEquals($country, $photoLocation->getCountry(), 'Country is different');
        }
    }

    // TODO figure out why this is commented.
    //     /**
    //      * Tests API_Photo->getSizes()
    //      * @dataProvider provideTestPhoto
    //      */
    //     public function testGetSizes (string $title, ?string $dateJSON, ?string $description,
    //                                  ?string $fileName, ?string $fileType): void
    //     {
    //         /** @var FactoryPhotos $photoFactory */
    //         $photoFactory = FactoryBase::getFactory(API_Photo::class);
    //
    //
    //         $photo    = $photoFactory->makePhoto ($title, NULL, $dateJSON, $description, $fileName, $fileType);
    //         $photoID  = $photo->getID ();
    //
    //         // --------------------------------------------------
    //
    //         $checkerPhoto = API_Photo::get($photoID);
    //
    //         $this->assertNotNull ($checkerPhoto);
    //
    //         $sizes = $checkerPhoto->getSizes();
    //
    //         print_r($sizes);
    //
    //         $this->fail ("getSizes test not completed");
    //     }

    /**
     * Tests API_Photo->getTitle()
     *
     * @dataProvider provideTestPhoto
     */
    public function testGetTitle(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $todayDate = $dateFactory->makeTodayDate();
        $title     = "Test getTitle - " . $todayDate->toTimestamp();

        $photo   = $photoFactory->makePhoto($title, null, $dateJSON, $description, $fileName, $fileType);
        $photoID = $photo->getID();

        $checkerPhoto = API_Photo::get($photoID);

        $this->assertNotNull($checkerPhoto);

        // --------------------------------------------------
        $actualTitle = $checkerPhoto->getTitle();
        // --------------------------------------------------

        $this->assertEquals($title, $actualTitle);
    }

    /**
     * Tests API_Photo->getTitle()
     *
     * @dataProvider provideUploadedPhoto
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
        $checkerPhoto = API_Photo::get($ID);

        $this->assertNotNull($checkerPhoto, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $checkTitle = $checkerPhoto->getTitle();
        // --------------------------------------------------

        $this->assertEquals($title, $checkTitle);
    }

    /**
     * Tests API_Photo->getUpdatedAt()
     * @dataProvider provideTestPhoto
     * @dataProvider provideTestPhotoFieldnames
     */
    public function testGetUpdatedAt(
        string  $title,
        ?string $dateJSON,
        ?string $description,
        ?string $fileName,
        ?string $fileType,
    ): void {
        /** @var FactoryPhotos $photoFactory */
        $photoFactory = FactoryBase::getFactory(API_Photo::class);

        /** @var FactoryDates $dateFactory */
        $dateFactory = FactoryBase::getFactory(API_Date::class);

        $randomDate   = $dateFactory->makeRandomDate();
        $delaySeconds = rand(5, 15);

        $retryCount = 0;
        do
        {
            $photo = $photoFactory->makePhoto($title, null, $dateJSON, $description, $fileName, $fileType);
        }
        while ($photo == null && $retryCount++ < 3);

        $photoID = $photo->getID();

        // Can't use 'created_at' value, because that is set to beginning of create,
        // and large photos can take an unknown amount of time to finish. Use current
        // time instead.
        $nowTime = time();

        sleep($delaySeconds);

        $photo->update('Testitle', $randomDate, 'Testing getUpdatedAt');

        $updatedPhoto = API_Photo::get($photoID);

        $this->assertNotNull($updatedPhoto);

        // --------------------------------------------------
        $updatedDate = (int) $updatedPhoto->getUpdatedAt();
        // --------------------------------------------------

        $this->assertLessThan(10, $updatedDate - ($nowTime + $delaySeconds));
    }

    /**
     * Tests API_Photo->getUpdatedAt()
     *
     * @dataProvider provideUploadedPhoto
     */
    public function testGetUpdatedAt_FileServer(
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
        $checkerPhoto = API_Photo::get($ID);

        $this->assertNotNull($checkerPhoto, "Couldn't get photo! Test failed before it started.");

        // --------------------------------------------------
        $checkDate = $checkerPhoto->getUpdatedAt();
        // --------------------------------------------------

        $this->assertNotNull($checkDate);

        $this->assertEquals($updated, $checkDate);
    }

    /**
     * Tests API_Photo->getURL()
     * This test requires a test photo ALREADY created on the server.
     *
     * @dataProvider provideUploadedPhoto
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
        $photo = API_Photo::get($ID);

        $this->assertNotNull($photo, "Couldn't get photo! Test failed before it started.");

        $expectedURL = 'https://www.geni.com/api/' . $ID;

        // --------------------------------------------------
        $photoURL = $photo->getURL();
        // --------------------------------------------------

        $this->assertEquals($expectedURL, $photoURL);
    }
}
