<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Date;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;

class FactoryEvents extends FactoryBase
{
    public function __construct()
    {
        // API_Event objects are always created as PART of larger objects.
        // So shredding is NOT needed.

        parent::__construct(ShredderDummy::class);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function makeTestEvent(string $name, ?string $description, ?API_Date $date, ?API_Location $location): API_Event
    {
        return new API_Event($name, $description, $date, $location);
    }

    public function makeRandomEvent(): API_Event
    {
        // TODO Finish. Add random event name and description.
        global $dateFactory, $locationFactory;

        $eventName        = 'Josh Brown';
        $eventDescription = 'Coming Home';

        $randomDate     = $dateFactory->makeRandomDate();
        $randomLocation = $locationFactory->makeRandomLocation();

        return $this->makeTestEvent($eventName, $eventDescription, $randomDate, $randomLocation);
    }

    protected function makeBMDEvents(string $name, ?string $description = null, ?API_Date $date = null, ?API_Location $location = null, bool $random = true): API_Event
    {
        global $dateFactory, $locationFactory;

        if ($random)
        {
            $date     = $dateFactory->makeRandomDate();
            $location = $locationFactory->makeRandomLocation();
        }

        return $this->makeTestEvent($name, $description, $date, $location);
    }

    public function makeBirthEvent(?string $child = null, ?string $description = null, ?API_Date $date = null, ?API_Location $location = null, bool $random = true): API_Event
    {
        global $profileFactory;

        if ($child == null)
        {
            $child = implode(' ', $profileFactory->makeRandomName());
        }

        $eventName = "Birth of $child";

        return $this->makeBMDEvents($eventName, $description, $date, $location, $random);
    }

    public function makeBaptismEvent(?string $child = null, ?string $description = null, ?API_Date $date = null, ?API_Location $location = null, bool $random = true): API_Event
    {
        global $profileFactory;

        if ($child == null)
        {
            $child = implode(' ', $profileFactory->makeRandomName());
        }

        $eventName = "Baptism of $child";

        return $this->makeBMDEvents($eventName, $description, $date, $location, $random);
    }

    public function makeBurialEvent(?string $child = null, ?string $description = null, ?API_Date $date = null, ?API_Location $location = null, bool $random = true): API_Event
    {
        global $profileFactory;

        if ($child == null)
        {
            $child = implode(' ', $profileFactory->makeRandomName());
        }

        $eventName = "Burial of $child";

        return $this->makeBMDEvents($eventName, $description, $date, $location, $random);
    }

    public function makeMarriageEvent(?string $spouse1 = null, ?string $spouse2 = null, ?string $description = null, ?API_Date $date = null, ?API_Location $location = null, bool $random = true): API_Event
    {
        global $profileFactory;

        if ($spouse1 == null)
        {
            $spouse1 = implode(' ', $profileFactory->makeRandomName());
        }

        if ($spouse2 == null)
        {
            $spouse2 = implode(' ', $profileFactory->makeRandomName());
        }

        $eventName = "Marriage of $spouse1 and $spouse2";

        return $this->makeBMDEvents($eventName, $description, $date, $location, $random);
    }

    public function makeDivorceEvent(?string $spouse1 = null, ?string $spouse2 = null, ?string $description = null, ?API_Date $date = null, ?API_Location $location = null, bool $random = true): API_Event
    {
        global $profileFactory;

        if ($spouse1 == null)
        {
            $spouse1 = implode(' ', $profileFactory->makeRandomName());
        }

        if ($spouse2 == null)
        {
            $spouse2 = implode(' ', $profileFactory->makeRandomName());
        }

        $eventName = "Divorce of $spouse1 and $spouse2";

        return $this->makeBMDEvents($eventName, $description, $date, $location, $random);
    }

    public function makeDeathEvent(?string $person = null, ?string $description = null, ?API_Date $date = null, ?API_Location $location = null, bool $random = true): API_Event
    {
        global $profileFactory;

        if ($person == null)
        {
            $person = implode(' ', $profileFactory->makeRandomName());
        }

        $eventName = "Death of $person";

        return $this->makeBMDEvents($eventName, $description, $date, $location, $random);
    }

    public static function equalEvents(?API_Event $event1, ?API_Event $event2): bool
    {
        $eventsEqual = false;

        if ($event1 != null && $event2 != null)
        {
            $nameEqual        = ($event1->getName() == $event2->getName());
            $descriptionEqual = ($event1->getDescription() == $event2->getDescription());
            $dateEqual        = ($event1->getDate() == $event2->getDate());

            $locationEqual = FactoryLocations::equalLocations($event1->getLocation(), $event2->getLocation());

            if ($nameEqual && $descriptionEqual && $dateEqual && $locationEqual)
            {
                $eventsEqual = true;
            }
        }
        elseif ($event1 == null && $event2 == null)
        {
            $eventsEqual = true;
        }

        return $eventsEqual;
    }

    public static function equalMarriageDivorceEvents(?API_Event $event1, ?API_Event $event2, string $spouse1, string $spouse2, string $eventType): bool
    {
        $eventsSame = true;

        if ($event1 != null && $event2 != null)
        {
            // Both are NOT NULL. Compare the events details.

            $differences = '';

            if (strcmp($event1->getName(), $event2->getName()) != 0)
            {
                $event2Name = $event2->getName();

                // Event name might have names in opposite order. Create and compare.
                $reverseOrder = "$eventType of $spouse2 and $spouse1";

                if ($reverseOrder != $event2Name)
                {
                    // Check if both have the same last name.
                    $spouseLastName1 = substr($spouse1, strrpos($spouse1, ' ') + 1);
                    $spouseLastName2 = substr($spouse2, strrpos($spouse2, ' ') + 1);

                    if (strcmp($spouseLastName1, $spouseLastName2) == 0)
                    {
                        // Both have same last name. Event name will have the following
                        // format in which the last name is only written once.
                        // Example: 'Marriage of Shmuel and Anat Kam'.

                        $spouseFirstName1 = substr($spouse1, 0, strrpos($spouse1, ' '));
                        $spouseFirstName2 = substr($spouse2, 0, strrpos($spouse2, ' '));

                        $option1 = "$eventType of $spouseFirstName1 and $spouseFirstName2 $spouseLastName1";
                        $option2 = "$eventType of $spouseFirstName2 and $spouseFirstName1 $spouseLastName1";

                        if (strcmp($event2Name, $option1) != 0 && strcmp($event2Name, $option2) != 0)
                        {
                            $eventsSame  = false;
                            $differences = 'name, ';
                        }
                    }
                }
            }

            if ($event1->getDescription() != $event2->getDescription())
            {
                $eventsSame = false;
                $differences .= 'description, ';
            }

            if (! API_Date::__equals($event1->getDate(), $event2->getDate()))
            {
                $eventsSame = false;
                $differences .= 'date, ';
            }

            $event1Location = $event1->getLocation();
            $event2Location = $event2->getLocation();

            if ($event1Location != null && $event2Location != null)
            {
                if ($event1Location->getCountry() != $event2Location->getCountry())
                {
                    $eventsSame = false;
                    $differences .= 'country, ';
                }

                if ($event1Location->getCity() != $event2Location->getCity())
                {
                    $eventsSame = false;
                    $differences .= 'city, ';
                }

                if ($event1Location->getStreetAddress() != $event2Location->getStreetAddress())
                {
                    $eventsSame = false;
                    $differences .= 'street, ';
                }
            }
            elseif ($event1Location != null || $event2Location != null)
            {
                $eventsSame = false;
                $differences .= 'location, ';
            }

            if ($differences != '')
            {
                echo "Differences: $differences";
            }
        }
        elseif ($event1 == null && $event2 == null)
        {
            $eventsSame = true;
        }

        return $eventsSame;
    }

    public function setConsecutiveDates(bool $on = true): void
    {
        global $dateFactory;

        $dateFactory->setConsecutiveDates($on);
    }
}
