<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * API_Location.
 * This is a basic data structure. It does NOT have an API of its own.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=location API_Location}
 */
class API_Location
{
    private string $country;
    private string $county;
    private string $city;
    private string $place_name;
    private string $state;
    private string $street_address1;
    private string $street_address2;
    private string $street_address3;

    // The following fields are generated automatically by the
    // Geni Server. As such, they do NOT have SET functions.

    private float  $longitude;
    private float  $latitude;
    private string $formatted_location;
    private string $country_code;

    /**
     * Create a BLANK object.
     * Use the set functions to assign values.
     */
    public function __construct()
    {
        $this->reset();
    }

    //------------------------------------------------------

    /**
     * Update current object with values returned from the Server.
     * It does NOT validate the data fields, because that would
     * reject already existing data.
     *
     * @param array<string, string|float> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        $this->country    = $arrayValues['country'] ?? '';
        $this->county     = $arrayValues['county'] ?? '';
        $this->city       = $arrayValues['city'] ?? '';
        $this->place_name = $arrayValues['place_name'] ?? '';
        $this->state      = $arrayValues['state'] ?? '';

        $this->street_address1 = $arrayValues['street_address1'] ?? '';
        $this->street_address2 = $arrayValues['street_address2'] ?? '';
        $this->street_address3 = $arrayValues['street_address3'] ?? '';

        $this->longitude          = (float) ($arrayValues['longitude'] ?? -1.0);
        $this->latitude           = (float) ($arrayValues['latitude'] ?? -1.0);
        $this->formatted_location = $arrayValues['formatted_location'] ?? '';
        $this->country_code       = $arrayValues['country_code'] ?? '';
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, string|float> $arrayValues
     * @return API_Location
     */
    public static function newFromArray(array $arrayValues): API_Location
    {
        $location = new API_Location();

        $location->updateFromArray($arrayValues);

        return $location;
    }

    /**
     * Creates array to send to Server in API calls.
     * @return array<string, string>
     */
    public function makeParamArray(): array
    {
        $paramArray = [];

        if ($this->country != '')
        {
            $paramArray ['country'] = $this->country;
        }

        if ($this->county != '')
        {
            $paramArray ['state'] = $this->state;
        }

        if ($this->state != '')
        {
            $paramArray ['county'] = $this->county;
        }

        if ($this->city != '')
        {
            $paramArray ['city'] = $this->city;
        }

        if ($this->place_name != '')
        {
            $paramArray ['place_name'] = $this->place_name;
        }

        if ($this->street_address1 != '')
        {
            $paramArray ['street_address1'] = $this->street_address1;
        }

        if ($this->street_address2 != '')
        {
            $paramArray ['street_address2'] = $this->street_address2;
        }

        if ($this->street_address3 != '')
        {
            $paramArray ['street_address3'] = $this->street_address3;
        }

        return $paramArray;
    }

    /**
     * Reset all fields in object to default values.
     */
    public function reset(): void
    {
        $this->country    = '';
        $this->county     = '';
        $this->city       = '';
        $this->place_name = '';
        $this->state      = '';

        $this->street_address1 = '';
        $this->street_address2 = '';
        $this->street_address3 = '';

        $this->longitude          = -1.0;
        $this->latitude           = -1.0;
        $this->formatted_location = '';
        $this->country_code       = '';
    }

    /**
     * Compare two location objects.
     * @param API_Location $first
     * @param API_Location $second
     * @return bool
     */
    public static function __equals(?API_Location $first, ?API_Location $second): bool
    {
        // Do NOT compare the following fields.
        // They are generated automatically by Geni.
        //      'longitude'
        //      'latitude'
        //      'formatted_location'
        //      'country_code'

        $equals = true;

        if ($first != null && $second != null)
        {
            // Both are NOT NULL.
            if (strcmp($first->country, $second->country) != 0
                || strcmp($first->state, $second->state) != 0
                || strcmp($first->county, $second->county) != 0
                || strcmp($first->city, $second->city) != 0
                || strcmp($first->place_name, $second->place_name) != 0
                || strcmp($first->street_address1, $second->street_address1) != 0
                || strcmp($first->street_address2, $second->street_address2) != 0
                || strcmp($first->street_address3, $second->street_address3) != 0)
            {
                $equals = false;
            }
        }
        else
        {
            if ($first == null && $second == null)
            {
                // Both are NULL.
                $equals = true;
            }
            else
            {
                // Only one is NULL.
                $equals = false;
            }
        }

        return $equals;
    }

    //------------------------------------------------------
    // Getter functions.

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getCounty(): string
    {
        return $this->county;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPlaceName(): string
    {
        return $this->place_name;
    }

    //------------------------------------------------------
    // Getter functions for two "sections" of the address.

    /**
     * @return array<string, string>
     */
    public function getLocation(): array
    {
        $location = [];

        if ($this->country != '')
        {
            $location ['country'   ] = $this->country;
        }

        if ($this->state != '')
        {
            $location ['state'     ] = $this->state;
        }

        if ($this->county != '')
        {
            $location ['county'    ] = $this->county;
        }

        if ($this->city != '')
        {
            $location ['city'      ] = $this->city;
        }

        if ($this->place_name != '')
        {
            $location ['place_name'] = $this->place_name;
        }

        return $location;
    }

    /**
     * @return array<string, string>
     */
    public function getStreetAddress(): array
    {
        $street = [];

        if ($this->street_address1 != '')
        {
            $street ['street_address1'] = $this->street_address1;
        }

        if ($this->street_address2 != '')
        {
            $street ['street_address2'] = $this->street_address2;
        }

        if ($this->street_address3 != '')
        {
            $street ['street_address3'] = $this->street_address3;
        }

        return $street;
    }

    // The following four values are set by Geni's Server. So there are no setters.

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function getFormattedLocation(): string
    {
        return $this->formatted_location;
    }

    //------------------------------------------------------
    // Setter functions for different "sections" of the address.

    /**
     * Set location fields. ALL parameters are OPTIONAL.
     * This function sets ONLY the values passed to it. So you might want to call
     * reset function first, if relevant.
     * @see API_Location::reset
     * @param string $country
     * @param string $state
     * @param string $county
     * @param string $city
     */
    public function setLocation(
        ?string $country = null,
        ?string $state = null,
        ?string $county = null,
        ?string $city = null,
    ): void {
        if ($country != null)
        {
            $this->country = $country;
        }

        if ($state != null)
        {
            $this->state = $state;
        }

        if ($county != null)
        {
            $this->county = $county;
        }

        if ($city != null)
        {
            $this->city = $city;
        }
    }

    /**
     * Set place name. Parameters is OPTIONAL.
     * This function sets ONLY the values passed to it. So you might want to call
     * reset function first, if relevant .
     * @see API_Location::reset
     * @param string $place_name
     */
    public function setPlaceName(?string $place_name): void
    {
        if ($place_name != null)
        {
            $this->place_name = $place_name;
        }
    }

    /**
     * Set street address fields.
     * This function sets ONLY the values passed to it. So you might want to call
     * reset function first, if relevant.
     * @see API_Location::reset
     * @param string $street_address1
     * @param string $street_address2  Optional.
     * @param string $street_address3  Optional.
     */
    public function setStreetAddress(
        string  $street_address1,
        ?string $street_address2 = null,
        string  $street_address3 = null,
    ): void {
        $this->street_address1 = $street_address1;

        if ($street_address2 != null)
        {
            $this->street_address2 = $street_address2;
        }
        if ($street_address3 != null)
        {
            $this->street_address3 = $street_address3;
        }
    }
}
