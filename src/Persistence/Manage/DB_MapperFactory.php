<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use Exception;
use PDO;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_UserMapper;

class DB_MapperFactory
{
    private PDO    $pdo;
    private int    $runID;
    private string $profileID;

    /**
     * Array of data-mapper objects. Key is class name of the mapper.
     * @var array <string, mixed>
     */
    private array $instances = [];

    public function __construct(PDO $pdo, int $runID, string $profileID)
    {
        $this->pdo       = $pdo;
        $this->runID     = $runID;
        $this->profileID = $profileID;
    }

    /**
     * Returns the correct mapper based on the provided object or class name.
     * @param DB_Persistor  $persistor Active persistor session.
     * @param string|object $target    Class-name or object to get mapper for.
     */
    public function getMapper(DB_Persistor $persistor, string|object $target): mixed
    {
        $mapperClass = $this->getMapperName($target);

        // Singleton-style caching of mapper instances
        if (!isset($this->instances[$mapperClass]))
        {
            $mapper = new $mapperClass($persistor, $this->pdo, $this->runID);

            if ($mapper instanceof DB_UserMapper)
            {
                $mapper->setProfileID($this->profileID);
            }

            $this->instances[$mapperClass] = $mapper;
        }

        return $this->instances[$mapperClass];
    }

    private function getMapperName(string|object $target): string
    {
        $className = ltrim(is_object($target) ? get_class($target) : $target, '\\');

        // 1. If a DB_*Mapper class name was already passed directly, validate and return it
        if (str_starts_with($className, 'ShmuelAKam\\Greenjeans\\Persistence\\Mappers\\'))
        {
            if (!class_exists($className))
            {
                throw new Exception("Mapper class not found at $className");
            }

            return $className;
        }

        // 2. Break the namespace into parts
        $parts = explode('\\', $className);

        // 3. Swap 'ShmuelAKam\Greenjeans\Api' -> 'ShmuelAKam\Greenjeans\Persistence\Mappers'
        if (
            count($parts) >= 3
            && $parts[0] === 'ShmuelAKam'
            && $parts[1] === 'Greenjeans'
            && $parts[2] === 'Api'
        ) {
            array_splice($parts, 2, 1, ['Persistence', 'Mappers']);
        }

        // 4. Isolate and transform the short class name ('API_Profile' -> 'DB_ProfileMapper')
        $shortName = array_pop($parts);
        $cleanName = str_starts_with($shortName, 'API_') ? substr($shortName, 4) : $shortName;

        // Special case: API_Relation lives in Api\Data, but DB_RelationMapper sits in root Mappers\
        if ($cleanName === 'Relation' && end($parts) === 'Data')
        {
            array_pop($parts);
        }

        $parts[]     = 'DB_' . $cleanName . 'Mapper';
        $mapperClass = implode('\\', $parts);

        if (!class_exists($mapperClass))
        {
            throw new Exception("Mapper for $className not found at $mapperClass");
        }

        return $mapperClass;
    }
}
