<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\Core\API_Wrapper;

use function ShmuelAKam\Greenjeans\Tests\Api\serverSetup;

abstract class ShredderBase
{
    private static bool   $setup = false;
    private static string $SHRED_BASE_PATH;
    protected string $objectType;
    protected int    $totalCreated;
    protected int    $totalDeleted;
    protected string $LOG_FILE_NAME;
    protected string $LOG_FAIL_NAME;
    protected string $LOG_DELETE_NAME;

    protected function __construct(string $objectType)
    {
        if (!self::$setup)
        {
            self::setup();
        }

        $this->objectType   = $objectType;
        $this->totalCreated = 0;
        $this->totalDeleted = 0;

        $this->LOG_FILE_NAME   = self::$SHRED_BASE_PATH . "$objectType-files.log";
        $this->LOG_FAIL_NAME   = self::$SHRED_BASE_PATH . "$objectType-fail.log";
        $this->LOG_DELETE_NAME = self::$SHRED_BASE_PATH . "$objectType-delete.log";
    }

    public function __destruct() {}

    private static function setup(): void
    {
        self::$SHRED_BASE_PATH = $GLOBALS ['TEST_API_ROOT'] . '/log/';

        // Make sure directory for logging object creation exists. This
        // is required for clean-up ("Shredding") after tests are complete.

        if (!is_dir(self::$SHRED_BASE_PATH))
        {
            mkdir(self::$SHRED_BASE_PATH, 0775, true);
        }

        self::$setup = true;
    }

    // ======================================================
    public function logCreate(string $createID): void
    {
        $this->logShred($this->LOG_FILE_NAME, $createID);
        $this->totalCreated++;
    }

    public function logDelete(string $deleteID): void
    {
        $this->logShred($this->LOG_DELETE_NAME, $deleteID);
        $this->totalDeleted++;
    }

    protected function logShred(string $filename, string $shredID): void
    {
        file_put_contents($filename, "$shredID\r\n", FILE_APPEND);
    }

    protected function shredderSetup(bool $silent): void
    {
        $API_Wrap = API_Wrapper::getInstance();

        if ($API_Wrap == null)
        {
            $API_Wrap = serverSetup();
        }

        $API_Wrap->setSilent($silent);
    }

    abstract protected function shredObject(string $objectID): Shred_Result;

    public function shredAll(bool $silent): void
    {
        $API_Wrap = API_Wrapper::getInstance();
        $API_Wrap->setSilent($silent);

        $objectType = $this->objectType . 's';

        if ($this->totalCreated > 0
            || file_exists($this->LOG_FILE_NAME)
            || file_exists($this->LOG_FAIL_NAME))
        {
            echo "\r\n\r\n";
            echo "==================================================\r\n";
            echo "========  Shredder of " . str_pad($objectType, 10) . "Started   ========\r\n";
        }
        else
        {
            echo "========  Shredder of " . str_pad($objectType, 10) . "NONE created\r\n";

            return;
        }

        $this->shredderSetup($silent);

        // --------------------------------------------------

        $countDone     = 0;
        $countFailed   = 0;
        $countAlready  = 0;
        $countInFile   = 0;
        $countByDelete = 0;

        if (file_exists($this->LOG_FILE_NAME))
        {
            // First check if we have previous failures that were not processed.

            if (file_exists($this->LOG_FAIL_NAME))
            {
                // IF a file of failures exists (A previous Shredder call
                // crashed). Append it to list of documents to delete, and
                // delete the file.

                $failList = file_get_contents($this->LOG_FAIL_NAME);
                file_put_contents($this->LOG_FILE_NAME, $failList, FILE_APPEND);
                unlink($this->LOG_FAIL_NAME);
            }

            $logString = file_get_contents($this->LOG_FILE_NAME);
            $shredIDs  = explode("\r\n", $logString);
            //             $toShred   = count($shredIDs);

            if (file_exists($this->LOG_DELETE_NAME))
            {
                // The different class tests call delete() on some of the objects.
                // These IDs must be removed from the list of profiles to delete.

                $deletedString = file_get_contents($this->LOG_DELETE_NAME);
                $deletedIDs    = explode("\r\n", $deletedString);
                $countByDelete = count($deletedIDs);

                $shredIDs = array_diff($shredIDs, $deletedIDs);
            }

            // Remove duplicates and empty lines.
            $shredIDs = array_filter(array_unique($shredIDs));

            $column      = 0;
            $actualCount = count($shredIDs);
            $countDigits = strlen((string) $actualCount);

            foreach ($shredIDs as $deleteID)
            {
                $countInFile++;
                $shredResult = $this->shredObject($deleteID);

                switch ($shredResult)
                {
                    case Shred_Result::SHREDDED:
                        echo '.';
                        $countDone++;
                        break;

                    case Shred_Result::FAILED:
                        echo 'F';
                        $this->logShred($this->LOG_FAIL_NAME, $deleteID);
                        $countFailed++;
                        break;

                    case Shred_Result::ALREADY_DONE:
                        // Profile objects have a flag set if already deleted.
                        echo 'A';
                        $countAlready++;
                        break;
                }

                $column++;
                if ($column == 80)
                {
                    $percent  = (string) round($countInFile / $actualCount * 100);
                    $progress = " " . str_pad($percent, 3, ' ', STR_PAD_LEFT)
                                . " % (" . str_pad((string) $countInFile, $countDigits, ' ', STR_PAD_LEFT)
                                . " / " . $actualCount . ")\r\n";
                    echo $progress;
                    $column = 0;
                }
            }

            unlink($this->LOG_FILE_NAME);

            if ($countFailed > 0)
            {
                rename($this->LOG_FAIL_NAME, $this->LOG_FILE_NAME);
            }

            if (file_exists($this->LOG_DELETE_NAME))
            {
                unlink($this->LOG_DELETE_NAME);
            }
        }

        echo "\r\n";
        echo "=========  Completed.\r\n";
        echo "Total of " . $this->totalCreated . " $objectType created.\r\n";
        echo "\t" . str_pad((string) $countInFile, 4, ' ', STR_PAD_LEFT) . " listed in file.\r\n";

        echo "\t" . str_pad((string) $countDone, 4, ' ', STR_PAD_LEFT) . " deleted.\r\n";
        echo "\t" . str_pad((string) $countByDelete, 4, ' ', STR_PAD_LEFT) . " deleted by delete function.\r\n";

        echo "\t" . str_pad((string) $countFailed, 4, ' ', STR_PAD_LEFT) . " FAILED to delete.\r\n";
        echo "\t" . str_pad((string) $countAlready, 4, ' ', STR_PAD_LEFT) . " previously deleted.\r\n";
        echo "====================================================\r\n\r\n";
    }
}
