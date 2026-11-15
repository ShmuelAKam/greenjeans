<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * This is a PODO that Geni uses to keep track of "images" sizes (e.g. a thumbnail) it
 * keeps for a specific photo. The {@link API_Photo} class has a data-member called
 * $sizes of this type. The object is read-only in that only Geni can determine
 * the values, and it has no setters.
 *
 * Similar classes exist for the other media objects (API_Document and API_Video).
 * {@link https://www.geni.com/platform/developer/help/api?path=photo_image_size_map API_PhotoImageSizeMap}
 */
class API_PhotoImageSizeMap
{
    // URLs of the different video sizes.
    private string $large;
    private string $medium;
    private string $origin;
    private string $print;
    private string $small;
    private string $thumb;
    private string $thumb2;

    //-------------------------------------------------

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->large  = '';
        $this->medium = '';
        $this->origin = '';
        $this->print  = '';
        $this->small  = '';
        $this->thumb  = '';
        $this->thumb2 = '';
    }

    /**
     * Update current object with values returned from the Server.
     * @param array<string, string> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        $this->large  = $arrayValues ['large'] ?? '';
        $this->medium = $arrayValues ['medium'] ?? '';
        $this->origin = $arrayValues ['origin'] ?? '';
        $this->print  = $arrayValues ['print'] ?? '';
        $this->small  = $arrayValues ['small'] ?? '';
        $this->thumb  = $arrayValues ['thumb'] ?? '';
        $this->thumb2 = $arrayValues ['thumb2'] ?? '';
    }

    /**
     * Factory method: Create instance from array
     * @param array<string, string> $arrayValues
     */
    public static function newFromArray(array $arrayValues): API_PhotoImageSizeMap
    {
        $sizeMap = new API_PhotoImageSizeMap();

        $sizeMap->updateFromArray($arrayValues);

        return $sizeMap;
    }

    /**
     * Convert object into array of values for API call.
     * @return array<string, string>
     */
    public function makeParamArray(): array
    {
        static $MEMBER_FIELDS = [
                'large', 'medium', 'origin', 'print', 'small', 'thumb', 'thumb2',
            ];

        $paramArray = [];

        foreach ($MEMBER_FIELDS as $member)
        {
            if ($this->$member != '')
            {
                $paramArray [$member] = $this->$member;
            }
        }

        return $paramArray;
    }

    //-------------------------------------------------

    public function getLarge(): string
    {
        return $this->large;
    }

    public function getMedium(): string
    {
        return $this->medium;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getPrint(): string
    {
        return $this->print;
    }

    public function getSmall(): string
    {
        return $this->small;
    }

    public function getThumb(): string
    {
        return $this->thumb;
    }

    public function getThumb2(): string
    {
        return $this->thumb2;
    }
}
