<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * API_PhoneNumber
 * This basic data structure does NOT have API of its own.
 * {@link https://www.geni.com/platform/developer/help/api?path=phone_number API_PhoneNumber}
 */
class API_PhoneNumber
{
    /**
     * Phone number area code
     * @var string
     */
    private string $area_code;

    /**
     * Phone number country code
     * @var string
     */
    private string $country_code;

    /**
     * Phone number
     * @var string
     */
    private string $number;

    /**
     * Phone number type
     * @var string
     */
    private string $type;

    //------------------------------------------------------

    /**
     * Constructor.
     * @param string $areaCode
     * @param string $countryCode
     * @param string $number
     * @param string $type
     */
    public function __construct(string $areaCode, string $countryCode, string $number, string $type)
    {
        $this->area_code    = $areaCode;
        $this->country_code = $countryCode;
        $this->number       = $number;
        $this->type         = $type;
    }

    /**
     * Update current object with values returned from the Server.
     * It does NOT validate the data fields, because that would
     * reject already existing data.
     *
     * @param array<string, string> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        $this->area_code    = $arrayValues['areaCode'] ?? '';
        $this->country_code = $arrayValues['countryCode'] ?? '';
        $this->number       = $arrayValues['number'] ?? '';
        $this->type         = $arrayValues['type'] ?? '';
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, string> $arrayValues
     * @return API_PhoneNumber
     */
    public static function newFromArray(array $arrayValues): API_PhoneNumber
    {
        $areaCode    = $arrayValues['area_code'] ?? '';
        $countryCode = $arrayValues['country_code'] ?? '';
        $number      = $arrayValues['number'] ?? '';
        $type        = $arrayValues['type'] ?? '';

        $phoneNumber = new API_PhoneNumber($areaCode, $countryCode, $number, $type);

        return $phoneNumber;
    }

    /**
     * Creates array to send to Server in API calls.
     * @return array<string>
     */
    public function makeParamArray(): array
    {
        $paramArray = [
            'area_code'    => $this->area_code,
            'country_code' => $this->country_code,
            'number'       => $this->number,
            'type'         => $this->type
        ];

        return $paramArray;
    }

    //------------------------------------------------------
    // Getters & Setters.

    public function getAreaCode(): string
    {
        return $this->area_code;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setAreaCode(string $areaCode): void
    {
        $this->area_code = $areaCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->country_code = $countryCode;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
