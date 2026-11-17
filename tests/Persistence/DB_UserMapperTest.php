<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence;

use ShmuelAKam\Greenjeans\Api\API_User;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_UserMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperUsers;

/**
 * DB_UserMapperTest
 * Unit tests for DB_UserMapper persistence layer.
 */
class DB_UserMapperTest extends DB_MapperTestBase
{
    private DB_UserMapper       $mapper;
    private DB_LinkTableMapper  $linkMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper     = $this->getMapper(DB_UserMapper::class);
        $this->linkMapper = $this->getMapper(DB_LinkTableMapper::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->mapper->deleteBy('run_id', $this->getRunID());
    }

    // =====================================================
    // Test Providers

    /**
     * @return array <string, array <string|int|array <string, string>>>
     */
    public static function provideUser(): array
    {
        return FactoryMapperUsers::USER_LIST;
    }

    // =====================================================
    // Custom Assert Functions

    /**
     * Asserts that two API_User instances have identical scalar values and all loaded collections.
     */
    protected function assertEqualsUser(API_User $expected, API_User $actual, string $message = ''): void
    {
        // 1. Scalar properties
        self::assertEquals($expected->getName(), $actual->getName(), $message . ' [name mismatch]');
        self::assertEquals($expected->getAccountType(), $actual->getAccountType(), $message . ' [account_type mismatch]');

        if ($expected->isLoaded('metadata'))
        {
            self::assertEquals(
                $expected->getMetadata(),
                $actual->getMetadata(),
                $message . ' [metadata mismatch]',
            );
        }

        // 2. Labels string collection
        $expectedLabelsLoaded = $expected->isLoaded('my-labels');
        $actualLabelsLoaded   = $actual->isLoaded('my-labels');

        self::assertEquals($expectedLabelsLoaded, $actualLabelsLoaded, $message . ' [my-labels isLoaded mismatch]');

        if ($expectedLabelsLoaded)
        {
            self::assertEquals(
                $expected->getMyLabels()->getList(),
                $actual->getMyLabels()->getList(),
                $message . ' [my-labels contents mismatch]',
            );
        }

        // 3. Map of all 10 relational list getter methods
        $collections = [
            'followed-documents' => 'getFollowedDocuments',
            'followed-profiles'  => 'getFollowedProfiles',
            'followed-projects'  => 'getFollowedProjects',
            'followed-surnames'  => 'getFollowedSurnames',
            'managed-profiles'   => 'getManagedProfiles',
            'max-family'         => 'getMaxFamily',
            'my-albums'          => 'getMyAlbums',
            'uploaded-documents' => 'getUploadedDocuments',
            'uploaded-photos'    => 'getUploadedPhotos',
            'uploaded-videos'    => 'getUploadedVideos',
        ];

        foreach ($collections as $apiName => $getter)
        {
            $expectedLoaded = $expected->isLoaded($apiName);
            $actualLoaded   = $actual->isLoaded($apiName);

            self::assertEquals($expectedLoaded, $actualLoaded, $message . " [{$apiName} isLoaded mismatch]");

            if ($expectedLoaded)
            {
                self::assertEquals(
                    $expected->{$getter}()->getList(),
                    $actual->{$getter}()->getList(),
                    $message . " [{$apiName} list content mismatch]",
                );
            }
        }
    }

    // =====================================================
    // Persistence & Retrieval Tests

    /**
     * Verifies basic create and read operations for the context user.
     * @param string $name
     * @param string $accountType
     * @param array  <string, string> $metadata
     * @param int    $numLabels
     * @param int    $numFollowed
     * @dataProvider provideUser
     */
    public function testCreateAndRead(
        string $name,
        string $accountType,
        array  $metadata,
        int    $numLabels,
        int    $numFollowed,
    ): void {
        /** @var FactoryMapperUsers $userMapFactory */
        global $userMapFactory;

        $user = $userMapFactory->makeMapUser($name, $accountType, $metadata, $numLabels, $numFollowed, full: true);

        //-------------------------------
        // There's only one 'user' so there's no return value.
        $this->mapper->save($user);

        $retrieved = $this->mapper->read(fullRead: true);
        //-------------------------------

        $this->assertNotNull($retrieved, 'Failed to retrieve user.');
        $this->assertEqualsUser($user, $retrieved);
    }

    /**
     * Verifies update behavior on the existing user row for the current run_id context.
     */
    public function testUpdateUser(): void
    {
        /** @var FactoryMapperUsers $userMapFactory */
        global $userMapFactory;

        $initialUser = $userMapFactory->makeMapUser('Initial Name', 'basic', ['theme' => 'light'], full: true);
        $this->mapper->save($initialUser);

        $updatedParams = $userMapFactory->makeParamArray('Updated Name', 'pro', ['theme' => 'dark'], full: true);
        $updatedUser   = API_User::newFromArray($updatedParams, fromDB: true);

        //-------------------------------
        $this->mapper->save($updatedUser);
        //-------------------------------

        $retrieved = $this->mapper->read(fullRead: true);

        $this->assertNotNull($retrieved);
        $this->assertEquals('Updated Name', $retrieved->getName());
        $this->assertEquals('pro', $retrieved->getAccountType());

        if ($retrieved->isLoaded('metadata'))
        {
            $this->assertEquals(['theme' => 'dark'], $retrieved->getMetadata());
        }

    }

    /**
     * Verifies that deleting the user removes the row and clears all associated link tables.
     */
    public function testDeleteUser(): void
    {
        /** @var FactoryMapperUsers $userMapFactory */
        global $userMapFactory;

        $this->linkMapper->clear('geni_user_labels', 'run_id', (string) $this->getRunID());

        $user = $userMapFactory->makeMapUser('User to Delete', 'pro', [], numLabels: 2, numFollowed: 2, full: true);

        $this->mapper->save($user);

        // Confirm user label rows exist.
        $rowCount = $this->mapper->countTableRows('geni_user_labels', 'run_id', $this->runID);
        $this->assertGreaterThan(0, $rowCount, 'Fsiled to find user label rows');

        //-------------------------------
        $this->mapper->delete();
        //-------------------------------

        $retrieved = $this->mapper->read(fullRead: false);
        $rowCount  = $this->mapper->countTableRows('geni_user_labels', 'run_id', $this->runID);

        $this->assertNull($retrieved);
        $this->assertEquals(0, $rowCount, "Failed to delete. Found $rowCount label rows.");
    }
}
