<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

/**
 * As the name implies, this class is a dummy.
 * It is meant for Factory classes of objects that do NOT need shredding.
 * Typically because they are always part of larger objects. The other case
 * is FactoryPhotoAlbums, because there is NO API to delete and album.
 *
 * All of the functions in this class do NOTHING.
 */
class ShredderDummy extends ShredderBase
{
    public function __construct()
    {
        // No need to call parent constructor. After all this class does nothing.
    }

    protected function shredObject(string $objectID): Shred_Result
    {
        return Shred_Result::SHREDDED;
    }

    protected function logShred(string $filename, string $shredID): void {}

    public function shredAll(bool $silent): void {}
}
