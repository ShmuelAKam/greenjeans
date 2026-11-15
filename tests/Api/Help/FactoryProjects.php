<?php

namespace ShmuelAKam\Greenjeans\Tests\Api\Help;

/**
 * The project API does NOT support creating projects, so this isn't really a 'factory'.
 * It's just a place to put the constants used with regard to project tests, and
 * mocking.
 */
class FactoryProjects extends FactoryBase
{
    //======================================================
    // Class constans for various tests and data providers.

    public const TEST_PROJECT = [
        'UID'          => 'project-4513134',
        'Name'         => "Shmuel's Test-API Project",
        'Description'  => "This is a temporary project, for running tests on. Please IGNORE.\r\n\r\n"
                          . "Please do NOT follow these projects. It messes up my test code. :-p "
                          . "AND there's nothing to see.",
        'Created_at'   => '1768858738',
        'URL'          => "https://www.geni.com/api/project-4513134",
    ];

    public const PROJECT_IDS = [
        'Bible_Tree'    => 'project-38',
        'Test_Project'  => 'project-4513134',
        'Empty_Project' => 'project-4513135',
        'NON_Exist'     => 'project-10000001',
        'TEST_Biblical' => 'project-4506705',
    ];

    //======================================================

    public function __construct() {}
}
