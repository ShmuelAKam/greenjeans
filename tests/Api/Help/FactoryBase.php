<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

abstract class FactoryBase
{
    private int          $totalCreated;

    /**
     * @var array <string>
     */
    private array        $createdIDs;
    private ShredderBase $shredder;
    private static string  $runType;

    protected function __construct(string $shredderClass)
    {
        $this->shredder     = new $shredderClass();
        $this->totalCreated = 0;
        $this->createdIDs   = [];
    }

    protected function __destruct() {}

    /**
     * Shred all objects created of the relevant class.
     * This function must be called directly.
     */
    public function shredAll(bool $silent): void
    {
        $this->shredder->shredAll($silent);
    }

    public static function setRunType(string $runType): void
    {
        self::$runType = $runType;
    }

    //======================================================

    /**
     * When running in 'Mocking' mode, we do NOT want to log the "creation"
     * of test objects because they do not exist on the server.
     * @return bool True when NOT Mocking.
     */
    private static function isShredding(): bool
    {
        return (self::$runType != 'Mocking');
    }

    public function logCreate(string  $createID): void
    {
        $this->createdIDs [] = $createID;
        $this->totalCreated++;

        if ($this->isShredding() && $createID != null)
        {
            $this->shredder->logCreate($createID);
        }
    }

    public function logDelete(string  $deleteID): void
    {
        //TODO maybe we need to unset it in createdIDs and log it
        // in separate list?

        if ($this->isShredding())
        {
            $this->shredder->logDelete($deleteID);
        }
    }

    /**
     * @param array <\ShmuelAKam\Greenjeans\Api\API_Profile|\ShmuelAKam\Greenjeans\Api\API_Document> $objects
     * @param bool  $getID
     * @return array <string>
     */
    public function makeArrayIDs(array $objects, bool $getID = true): array
    {
        $IDs = [];

        if ($getID)
        {
            foreach ($objects as $prof)
            {
                $IDs[] = $prof->getID();
            }
        }
        else
        {
            foreach ($objects as $prof)
            {
                $IDs[] = $prof->getGUID();
            }
        }

        return $IDs;
    }

    //======================================================

    public function getTotalCreated(): int
    {
        return $this->totalCreated;
    }

    /**
     * Return the ID of one of the objects created by the factory.
     * @return string Document ID.
     */
    public function getRandomID(): string
    {
        $key = array_rand($this->createdIDs);

        return $this->createdIDs [$key];
    }

    //------------------------------------------------------
    // Generic helper functions, e.g. to created IDs.

    public function fullFileName(string $fileName): string
    {
        return $GLOBALS ['TEST_FILES_ROOT'] . $fileName;
    }

    /**
     * Generates a stable, numeric 8-digit ID based on a string seed.
     * Use this for internal database-style IDs.
     */
    public static function generateNumericId(string $seed): string
    {
        // crc32 provides a consistent 32-bit integer from a string.
        // Modulo 100,000,000 limits the range to 0 - 99,999,999.
        $number = abs(crc32($seed)) % 100000000;

        // Pad with leading zeros to guarantee an exact 8-digit string
        return sprintf('%08d', $number);
    }

    /**
     * Generates a prefixed ID (e.g., 'document-00048472')
     * This mirrors the "Subject" format Geni uses.
     */
    public static function generatePrefixedId(string $type, ?string $seed = null): string
    {
        $idPart = $seed
        ? self::generateNumericId($seed)
        : (string) random_int(10000000, 99999999); // Cryptographically secure 8-digit fallback

        return "{$type}-{$idPart}";
    }

    /**
     * Generates a 19-digit numeric GUID seeded by the object ID.
     * @param string $id The ID to seed from (e.g., 'profile-123')
     */
    public static function generateGuidFromId(string $id): string
    {
        // Hashing the ID gives us a stable hex string
        $hash = substr(md5($id), 0, 15);

        // Converting hex to decimal gives us that massive 19-digit Geni-style number
        // We cast to string immediately to prevent PHP from mangling it as a float
        return (string) hexdec($hash);
    }

    public static function generateName(string $id): string
    {
        return "$id - " . date('Y-m-d H:i');
    }

    /**
     * Create a list of objects. These DO NOT need to be real projects
     * because there is no interaction with the Geni server.
     * @param  string $prefix
     * @param  int    $count
     * @return array <string>
     */
    public static function makeIDList(string $prefix, int $count): array
    {
        $list = [];

        for ($i = 0; $i < $count; $i++)
        {
            $list[] = FactoryBase::generatePrefixedId($prefix);
        }

        return $list;
    }

    /**
     * Generates a mock Geni media URL.
     *
     * @param string $fileId    e.g. '53444869b92de4b7'
     * @param string $fileName  e.g. 'test-doc-3.png'
     * @param string $size      e.g. 'large', 'medium', 'original'
     * @return string
     */
    protected function makeMediaURL(string $fileId, string $fileName, string $size): string
    {
        // Example:
        // https://media.geni.com/p14/72/01/34/c1/53444869b92de4b7/test-doc-3_large.png?hash=2dad52be25e82d1e20408dcc96ac74988f0119e773f213335205fe1af282f02b.1789455599

        // 1. Derive the 16-character internal Blob Storage Key from the ID/GUID
        $storageKey = substr(hash('sha256', 'geni_blob_' . $fileId), 0, 16);

        // 2. Separate base name and extension
        $pathInfo = pathinfo($fileName);

        $baseName  = $pathInfo ['filename'];
        $extension = $pathInfo ['extension'];

        $formattedFileName = sprintf('%s_%s.%s', $baseName, $size, $extension);

        // 3. Generate 2-character directory buckets from the storage key
        $hashBucket = md5($storageKey);
        $dir1       = substr($hashBucket, 0, 2);
        $dir2       = substr($hashBucket, 2, 2);
        $dir3       = substr($hashBucket, 4, 2);
        $dir4       = substr($hashBucket, 6, 2);

        // 4. Build file path: /p14/{dir1}/{dir2}/{dir3}/{dir4}/{storageKey}/{fileName_size.ext}
        $filePath = sprintf('/p14/%s/%s/%s/%s/%s/%s', $dir1, $dir2, $dir3, $dir4, $storageKey, $formattedFileName);

        // 5. Calculate expiration & HMAC signature
        $expiresAt = time() + 3600;
        $payload   = $filePath . '.' . $expiresAt;
        $signature = hash_hmac('sha256', $payload, 'test_mock_secret');

        return sprintf('https://media.geni.com%s?hash=%s.%d', $filePath, $signature, $expiresAt);
    }
}
