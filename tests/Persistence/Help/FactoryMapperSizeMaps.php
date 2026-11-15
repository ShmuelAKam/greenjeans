<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_DocumentImageSizeMap;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;
use ShmuelAKam\Greenjeans\Api\Data\API_VideoImageSizeMap;

/**
 * Factory for creating test size-map instances for Option B unit tests.
 */
class FactoryMapperSizeMaps extends FactoryMapperBase
{
    /**
     * Complete 7-field size map payload (for Video and Photo).
     */
    public const FULL_SIZE_PRESET = [
        'large'  => 'https://images.geni.com/media/large_123.jpg',
        'medium' => 'https://images.geni.com/media/medium_123.jpg',
        'origin' => 'https://images.geni.com/media/origin_123.jpg',
        'print'  => 'https://images.geni.com/media/print_123.jpg',
        'small'  => 'https://images.geni.com/media/small_123.jpg',
        'thumb'  => 'https://images.geni.com/media/thumb_123.jpg',
        'thumb2' => 'https://images.geni.com/media/thumb2_123.jpg',
    ];

    /**
     * Compact 3-field size map payload (for Document or 3-field variant).
     */
    public const COMPACT_SIZE_PRESET = [
        'large'  => 'https://images.geni.com/media/doc_large_123.jpg',
        'medium' => 'https://images.geni.com/media/doc_medium_123.jpg',
        'thumb'  => 'https://images.geni.com/media/doc_thumb_123.jpg',
    ];

    public function makeVideoSizeMap(): API_VideoImageSizeMap
    {
        return API_VideoImageSizeMap::newFromArray(self::FULL_SIZE_PRESET);
    }

    public function makePhotoSizeMap(): API_PhotoImageSizeMap
    {
        return API_PhotoImageSizeMap::newFromArray(self::FULL_SIZE_PRESET);
    }

    public function makeDocumentSizeMap(): API_DocumentImageSizeMap
    {
        return API_DocumentImageSizeMap::newFromArray(self::COMPACT_SIZE_PRESET);
    }
}
