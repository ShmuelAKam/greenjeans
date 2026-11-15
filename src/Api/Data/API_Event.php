<?php

namespace ShmuelAKam\Greenjeans\Api\Data;

/**
 * API_Event - Used by API_Profile and API_Union to describe life events.
 * Contains optional date and location objects.
 *
 * This basic data structure does NOT have API of its own. It is ALWAYS
 * part of a larger object, such as API_Profile.
 *
 * {@link https://www.geni.com/platform/developer/help/api?path=event API_Event}
 */
class API_Event
{
    /**
     * Title of event.
     * @var string
     */
    private string        $name;

    /**
     * Event's description.
     * @var string
     */
    private string	       $description;

    /**
     * Date of Event (possibly range of dates). Optional.
     * @var API_Date
     */
    private ?API_Date      $date;

    /**
     * Location of Event. Optional.
     * @var API_Location
     */
    private ?API_Location  $location;

    //------------------------------------------------------

    /**
     * Constructor.
     * Sets event name, and the optional values.
     * @param string       $name    Event title/name.
     * @param string       $description Description      (optional).
     * @param API_Date     $date        Event's date     (optional).
     * @param API_Location $location    Event's location (optional).
     */
    public function __construct(string $name, ?string $description, ?API_Date $date, ?API_Location $location)
    {
        $this->name = $name;

        $this->description = $description ?? '';
        $this->date        = $date ?? null;
        $this->location    = $location ?? null;
    }

    //------------------------------------------------------

    /**
     * Update current object with values returned from the Server.
     * It does NOT validate the data fields, because that would
     * reject already existing data.
     *
     * @param array<string, string|array <string, string>> $arrayValues
     */
    public function updateFromArray(array $arrayValues): void
    {
        // This sets ONLY fields that were changed.

        if (isset($arrayValues ['name']))
        {
            $this->name = $arrayValues ['name'];
        }

        if (isset($arrayValues ['description']))
        {
            $this->description = $arrayValues ['description'];
        }

        // Delegate update of sub-objects.
        if (isset($arrayValues ['date']))
        {
            $this->date->updateFromArray($arrayValues ['date']);
        }

        if (isset($arrayValues ['location']))
        {
            $this->location->updateFromArray($arrayValues ['location']);
        }
    }

    /**
     * Factory function.
     * Creates object from values sent by Server (or the persistence layer).
     * @param array<string, string|array <string, string>> $arrayValues
     * @return API_Event
     */
    public static function newFromArray(array $arrayValues): API_Event
    {
        // This sets ALL fields that were changed. Fields not changed have default value.
        $name        = '';
        $description = '';
        $date        = null;
        $location    = null;

        if (isset($arrayValues ['name']))
        {
            $name = $arrayValues ['name'];
        }

        if (isset($arrayValues ['description']))
        {
            $description = $arrayValues ['description'];
        }

        if (isset($arrayValues ['date']) && is_array($arrayValues ['date']))
        {
            $date = API_Date::newFromArray($arrayValues ['date']);
        }

        if (isset($arrayValues ['location']) && is_array($arrayValues ['location']))
        {
            $location = API_Location::newFromArray($arrayValues ['location']);
        }

        $event = new API_Event($name, $description, $date, $location);

        return $event;
    }

    /**
     * Creates array to send to Server in API calls.
     * @return array<string, array<string, array<string, string>|string>|string>
     */
    public function makeParamArray(): array
    {
        $paramArray ['name'] = $this->name;

        if ($this->description != null)
        {
            $paramArray ['description'] = $this->description;
        }

        if ($this->date != null)
        {
            $paramArray ['date'] = $this->date->makeParamArray();
        }

        if ($this->location != null)
        {
            $paramArray ['location'] = $this->location->makeParamArray();
        }

        return $paramArray;
    }

    /**
     * Compare two event objects.
     * @param API_Event $first
     * @param API_Event $second
     * @return bool
     */
    public static function __equals(API_Event $first, API_Event $second): bool
    {
        $eventsSame = true;

        if ($first->name != $second->name    // string
            || $first->description != $second->description    // string
            || !API_Date::__equals($first->date, $second->date))  // API_Date
        {
            $eventsSame = false;
        }
        else
        {
            // Compare API_Location object.
            if (API_Location::__equals($first->location, $second->location) == false)
            {
                $eventsSame = false;
            }
        }

        return $eventsSame;
    }

    /**
     * Returns a chronological sort key for the event based on its associated API_Date.
     */
    public function getSortKey(): string
    {
        return $this->date ? $this->date->getSortKey() : '99999999';
    }

    /**
     * Sorts an array of API_Event objects in-place chronologically by date.
     * Events with missing or incomplete dates are placed at the end.
     *
     * @param array<API_Event> $events List of events to sort.
     */
    public static function sortChronologically(array &$events): void
    {
        usort($events, function (API_Event $a, API_Event $b) {
            return strcmp($a->getSortKey(), $b->getSortKey());
        });
    }

    //------------------------------------------------------
    // Getters & Setters.

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDate(): ?API_Date
    {
        return $this->date;
    }

    public function getLocation(): ?API_Location
    {
        return $this->location;
    }

    public function setName(string        $name): void
    {
        $this->name = $name;
    }

    public function setDescription(string        $description): void
    {
        $this->description = $description;
    }

    public function setDate(?API_Date     $date): void
    {
        $this->date = $date;
    }

    public function setLocation(?API_Location $location): void
    {
        $this->location = $location;
    }
}
