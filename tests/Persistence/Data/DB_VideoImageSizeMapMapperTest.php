<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Data;

use ShmuelAKam\Greenjeans\Api\Data\API_VideoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_VideoImageSizeMapMapper;
use ShmuelAKam\Greenjeans\Tests\Persistence\Help\FactoryMapperSizeMaps;

class DB_VideoImageSizeMapMapperTest extends DB_SizeMapMapperTestBase
{
    protected function getMapperClass(): string
    {
        return DB_VideoImageSizeMapMapper::class;
    }

    protected function makeTestObject(): object
    {
        /** @var FactoryMapperSizeMaps $sizeMapFactory */
        global $sizeMapFactory;

        return $sizeMapFactory->makeVideoSizeMap();
    }

    /**
     * Asserts equality between expected and actual API_VideoImageSizeMap objects.
     */
    protected static function assertEqualSizes(mixed $expected, mixed $actual): void
    {
        self::assertInstanceOf(API_VideoImageSizeMap::class, $expected);
        self::assertInstanceOf(API_VideoImageSizeMap::class, $actual);

        self::assertEquals($expected->getLarge(), $actual->getLarge(), 'Large URL mismatch');
        self::assertEquals($expected->getMedium(), $actual->getMedium(), 'Medium URL mismatch');
        self::assertEquals($expected->getOrigin(), $actual->getOrigin(), 'Origin URL mismatch');
        self::assertEquals($expected->getPrint(), $actual->getPrint(), 'Print URL mismatch');
        self::assertEquals($expected->getSmall(), $actual->getSmall(), 'Small URL mismatch');
        self::assertEquals($expected->getThumb(), $actual->getThumb(), 'Thumb URL mismatch');
        self::assertEquals($expected->getThumb2(), $actual->getThumb2(), 'Thumb2 URL mismatch');
    }
}
