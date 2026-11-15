<?php

namespace ShmuelAKam\Greenjeans\Persistence\Manage;

use Exception;
use PDO;

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
     * @param string|object $target  Class-name or object to get mapper for.
     */
    public function getMapper(DB_Persistor $persistor, string|object $target): mixed
    {
        $mapperClass = $this->getMapperName($target);

        // Singleton-style caching of mapper instances
        if (!isset($this->instances[$mapperClass]))
        {
            $mapper = new $mapperClass($persistor, $this->pdo, $this->runID);

            if ($target == 'ShmuelAKam\Greenjeans\Api\API_User')
            {
                $mapper->setProfileID($this->profileID);
            }

            $this->instances[$mapperClass] = $mapper;
        }

        return $this->instances [$mapperClass];
    }

    private function getMapperName(string|object $target): string
    {
        $className = is_object($target) ? get_class($target) : $target;

        // 1. Break the namespace into parts (ltrim prevents issues with leading slashes)
        $parts = explode('\\', ltrim($className, '\\'));

        // 2. Swap the root namespace
        if ($parts[0] === 'Geni_API')
        {
            $parts[0] = 'Geni_DB';
        }

        // 3. Isolate and clean the short class name (remove 'API_' prefix)
        $shortName = array_pop($parts);
        $cleanName = str_starts_with($shortName, 'API_') ? substr($shortName, 4) : $shortName;

        // 4. Put the clean name back and rebuild the full namespace string
        $parts[]     = $cleanName;
        $mapperClass = implode('\\', $parts);

        if (!class_exists($mapperClass))
        {
            throw new Exception("Mapper for $className not found at $mapperClass");
        }

        return $mapperClass;
    }
}
