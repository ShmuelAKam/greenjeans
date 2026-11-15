<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

use ShmuelAKam\Greenjeans\Api\API_MediaBase;

class ShredderPhotos extends ShredderBase
{
    public function __construct()
    {
        parent::__construct('photo');
    }

    protected function shredObject(string $objectID): Shred_Result
    {
        $shredded = Shred_Result::FAILED;

        $deleted = API_MediaBase::deleteID($objectID);

        if ($deleted)
        {
            $shredded = Shred_Result::SHREDDED;
        }

        return $shredded;
    }
}
