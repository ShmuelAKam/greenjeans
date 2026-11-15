<?php

namespace ShmuelAKam\Greenjeans\Tests\Api;

use ShmuelAKam\Greenjeans\Api\API_Stats;

const STATS_NAME = "World Family Tree";

const STATS_URL = "https://www.geni.com/api/stats/world-family-tree";

/**
 * API_Stats test case.
 */
class API_StatsTest extends API_BaseTest
{
    private ?API_Stats $aPI_Stats;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aPI_Stats = new API_Stats();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->aPI_Stats = null;

        parent::tearDown();
    }

    /**
     * Tests API_Stats->getStats()
     */
    public function testGetStats(): void
    {
        $statsValues = $this->aPI_Stats->getStats();

        $this->assertArrayHasKey('name', $statsValues);
        $this->assertEquals(STATS_NAME, $statsValues['name']);

        $this->assertArrayHasKey('url', $statsValues);
        $this->assertEquals(STATS_URL, $statsValues['url']);
    }

    /**
     * Tests API_Stats->getWFTStats()
     */
    public function testGetWFT_Stats(): void
    {
        $stats = $this->aPI_Stats->getWFTStats();

        $this->assertArrayHasKey('formatted_size', $stats);
        $this->assertArrayHasKey('size', $stats);
    }
}
