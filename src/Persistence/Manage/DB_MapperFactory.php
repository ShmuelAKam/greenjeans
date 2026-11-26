<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use Exception;
use PDO;
use ShmuelAKam\Greenjeans\Persistence\Mappers\DB_UserMapper;

/**
 * Factory and cache for mapper objects.
 * Mappers are created using the FQCN of either API classes, or the FQCN of the
 * mapper itself.
 *
 * This class is NOT accessed by the user, as it is wrapped by DB_Persistor,
 * which is the main interface to the Persistence layer.
 * @internal
 */
class DB_MapperFactory
{
    /**
     * @var \PDO This is the global PDO object used throughout the package.
     */
    private PDO    $pdo;

    /**
     * @var int run_ID defined at the beginning of the run. Is used throughout
     *          the persistence layer to define a "unit of work".
     */
    private int    $runID;

    /**
     * @var string profile-ID of the USER running the script. This is NOT the
     * user ID. This is a required value for the DB_UserMapper.
     */
    private string $profileID;

    /**
     * Array of data-mapper objects. Key is the class name of the mapper.
     * @var array <string, mixed>
     */
    private array  $instances = [];

    //--------------------------------------------------------------

    /**
     * Constructor.
     * @param PDO    $pdo       Global PDO used throughout the persistence layer.
     * @param int    $run_id    defined at the beginning of the run. Is used throughout
     *                          the persistence layer to define a "unit of work".
     * @param string $profileID Of the user running the script. Is needed by the
     *                          DB_UserMapper.
     */
    public function __construct(PDO $pdo, int $runID, string $profileID)
    {
        $this->pdo       = $pdo;
        $this->runID     = $runID;
        $this->profileID = $profileID;
    }

    /**
     * Returns the correct mapper based on the provided class name (or object).
     * @param string|object $target FQCN API Class-name or object to get mapper for.
     */
    public function getMapper(string|object $target): mixed
    {
        $mapperClass = $this->getMapperName($target);

        // Singleton-style caching of mapper instances
        if (!isset($this->instances[$mapperClass]))
        {
            $mapper = new $mapperClass($this->pdo, $this->runID);

            // Special case: DB_UserMapper also requires profileID.
            if ($mapper instanceof DB_UserMapper)
            {
                $mapper->setUserID($this->profileID);
            }

            $this->instances[$mapperClass] = $mapper;
        }

        return $this->instances[$mapperClass];
    }

    /**
     * Convert FQCN class name into FQCN DB_ mapper class name.
     *
     * @param string|object $target API_ or DB_ Class-name or object to get mapper for.
     * @return string
     */
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

        // Special case: API_Relation lives in Api\Data, but DB_RelationMapper
        // sits in root Mappers\
        // TODO why?

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
