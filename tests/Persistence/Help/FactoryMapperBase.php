<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

class FactoryMapperBase
{
    /**
     * Lookup table to convert base API classes to the Mapper factories that
     * create them. These factories are specific to the persistence testing.
     * As such they do NOT call the Geni Server.
     * @var array <string, string>
     */
    private const FACTORY_LOOKUP = [
        'API_Comment'     => 'FactoryMapperComments',
        'API_Date'        => 'FactoryMapperDates',
        'API_Document'    => 'FactoryMapperDocuments',
        'API_Event'       => 'FactoryMapperEvents',
        'API_Location'    => 'FactoryMapperLocations',
        'API_Name'        => 'FactoryMapperNames',
        'API_PhoneNumber' => 'FactoryMapperPhoneNumbers',
        'API_Photo'       => 'FactoryMapperPhotos',
        'API_PhotoAlbum'  => 'FactoryMapperPhotoAlbums',
        'API_Profile'     => 'FactoryMapperProfiles',
        'API_Project'     => 'FactoryMapperProjects',
        'API_Relation'    => 'FactoryMapperRelations',
        'API_Revision'    => 'FactoryMapperRevisions',
        'API_Surname'     => 'FactoryMapperSurnames',
        'API_Union'       => 'FactoryMapperUnions',
        'API_User'        => 'FactoryMapperUsers',
        'API_Video'       => 'FactoryMapperVideos',

        // There is no API_Tag class, as they are simple arrays. But they have a factory.
        'Tag Mapper'      => 'FactoryMapperTags',

        // The three size map classes, all use the same simple mapper.
        'API_DocumentImageSizeMap' => 'FactoryMapperSizeMaps',
        'API_PhotoImageSizeMap'    => 'FactoryMapperSizeMaps',
        'API_VideoImageSizeMap'    => 'FactoryMapperSizeMaps',
    ];

    /**
     * Cache of created factories.
     * @var array <string, mixed>
     */
    private static array $factories = [];

    public static function getFactory(string $objectClass): mixed
    {
        // Isolate classname from FQCN.
        // One special case 'Tag Mapper', does NOT contain a package name.

        $className = basename(str_replace('\\', '/', $objectClass));

        if (!isset(self::$factories [$className]))
        {
            $factoryClass = self::FACTORY_LOOKUP [$className];
            $factoryFQCN  = 'ShmuelAKam\\Greenjeans\\Tests\\Persistence\\Help\\'
                . $factoryClass;

            self::$factories [$className] = new $factoryFQCN();
        }

        return self::$factories [$className];
    }

    public function __construct() {}
}
