<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Api\Data\API_DocumentImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_DocumentImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperBase;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperSizeMaps;

class DB_DocumentImageSizeMapMapperTest extends DB_SizeMapMapperTestBase
{
    protected function getMapperClass(): string
    {
        return DB_DocumentImageSizeMapMapper::class;
    }

    protected function makeTestObject(): object
    {
        /** @var FactoryMapperSizeMaps $sizeMapFactory */
        $sizeMapFactory = FactoryMapperBase::getFactory(API_DocumentImageSizeMap::class);

        return $sizeMapFactory->makeDocumentSizeMap();
    }

    /**
     * Asserts equality between expected and actual API_DocumentImageSizeMap objects (3 fields).
     */
    protected static function assertEqualSizes(mixed $expected, mixed $actual): void
    {
        self::assertInstanceOf(API_DocumentImageSizeMap::class, $expected);
        self::assertInstanceOf(API_DocumentImageSizeMap::class, $actual);

        self::assertEquals($expected->getLarge(), $actual->getLarge(), 'Large URL mismatch');
        self::assertEquals($expected->getOrigin(), $actual->getOrigin(), 'Original URL mismatch');
        self::assertEquals($expected->getThumb(), $actual->getThumb(), 'Thumb URL mismatch');
    }
}
