<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Data\API_Gender;

class FactoryProfiles extends FactoryBase
{
    public function __construct()
    {
        parent::__construct(ShredderProfiles::class);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Create the "shell" of a profile.
     * This profile object has NOT yet been created on the server. This means that
     * this shell does NOT have ID values, and can not call the getter methods.
     *
     * These shells are needed for testing API functions that take an API_Profile
     * parameter, that is NOT on the server, such as the addParent function. These
     * functions take the shell values, create a profile on the server, and return
     * the profile that was created. This object now has IDs.
     *
     * Because the profile was created indirectly, the FactoryBase::logCreate
     * function must be called directly AFTER the add.... function call.
     *
     * @param string $firstName
     * @param string $lastName
     * @param bool $alive
     * @param bool $public
     * @param API_Gender $gender
     * @return array <API_Profile|string> Returns the API_Profile object AND the
     *         full-name of the person. It is required to return the name, because
     *         the get functions won't work (because it's a shell object, not yet
     *         on the Server).
     */
    public function makeShell(string $firstName, string $lastName, bool $alive, bool $public, API_Gender $gender): array
    {
        $shell = new API_Profile();

        $shell->setFullName(first_name: $firstName, last_name: $lastName);
        $shell->setGender($gender);
        $shell->setIsAlive($alive);
        $shell->setPublic($public);

        // There's no way to read the name fields from a shell. So also return
        // the name. It is needed for some of the tests.

        $fullName = "$firstName $lastName";

        return [
            $shell,
            $fullName
        ];
    }

    /**
     * Create a randomized "shell" of a profile.
     *
     * @see FactoryProfiles::makeShell for full explanation.
     *
     * @param bool $alive
     * @return array <API_Profile|string> Returns the API_Profile object AND the
     *          full-name of the person. It is required to return the name, because
     *          the get functions won't work.
     */
    public function makeRandomShell(?bool $alive = null): array
    {
        $gender = $this->makeRandomGender();
        $public = (rand(0, 1) == 1 ? true : false);

        if ($alive === null)
        {
            $alive = (rand(0, 1) == 1 ? true : false);
        }

        list($firstName, $lastName) = $this->makeRandomName($gender);

        return $this->makeShell($firstName, $lastName, $alive, $public, $gender);
    }

    /**
     * Make shell with some constant/random values.
     * @return array <API_Profile|string>
     */
    public function makeSemiShell(): array
    {
        $gender = $this->makeRandomGender();
        $public = (rand(0, 1) == 1 ? true : false);
        $alive  = (rand(0, 1) == 1 ? true : false);

        list($firstName, $lastName) = $this->makeRandomName($gender);

        return $this->makeShell($firstName, $lastName, $alive, $public, $gender);
    }

    /**
     * Make shell with constant values.
     * @return array <API_Profile|string>
     */
    public function makeConstantShell(): array
    {
        $firstName = 'Zombie';
        $lastName  = 'Dudette';

        return $this->makeShell($firstName, $lastName, alive: false, public: false, gender: API_Gender::Male);
    }

    // ======================================================

    /**
     * Create a new profile using the given parameters.
     *
     * @param string $firstName
     * @param string $lastName
     * @param bool $alive
     * @param bool $public
     * @param API_Gender $gender
     * @return API_Profile
     */
    public function makeTestProfile(string $firstName, string $lastName, bool $alive, bool $public, API_Gender $gender, bool $onServer = true): API_Profile
    {
        $testProfile = new API_Profile();

        $testProfile->setFullName(first_name: $firstName, last_name: $lastName);
        $testProfile->setGender($gender);
        $testProfile->setIsAlive($alive);
        $testProfile->setPublic($public);

        if ($onServer)
        {
            $testProfile = $testProfile->add();

            $this->logCreate($testProfile->getID());
        }

        return $testProfile;
    }

    /**
     * Create single profile, with "random" values.
     * Done by calling the makeProfiles factory for a single profile.
     *
     * @param bool $alive
     *            (profile must be dead for some tests).
     * @return API_Profile
     */
    public function makeRandomProfile(?bool $alive = null, ?bool $public = null, bool $onServer = true): API_Profile
    {
        $gender = $this->makeRandomGender();

        list($firstName, $lastName) = $this->makeRandomName($gender);

        if ($alive === null)
        {
            $alive = (rand(0, 1) == 1 ? true : false);
        }

        if ($public === null)
        {
            $public = (rand(0, 1) == 1 ? true : false);
        }

        $profile = $this->makeTestProfile($firstName, $lastName, $alive, $public, $gender, $onServer);

        return $profile;
    }

    /**
     * Create an array of IDs /GUIDs of new profiles.
     *
     * Uses const arrays above to create combinations of names. This is done by
     * using modulo on the index with different dividers.
     *
     * @param int  $num     Number of profiles to create.
     * @param bool $allDead all created profiles must be dead (required for some tests).
     * @return array <API_Profile> New created profiles.
     */
    public function makeProfiles(int $num, bool $allDead = false, bool $allPublic = null, bool $onServer = true): array
    {
        $profiles = [];

        for ($ind = 0; $ind < $num; $ind++)
        {
            $profiles[] = $this->makeRandomProfile(!$allDead, $allPublic, $onServer);
        }

        return $profiles;
    }

    /**
     * Make a set of identical profiles (obviously they have different IDs).
     *
     * @param int $num      Number of profiles to create.
     * @param bool $allDead All created profiles must be dead (required for some tests).
     * @return array <API_Profile> New created profiles.
     */
    public function makeClones(int $num, bool $allDead = false): array
    {
        $first    = $this->makeRandomProfile(! $allDead);
        $clones[] = $first;

        $fullName = $first->getFullName();

        $firstName = $fullName->getFirstName();
        $lastName  = $fullName->getLastName();
        $alive     = $first->isAlive();
        $public    = $first->isPublic();
        $gender    = $first->getGender();

        for ($ind = 1; $ind < $num; $ind++)
        {
            $clonedProfile = $this->makeTestProfile($firstName, $lastName, $alive, $public, $gender);
            $clones[]      = $clonedProfile;
        }

        return $clones;
    }

    // ------------------------------------------------------

    /**
     * Create profile based on array of test data.
     *
     * @param array<string, mixed|array<string, mixed>> $profileParam
     * @return API_Profile
     */
    public function makeCloneFromArray(array $profileParam): API_Profile
    {
        // The $skeleton is NOT a server object. It just created to copy the data from.
        // newFromArray can NOT be used directly, as the data in $profileParam is NOT
        // coming from the server.
        $skeleton = API_Profile::newFromArray($profileParam);

        $clone = $this->makeClone($skeleton, fullClone: true);

        return $clone;
    }

    /**
     * Make clone of existing profile, using its ID.
     *
     * @param string $ID
     *            Profile to clone.
     * @param bool $fullClone
     *            When set, copy additional data beyond basics.
     * @return API_Profile
     */
    public function makeCloneFromID(string $ID, bool $fullClone = false): API_Profile
    {
        $base  = API_Profile::get($ID);
        $clone = $this->makeClone($base, $fullClone);

        return $clone;
    }

    protected function makeClone(API_Profile $base, bool $fullClone = false): API_Profile
    {
        $fullName = $base->getFullName();

        $firstName = $fullName->getFirstName();
        $lastName  = $fullName->getLastName();
        $alive     = $base->isAlive();
        $public    = $base->isPublic();
        $gender    = $base->getGender();

        $clone = $this->makeTestProfile($firstName, $lastName, $alive, $public, $gender);

        if ($fullClone)
        {
            // Copy life Events and whatever more.

            $clone->setFullName($firstName, $fullName->getMiddleName(), $lastName, $fullName->getMaidenName(), $fullName->getSuffix(), $fullName->getTitle(), $fullName->getDisplayName());

            $birthEvent   = $base->getBirth();
            $baptismEvent = $base->getBaptism();
            $burialEvent  = $base->getBurial();
            $deathEvent   = $base->getDeath();

            if ($birthEvent != null)
            {
                $clone->setBirth($birthEvent);
            }

            if ($baptismEvent != null)
            {
                $clone->setBaptism($baptismEvent);
            }

            if ($burialEvent != null)
            {
                $clone->setBurial($burialEvent);
            }

            if ($deathEvent != null)
            {
                $clone->setDeath($deathEvent);
            }

            $clone->setCauseOfDeath($base->getCauseOfDeath());

            $currentResidence = $base->getCurrentResidence();
            if ($currentResidence != null)
            {
                $clone->setCurrentResidence($currentResidence);
            }

            $clone->setAboutMe($base->getAboutMe());

            $clone->update();
        }

        return $clone;
    }

    // ======================================================

    public function makeRandomGender(): API_Gender
    {
        $genderInd = rand(0, 2);

        return GENDERS [$genderInd];
    }

    /**
     * @param API_Gender $gender
     * @return array <string> First and last names.
     */
    public function makeRandomName(?API_Gender $gender = null): array
    {
        if ($gender == null)
        {
            $gender = $this->makeRandomGender();
        }

        $genderKey = array_search($gender, GENDERS);

        $firstName = FIRST_NAMES [$genderKey][rand(0, 49)];
        $lastName  = LAST_NAMES  [array_rand(LAST_NAMES)];

        return [
            $firstName,
            $lastName
        ];
    }

    /**
     * @param string $personName
     * @param string $spouseName
     * @param array <string> $list of events BMD to create
     * @return array <string, \ShmuelAKam\Greenjeans\Api\Data\API_Event>
     */
    public function makeSetBMD_Events(string $personName, ?string $spouseName = null, array $list = []): array // of API_Event objects.
    {
        global $eventFactory;

        $eventArray = [];

        if ($spouseName == null)
        {
            $spouseName = $this->makeRandomName();
        }

        $eventFactory->setConsecutiveDates();

        foreach ($list as $eventName)
        {
            $event = null;
            switch ($eventName)
            {
                case 'Birth':
                    $event = $eventFactory->makeBirthEvent($personName);
                    break;
                case 'Baptism':
                    $event = $eventFactory->makeBaptismEvent($personName);
                    break;
                case 'Burial':
                    $event = $eventFactory->makeBurialEvent($personName);
                    break;
                case 'Marriage':
                    $event = $eventFactory->makeMarriageEvent($personName, $spouseName);
                    break;
                case 'Divorce':
                    $event = $eventFactory->makeDivorceEvent($personName, $spouseName);
                    break;
                case 'Death':
                    $event = $eventFactory->makeDeathEvent($personName);
                    break;

                default:
                    break;
            }

            if ($event != null)
            {
                $eventArray[$eventName] = $event;
            }
        }

        $eventFactory->setConsecutiveDates(false);

        return $eventArray;
    }
}

// ====================================================================

// M - Male
// F - Female
// U - Unknown
const TEST_SetGender = [
    //              From                 To
    'M-M' => [  API_Gender::Male,    API_Gender::Male     ],
    'M-F' => [  API_Gender::Male,    API_Gender::Female   ],
    'M-U' => [  API_Gender::Male,    API_Gender::Unknown  ],
    'F-M' => [  API_Gender::Female,  API_Gender::Male     ],
    'F-F' => [  API_Gender::Female,  API_Gender::Female   ],
    'F-U' => [  API_Gender::Female,  API_Gender::Unknown  ],
    'U-M' => [  API_Gender::Unknown, API_Gender::Male     ],
    'U-F' => [  API_Gender::Unknown, API_Gender::Female   ],
    'U-U' => [  API_Gender::Unknown, API_Gender::Unknown  ]
];

const GIRL_NAMES = [
    'Amelia',
    'Olivia',
    'Isla',
    'Lily',
    'Harper',
    'Poppy',
    'Ivy',
    'Isabella',
    'Ava',
    'Bonnie',
    'Freya',
    'Elsie',
    'Daisy',
    'Mia',
    'Rosie',
    'Evie',
    'Charlotte',
    'Willow',
    'Phoebe',
    'Florence',
    'Sienna',
    'Evelyn',
    'Grace',
    'Delilah',
    'Maisie',
    'Sophia',
    'Ella',
    'Lottie',
    'Aria',
    'Millie',
    'Maya',
    'Violet',
    'Arabella',
    'Aurora',
    'Matilda',
    'Sofia',
    'Hallie',
    'Maeve',
    'Mabel',
    'Esme',
    'Ruby',
    'Emily',
    'Luna',
    'Penelope',
    'Ellie',
    'Scarlett',
    'Mila',
    'Isabelle',
    'Sophie',
    'Olive'
];

const BOY_NAMES = [
    'Noah',
    'Theo',
    'Luca',
    'Arthur',
    'Oliver',
    'Archie',
    'George',
    'Leo',
    'Freddie',
    'Arlo',
    'Alfie',
    'Oscar',
    'Teddy',
    'Henry',
    'Albie',
    'Finley',
    'Jude',
    'Charlie',
    'Elijah',
    'Tommy',
    'Harry',
    'Oakley',
    'Hudson',
    'Roman',
    'Rory',
    'Reuben',
    'Louie',
    'Jack',
    'Reggie',
    'Lucas',
    'Theodore',
    'Ezra',
    'Isaac',
    'Thomas',
    'Jacob',
    'Max',
    'James',
    'Ethan',
    'Ronnie',
    'Frankie',
    'Rowan',
    'Harrison',
    'Adam',
    'Riley',
    'William',
    'Jaxon',
    'Grayson',
    'Caleb',
    'Mason',
    'Hunter'
];

const UNKNOWN_NAMES = [
    'Finland',
    'Denmark',
    'Iceland',
    'Sweden',
    'Netherlands',
    'Costa Rica',
    'Norway',
    'Israel',
    'Luxembourg',
    'Mexico',
    'Australia',
    'New Zealand',
    'Switzerland',
    'Belgium',
    'Ireland',
    'Lithuania',
    'Austria',
    'Canada',
    'Slovenia',
    'Czechia',
    'UAE',
    'Germany',
    'UK',
    'USA',
    'Belize',
    'Poland',
    'Taiwan',
    'Uruguay',
    'Kosovo',
    'Kuwait',
    'Serbia',
    'Saudi Arabia',
    'France',
    'Singapore',
    'Romania',
    'Brazil',
    'El Salvador',
    'Spain',
    'Estonia',
    'Italy',
    'Panama',
    'Argentina',
    'Kazakhstan',
    'Guatemala',
    'Chile',
    'Vietnam',
    'Nicaragua',
    'Malta',
    'Thailand',
    'Slovakia'
];

const FIRST_NAMES = [
    0 => GIRL_NAMES,
    1 => BOY_NAMES,
    2 => UNKNOWN_NAMES
];

const LAST_NAMES = [
    'Cohen',
    'Levy',
    'Mizrahi',
    'Peretz',
    'Bitton',
    'Dahan',
    'Avraham',
    'Friedman',
    'Katz',
    'Yosef',
    'Omer',
    'Schwartz',
    'David',
    'Azulay',
    'Malkah',
    'Weiss',
    'Gaby',
    'Shapiro',
    'Ohayon',
    'Reuben',
    'Lavyan',
    'Vaknin',
    'Isaac',
    'Adri',
    'Klein',
    'Yaakov',
    'Segel',
    'Moshe',
    'Ashkenazi'
];

const GENDERS = [
    API_Gender::Female,
    API_Gender::Male,
    API_Gender::Unknown
];
