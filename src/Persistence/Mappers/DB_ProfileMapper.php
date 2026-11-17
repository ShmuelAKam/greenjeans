<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers;

use PDO;
use ShmuelAKam\Greenjeans\Api\API_Profile;
use ShmuelAKam\Greenjeans\Api\Data\API_Event;
use ShmuelAKam\Greenjeans\Api\Data\API_Location;
use ShmuelAKam\Greenjeans\Api\Data\API_PhotoImageSizeMap;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_LinkTableMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_EventMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_LocationMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_NameMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_PhoneNumberMapper;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Data\DB_PhotoImageSizeMapMapper;

/**
 * DB_ProfileMapper
 * Final implementation for the central SDK object.
 * Handles extensive $values mapping and complex collection delegation.
 */
class DB_ProfileMapper extends DB_BaseMapper
{
    private DB_EventMapper            $eventMapper;
    private DB_LinkTableMapper        $linkMapper;
    private DB_LocationMapper         $locationMapper;
    private DB_NameMapper             $nameMapper;
    private DB_PhoneNumberMapper      $phoneMapper;
    private DB_PhotoImageSizeMapMapper $sizeMapper;
    private \PDOStatement $queryInsert;
    private \PDOStatement $querySelectIDs;
    private \PDOStatement $queryInsertDetails;

    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_profiles', 'profile_id');

        $this->eventMapper    = $persistor->getMapper(DB_EventMapper::class);
        $this->linkMapper     = $persistor->getMapper(DB_LinkTableMapper::class);
        $this->locationMapper = $persistor->getMapper(DB_LocationMapper::class);
        $this->nameMapper     = $persistor->getMapper(DB_NameMapper::class);
        $this->phoneMapper    = $persistor->getMapper(DB_PhoneNumberMapper::class);
        $this->sizeMapper     = $persistor->getMapper(DB_PhotoImageSizeMapMapper::class);

        $sql = "INSERT INTO geni_profiles (
            profile_id, guid, name, display_name, first_name, middle_name, last_name, maiden_name, suffix,
            gender, is_alive, public, claimed, big_tree, locked, master_profile, merge_pending,
            occupation, email, get_email, language, about_me, cause_of_death, merge_note,
            birth_id, death_id, burial_id, baptism_id, residence_id, location_id, mugshot_id,
            created_at, updated_at, documents_updated_at, photos_updated_at, videos_updated_at,
            creator_id, curator_id, merged_into_id, nicknames, run_id
        ) VALUES (
            :id, :guid, :name, :d_name, :f_name, :m_name, :l_name, :maiden, :suffix,
            :gender, :is_alive, :public, :claimed, :big_tree, :locked, :master, :merge_p,
            :occupation, :email, :get_email, :lang, :about, :cause, :merge_n,
            :birth_id, :death_id, :burial_id, :baptism_id, :res_id, :location_id, :mug_id,
            :created, :updated, :doc_up, :photo_up, :video_up,
            :creator, :curator, :merged, :nicknames, :run_id
        )";

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "SELECT birth_id, death_id, burial_id, baptism_id, residence_id, mugshot_id, location_id
                FROM geni_profiles WHERE profile_id = :profile_id AND run_id = :run_id";

        $this->querySelectIDs = $pdo->prepare($sql);
        $this->querySelectIDs->bindValue(':run_id', $run_id, PDO::PARAM_INT);

        $sql = "INSERT INTO geni_profile_details (profile_id, locale, detail_strings, run_id)
                VALUES (:profile_id, :locale, :detail_strings, :run_id)";

        $this->queryInsertDetails = $pdo->prepare($sql);
        $this->queryInsertDetails->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    //------------------------------------------------------

    /**
     * Entry point for persistence.
     */
    public function save(API_Profile $profile): string
    {
        if ($this->exists($profile->getId()))
        {
            $this->update($profile);
        }
        else
        {
            $this->create($profile, clear: false);
        }

        return $profile->getID();
    }

    /**
     * Create logic for Profile.
     */
    protected function create(API_Profile $profile, bool $clear): void
    {
        $profileID = $profile->getId();
        $v         = $profile->makeParamArray(allFields: true, forDB: true);

        // 1. Sub-object Creation
        $baptismId = $this->createEvent($v, 'baptism', $profileID);
        $birthId   = $this->createEvent($v, 'birth', $profileID);
        $burialId  = $this->createEvent($v, 'burial', $profileID);
        $deathId   = $this->createEvent($v, 'death', $profileID);

        $residenceId = null;
        if (isset($v['current_residence']))
        {
            $residence   = API_Location::newFromArray($v['current_residence']);
            $residenceId = $this->locationMapper->create($residence, $profileID);
        }

        $locationId = null;
        if (isset($v['location']))
        {
            $location   = API_Location::newFromArray($v['location']);
            $locationId = $this->locationMapper->create($location, $profileID);
        }

        $mugshotId = null;
        if (isset($v['mugshot_urls']))
        {
            $sizes     = API_PhotoImageSizeMap::newFromArray($v['mugshot_urls']);
            $mugshotId = $this->sizeMapper->create($sizes);
        }

        //----------------------------------------------------------------------

        // 2. Primary Insert
        $this->executeWithBinds($this->queryInsert, [
            'id'          => $profileID,
            'guid'        => $v['guid'] ?? null,
            'name'        => $v['name'] ?? null,
            'd_name'      => $v['display_name'] ?? null,
            'f_name'      => $v['first_name'] ?? null,
            'm_name'      => $v['middle_name'] ?? null,
            'l_name'      => $v['last_name'] ?? null,
            'maiden'      => $v['maiden_name'] ?? null,
            'suffix'      => $v['suffix'] ?? null,
            'gender'      => $v['gender'] ?? null,
            'is_alive'    => isset($v['is_alive']) ? (int) $v['is_alive'] : 0,
            'public'      => isset($v['public']) ? (int) $v['public'] : 0,
            'claimed'     => isset($v['claimed']) ? (int) $v['claimed'] : 0,
            'big_tree'    => isset($v['big_tree']) ? (int) $v['big_tree'] : 0,
            'locked'      => isset($v['locked']) ? (int) $v['locked'] : 0,
            'master'      => isset($v['master_profile']) ? (int) $v['master_profile'] : 0,
            'merge_p'     => isset($v['merge_pending']) ? (int) $v['merge_pending'] : 0,
            'occupation'  => $v['occupation'] ?? null,
            'email'       => $v['email'] ?? null,
            'get_email'   => isset($v['get_email']) ? (int) $v['get_email'] : 0,
            'lang'        => $v['language'] ?? null,
            'about'       => $v['about_me'] ?? null,
            'cause'       => $v['cause_of_death'] ?? null,
            'merge_n'     => $v['merge_note'] ?? null,

            'birth_id'    => $birthId,
            'death_id'    => $deathId,
            'burial_id'   => $burialId,
            'baptism_id'  => $baptismId,
            'res_id'      => $residenceId,
            'mug_id'      => $mugshotId,
            'location_id' => $locationId,

            'created'     => $v['created_at'] ?? null,
            'updated'     => $v['updated_at'] ?? null,
            'doc_up'      => $v['documents_updated_at'] ?? null,
            'photo_up'    => $v['photos_updated_at'] ?? null,
            'video_up'    => $v['videos_updated_at'] ?? null,
            'creator'     => $v['creator'] ?? null,
            'curator'     => $v['curator'] ?? null,
            'merged'      => $v['merged_into'] ?? null,
            'nicknames'   => $v['nicknames'] ?? null
        ]);

        $this->saveCollections($profile, $v, clear: $clear);
    }

    public function read(string $profileID, bool $fullRead): ?API_Profile
    {
        static $docColumnMap = [
            'profile_id' => 'id',
            'text_content' => 'text',
        ];

        $profileRow = $this->readArray($profileID);

        if ($profileRow === null)
        {
            return null;
        }

        $profileRow = $this->mapColumns($profileRow, $docColumnMap);

        if (isset($profileRow['baptism_id']))
        {
            $profileRow['baptism'] = $this->eventMapper->readArray($profileRow['baptism_id']);
        }

        if (isset($profileRow['birth_id']))
        {
            $profileRow['birth'] = $this->eventMapper->readArray($profileRow['birth_id']);
        }

        if (isset($profileRow['burial_id']))
        {
            $profileRow['burial'] = $this->eventMapper->readArray($profileRow['burial_id']);
        }

        if (isset($profileRow['death_id']))
        {
            $profileRow['death'] = $this->eventMapper->readArray($profileRow['death_id']);
        }

        if (isset($profileRow['residence_id']))
        {
            $profileRow['current_residence'] = $this->locationMapper->readArray($profileRow['residence_id']);
        }

        if (isset($profileRow['location_id']))
        {
            $profileRow['location'] = $this->locationMapper->readArray($profileRow['location_id']);
        }

        if (isset($profileRow['mugshot_id']))
        {
            $profileRow['mugshot_urls'] = $this->sizeMapper->readArray($profileRow['mugshot_id']);
        }

        if (isset($profileRow['nicknames']))
        {
            $profileRow['nicknames'] = explode(',', $profileRow['nicknames']);
        }

        $collections = $this->readCollections($profileID);

        $fullRow = array_merge($profileRow, $collections);

        return API_Profile::newFromArray($fullRow, fromDB: true);
    }

    /**
     * Update logic: Sub-object replacement pattern.
     */
    protected function update(API_Profile $profile): void
    {
        $this->delete($profile->getId());

        $this->create($profile, clear: true);
    }

    public function delete(string $profileID): void
    {
        $this->cleanupSubIDs($profileID);

        $this->deleteById($profileID);

        $this->deleteCollections($profileID);
    }

    //------------------------------------------------------

    /**
     * @param API_Profile $profile
     * @param array <string, string|mixed> $profileVals
     * @param bool $clear
     */
    private function saveCollections(API_Profile $profile, array $profileVals, bool $clear): void
    {
        $profileID = $profile->getID();

        if (isset($profileVals ['detail_strings']))
        {
            foreach ($profileVals['detail_strings'] as $locale => $details)
            {
                $this->executeWithBinds($this->queryInsertDetails, [
                    ':profile_id'     => $profileID,
                    ':locale'         => $locale,
                    ':detail_strings' => $details
                ]);
            }
        }

        if (isset($profileVals ['events']))
        {
            $this->eventMapper->createAll($profileVals ['events'], $profileID);
        }

        if (isset($profileVals ['names']))
        {
            $this->nameMapper->createAll($profileVals ['names'], $profileID);
        }

        if (isset($profileVals ['phone_numbers']))
        {
            $this->phoneMapper->createAll($profileVals ['phone_numbers'], $profileID);
        }

        $this->linkMapper->save('geni_profile_managers', 'profile_id', $profileID, 'manager_id', $profileVals ['managers'] ?? [], true);
        $this->linkMapper->save('geni_profile_req_merges', 'profile_id', $profileID, 'other_id', $profileVals ['requested_merges'] ?? [], true);
        $this->linkMapper->save('geni_profile_unions', 'profile_id', $profileID, 'union_id', $profileVals ['unions'] ?? [], true);
        $this->linkMapper->save('geni_profile_projects', 'profile_id', $profileID, 'project_id', $profileVals ['projects'] ?? [], true);

        if ($profile->isLoaded('documents'))
        {
            $this->linkMapper->save(
                'geni_profile_documents',
                'profile_id',
                $profileID,
                'doc_id',
                $profile->getDocuments()->getListIDs(),
                true,
            );
        }

        if ($profile->isLoaded('photos'))
        {
            $this->linkMapper->save(
                'geni_profile_photos',
                'profile_id',
                $profileID,
                'photo_id',
                $profile->getPhotos()->getListIDs(),
                true,
            );
        }

        if ($profile->isLoaded('videos'))
        {
            $this->linkMapper->save(
                'geni_profile_videos',
                'profile_id',
                $profileID,
                'video_id',
                $profile->getVideos()->getListIDs(),
                true,
            );
        }
    }

    private function deleteCollections(string $profileID): void
    {
        $this->phoneMapper->deleteAll($profileID);
        $this->eventMapper->deleteAll($profileID);
        $this->nameMapper->deleteAll($profileID);

        $this->linkMapper->clear('geni_profile_details', 'profile_id', $profileID);
        $this->linkMapper->clear('geni_profile_managers', 'profile_id', $profileID);
        $this->linkMapper->clear('geni_profile_unions', 'profile_id', $profileID);
        $this->linkMapper->clear('geni_profile_req_merges', 'profile_id', $profileID);
        $this->linkMapper->clear('geni_profile_projects', 'profile_id', $profileID);
        $this->linkMapper->clear('geni_profile_documents', 'profile_id', $profileID);
        $this->linkMapper->clear('geni_profile_photos', 'profile_id', $profileID);
        $this->linkMapper->clear('geni_profile_videos', 'profile_id', $profileID);
    }

    /**
     * @param string $profileID
     * @return array <string, string|mixed>
     */
    private function readCollections(string $profileID): array
    {
        /**
         * @var array <string, mixed> $collections
         * The 'mixed' values are one of:
         *      array <string|API_Event|API_PhoneNumber>
         *      array <string, string|API_Name>
         */
        $collections = [];

        /**
         * @var array <array <string, string>> $detailStrings
         */
        $detailStrings = $this->linkMapper->readRow('geni_profile_details', 'profile_id', $profileID);

        if (count($detailStrings) > 0)
        {
            /**
             * @var array <string, string> $allDetails
             */
            $allDetails = [];

            foreach ($detailStrings as $detail)
            {
                $locale = $detail ['locale'];

                $allDetails [$locale] = $detail ['detail_strings'];
            }

            $collections ['detail_strings'] = $allDetails;
        }

        /**
         * @var array <API_Event> $events
         */
        $events = $this->eventMapper->readAll($profileID);

        if (count($events) > 0)
        {
            $collections ['events'] = $events;
        }

        /**
         * @var array <string, \ShmuelAKam\Greenjeans\Api\Data\API_Name> $names
         */
        $names = $this->nameMapper->readAll($profileID);

        if (count($names) > 0)
        {
            $collections ['names'] = $names;
        }

        /**
         * @var array <\ShmuelAKam\Greenjeans\Api\Data\API_PhoneNumber> $numbers
         */
        $numbers = $this->phoneMapper->readAll($profileID);

        if (count($numbers) > 0)
        {
            $collections ['phone_numbers'] = $numbers;
        }

        /**
         * @var array <string> $managers
         */
        $managers = $this->linkMapper->readCol('geni_profile_managers', 'profile_id', $profileID, 'manager_id');

        if (count($managers) > 0)
        {
            $collections ['managers'] = $managers;
        }

        /**
         * @var array <string> $merges
         */
        $merges = $this->linkMapper->readCol('geni_profile_req_merges', 'profile_id', $profileID, 'other_id');

        if (count($merges) > 0)
        {
            $collections ['requested_merges'] = $merges;
        }

        /**
         * @var array <string> $unions
         */
        $unions = $this->linkMapper->readCol('geni_profile_unions', 'profile_id', $profileID, 'union_id');

        if (count($unions) > 0)
        {
            $collections ['unions'] = $unions;
        }

        /**
         * @var array <string> $projects
         */
        $projects = $this->linkMapper->readCol('geni_profile_projects', 'profile_id', $profileID, 'project_id');

        if (count($projects) > 0)
        {
            $collections ['projects'] = $projects;
        }

        /**
         * @var array <string> $documents
         */
        $documents = $this->linkMapper->readCol('geni_profile_documents', 'profile_id', $profileID, 'doc_id');

        if (count($documents) > 0)
        {
            $collections ['documents'] = $documents;
        }

        /**
         * @var array <string> $photos
         */
        $photos = $this->linkMapper->readCol('geni_profile_photos', 'profile_id', $profileID, 'photo_id');

        if (count($photos) > 0)
        {
            $collections ['photos'] = $photos;
        }

        /**
         * @var array <string> $videos
         */
        $videos = $this->linkMapper->readCol('geni_profile_videos', 'profile_id', $profileID, 'video_id');

        if (count($videos) > 0)
        {
            $collections ['videos'] = $videos;
        }

        return $collections;
    }

    private function cleanupSubIDs(string $profileID): void
    {
        $this->executeWithBinds($this->querySelectIDs, ['profile_id' => $profileID]);
        $row = $this->querySelectIDs->fetch(PDO::FETCH_ASSOC);

        if (!$row)
        {
            return;
        }

        if ($row['birth_id'])
        {
            $this->eventMapper->delete((int) $row['birth_id']);
        }

        if ($row['death_id'])
        {
            $this->eventMapper->delete((int) $row['death_id']);
        }

        if ($row['burial_id'])
        {
            $this->eventMapper->delete((int) $row['burial_id']);
        }

        if ($row['baptism_id'])
        {
            $this->eventMapper->delete((int) $row['baptism_id']);
        }

        if ($row['residence_id'])
        {
            $this->locationMapper->delete((int) $row['residence_id']);
        }

        if ($row['mugshot_id'])
        {
            $this->sizeMapper->delete((int) $row['mugshot_id']);
        }

        if ($row['location_id'])
        {
            $this->locationMapper->delete((int) $row['location_id']);
        }
    }

    /**
     * @param array <string, string|array<string, string>> $profileParams
     * @param string $eventName
     * @param string $profileID
     * @return int|NULL
     */
    private function createEvent(array $profileParams, string $eventName, string $profileID): ?int
    {
        $eventID = null;

        if (isset($profileParams [$eventName]))
        {
            $eventObj = API_Event::newFromArray($profileParams [$eventName]);
            $eventID  = $this->eventMapper->create($eventObj, $profileID);
        }

        return $eventID;
    }
}
