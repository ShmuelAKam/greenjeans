<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_PhotoImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperSizeMaps;

class DB_PhotoImageSizeMapMapperTest extends DB_SizeMapMapperTestBase
{
    protected function getMapperClass(): string
    {
        return DB_PhotoImageSizeMapMapper::class;
    }

    protected function makeTestObject(): object
    {
        /** @var FactoryMapperSizeMaps $sizeMapFactory */
        global $sizeMapFactory;

        return $sizeMapFactory->makePhotoSizeMap();
    }

    /**
     * Asserts equality between expected and actual API_PhotoImageSizeMap objects.
     */
    protected static function assertEqualSizes(mixed $expected, mixed $actual): void
    {
        self::assertInstanceOf(API_PhotoImageSizeMap::class, $expected);
        self::assertInstanceOf(API_PhotoImageSizeMap::class, $actual);

        self::assertEquals($expected->getLarge(), $actual->getLarge(), 'Large URL mismatch');
        self::assertEquals($expected->getMedium(), $actual->getMedium(), 'Medium URL mismatch');
        self::assertEquals($expected->getOrigin(), $actual->getOrigin(), 'Origin URL mismatch');
        self::assertEquals($expected->getPrint(), $actual->getPrint(), 'Print URL mismatch');
        self::assertEquals($expected->getSmall(), $actual->getSmall(), 'Small URL mismatch');
        self::assertEquals($expected->getThumb(), $actual->getThumb(), 'Thumb URL mismatch');
        self::assertEquals($expected->getThumb2(), $actual->getThumb2(), 'Thumb2 URL mismatch');
    }
}
