<?php

namespace ShmuelAKam\Greenjeans\Persistence\Mappers\Data;

use PDO;
use PDOStatement;
use ShmuelAKam\Greenjeans\Api\Data\API_Name;
use ShmuelAKam\Greenjeans\Persistence\Manage\DB_Persistor;
use ShmuelAKam\Greenjeans\Persistence\Mappers\Core\DB_BaseMapper;

/**
 * DB_NameMapper
 * Handles persistence for the API_Name sub-object.
 * Nicknames are stored as a comma-separated string per the makeParamArray logic.
 */
class DB_NameMapper extends DB_BaseMapper
{
    private PDOStatement $queryInsert;

    /**
     * @param DB_Persistor $persistor
     * @param PDO $pdo
     * @param int $run_id
     * @phpstan-ignore constructor.unusedParameter
     */
    public function __construct(DB_Persistor $persistor, PDO $pdo, int $run_id)
    {
        parent::__construct($pdo, $run_id, 'geni_profile_names_localized', 'profile_id');

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $baseSql = "INSERT INTO geni_profile_names_localized (
                    profile_id, locale, display_name, first_name, last_name, maiden_name,
                    middle_name, suffix, nicknames, title, run_id
                ) VALUES (
                    :profile_id, :locale, :display_name, :first_name, :last_name, :maiden_name,
                    :middle_name, :suffix, :nicknames, :title, :run_id
                )";

        $sql = match ($driver)
        {
            'sqlite' => $baseSql . "
            ON CONFLICT (profile_id, locale, run_id) DO UPDATE SET
                display_name = EXCLUDED.display_name,
                first_name   = EXCLUDED.first_name,
                last_name    = EXCLUDED.last_name,
                maiden_name  = EXCLUDED.maiden_name,
                middle_name  = EXCLUDED.middle_name,
                suffix       = EXCLUDED.suffix,
                nicknames    = EXCLUDED.nicknames,
                title        = EXCLUDED.title",

            'mysql' => $baseSql . "
            AS new_vals ON DUPLICATE KEY UPDATE
                display_name = new_vals.display_name,
                first_name   = new_vals.first_name,
                last_name    = new_vals.last_name,
                maiden_name  = new_vals.maiden_name,
                middle_name  = new_vals.middle_name,
                suffix       = new_vals.suffix,
                nicknames    = new_vals.nicknames,
                title        = new_vals.title",

            default => throw new \RuntimeException("Unsupported PDO driver: {$driver}"),
        };

        $this->queryInsert = $pdo->prepare($sql);
        $this->queryInsert->bindValue(':run_id', $run_id, PDO::PARAM_INT);
    }

    /**
     * Persist a new name.
     * @param API_Name $name
     * @return int The generated ID.
     */
    public function create(API_Name $name, string $profileID, string $locale): int
    {
        $v = $name->makeParamArray();

        $this->executeWithBinds($this->queryInsert, [
            'profile_id'   => $profileID,
            'locale'       => $locale,
            'display_name' => $v ['display_name'] ?? null,
            'first_name'   => $v ['first_name'] ?? null,
            'last_name'    => $v ['last_name'] ?? null,
            'maiden_name'  => $v ['maiden_name'] ?? null,
            'middle_name'  => $v ['middle_name'] ?? null,
            'suffix'       => $v ['suffix'] ?? null,
            'nicknames'    => $v ['nicknames'] ?? null,
            'title'        => $v ['title'] ?? null
        ]);

        return (int) $this->getPDO()->lastInsertId();
    }

    //------------------------------------------------------

    /**
     * @param array <string, API_Name> $names
     * @param string $profileID
     */
    public function createAll(array $names, string $profileID): void
    {
        $this->deleteAll($profileID);

        foreach ($names as $locale => $nameObj)
        {
            $this->create($nameObj, $profileID, $locale);
        }
    }

    public function deleteAll(string $profileID): void
    {
        $this->deleteById($profileID);
    }

    /**
     * @param string $profileID
     * @return array <string, API_Name>
     */
    public function readAll(string $profileID): array
    {
        $nameRawArray = $this->readAllBy('profile_id', $profileID, 'locale');
        $nameArray    = [];

        foreach ($nameRawArray as $nameObjArray)
        {
            $locale = $nameObjArray ['locale'];
            $name   = API_Name::newFromArray($nameObjArray);

            $nameArray [$locale] = $name;
        }

        return $nameArray;
    }
}
