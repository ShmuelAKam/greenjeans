<?php

namespace ShmuelAKam\Greenjeans\Tests\Persistence\Help;

use ShmuelAKam\Greenjeans\Api\Data\API_Comment;

/**
 * Factory for creating test API_Comment objects for DB_CommentMapper unit tests.
 */
class FactoryMapperComments extends FactoryMapperBase
{
    public const COMMENT_PRESETS = [
        'basic_comment' => [
            'comment'    => 'This is a standard historical note regarding birth record discrepancies.',
            'created_at' => '2023-01-15 10:30:00',
            'title'      => 'Birth Record Note',
        ],
        'multiline_comment' => [
            'comment'    => "Line 1: Found census record.\nLine 2: Verified parish ledger.\nLine 3: Confirmed lineage.",
            'created_at' => '2022-05-20 14:15:00',
            'title'      => 'Parish Census Analysis',
        ],
        'unicode_comment' => [
            'comment'    => 'Recherche généalogique: Acte de naissance vérifié à Paris (1848) 📜✨',
            'created_at' => '2024-03-10 18:45:00',
            'title'      => 'French Archives',
        ],
        'minimal_comment' => [
            'comment'    => 'Unverified claim.',
            'created_at' => '2021-11-02 08:00:00',
            'title'      => '',
        ],
    ];

    /**
     * Create an API_Comment instance from a preset key.
     */
    public function makePresetComment(string $presetKey): API_Comment
    {
        $preset = self::COMMENT_PRESETS[$presetKey] ?? self::COMMENT_PRESETS['basic_comment'];

        return API_Comment::newFromArray([
            'comment'    => $preset['comment'],
            'created_at' => strtotime($preset['created_at']),
            'title'      => $preset['title'],
        ]);
    }
}
