<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_DocumentImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_PhotoImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_VideoImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\DB_MapperTestBase;

abstract class DB_SizeMapMapperTestBase extends DB_MapperTestBase
{
    /**
     * @var DB_DocumentImageSizeMapMapper|DB_PhotoImageSizeMapMapper|DB_VideoImageSizeMapMapper $mapper
     */
    protected $mapper;

    abstract protected function getMapperClass(): string;

    abstract protected function makeTestObject(): object;

    abstract protected static function assertEqualSizes(mixed $expected, mixed $actual): void;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->getMapper($this->getMapperClass());
    }

    public function testSaveAndReadSizeMap(): void
    {
        $obj = $this->makeTestObject();

        //-------------------------------
        $insertedID = $this->mapper->save($obj);

        $retrieved = $this->mapper->read($insertedID);
        //-------------------------------

        $this->assertGreaterThan(0, $insertedID);
        $this->assertNotNull($retrieved);

        $this->assertEqualSizes($obj, $retrieved);
    }

    public function testDeleteSizeMap(): void
    {
        $obj = $this->makeTestObject();

        $insertedID = $this->mapper->save($obj);
        $this->assertNotNull($this->mapper->read($insertedID));

        //-------------------------------
        $this->mapper->delete($insertedID);
        //-------------------------------

        $this->assertNull($this->mapper->read($insertedID));
    }
}
