<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * This is a PODO that Geni uses to keep track of "images" sizes (e.g. a thumbnail) it
 * keeps for a specific document. The {@link API_Document} class has a data-member called
 * $sizes of this type. The object is read-only in that only Geni can determine
 * the values, and it has no setters.
 *
 * Similar classes exist for the other media objects (API_Photo and API_Video).
 * {@link https://www.geni.com/platform/developer/help/api?path=document_image_size_map}
 */
class API_DocumentImageSizeMap
{
    private string $large;
    private string $origin;
    private string $thumb;

    //-------------------------------------------------

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->large  = '';
        $this->origin = '';
        $this->thumb  = '';
    }

    /**
     * Update current object with values returned from the Server.
     * @param array<string, string> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        $this->large  = $arrayValues ['large'] ?? '';
        $this->origin = $arrayValues ['origin'] ?? '';
        $this->thumb  = $arrayValues ['thumb'] ?? '';
    }

    /**
     * Factory method: Create instance from array
     * @param array<string, string> $arrayValues
     */
    public static function newFromArray(array $arrayValues): API_DocumentImageSizeMap
    {
        $sizeMap = new API_DocumentImageSizeMap();

        $sizeMap->updateFromArray($arrayValues);

        return $sizeMap;
    }

    //-------------------------------------------------

    public function getLarge(): string
    {
        return $this->large;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getThumb(): string
    {
        return $this->thumb;
    }
}
