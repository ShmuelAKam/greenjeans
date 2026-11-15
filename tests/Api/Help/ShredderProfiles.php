<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_Profile;

class ShredderProfiles extends ShredderBase
{
    public function __construct()
    {
        parent::__construct('profile');
    }

    protected function shredObject(string $objectID): Shred_Result
    {
        $shredded = Shred_Result::FAILED;

        $profileToDelete = API_Profile::getBrief($objectID);

        if ($profileToDelete != null)
        {
            $alreadyDeleted = $profileToDelete->isDeleted();

            if (!$alreadyDeleted)
            {
                $profileDeleted = $profileToDelete->delete();

                if ($profileDeleted)
                {
                    $shredded = Shred_Result::SHREDDED;
                }
            }
            else
            {
                $shredded = Shred_Result::ALREADY_DONE;
            }
        }

        return $shredded;
    }
}
