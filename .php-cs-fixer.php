<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(false)
    ->setRules([
        // Baseline standard
        '@PER-CS' => true,

        // Enforce exactly one blank line after each method:
        'class_attributes_separation' => [
            'elements' => [
                'method'   => 'one',
                'property' => 'only_if_meta',
            ],
        ],
        // 1. ALLMAN BRACKETS
        'control_structure_braces' => true,
        'control_structure_continuation_position' => [
            'position' => 'next_line', // puts 'else', 'catch', etc. on a fresh line
        ],
        'braces_position' => [
            'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'functions_opening_brace'          => 'next_line_unless_newline_at_signature_end',
            'classes_opening_brace'            => 'next_line_unless_newline_at_signature_end',
            'anonymous_classes_opening_brace'  => 'next_line_unless_newline_at_signature_end',
            'anonymous_functions_opening_brace' => 'same_line',
        ],

        // 2. ARRAY PRESERVATION (Protects Data Providers & Matrix Constants)
        'array_syntax'                     => ['syntax' => 'short'],
        'array_indentation'                => false, // Leaves existing indentation intact
        'trim_array_spaces'                => false, // Preserves interior array padding
        'whitespace_after_comma_in_array'  => false, // Preserves column-aligned array rows
        'no_multiline_whitespace_around_double_arrow' => false,
        'trailing_comma_in_multiline'      => [
            'elements' => ['arguments', 'parameters'], // Excludes 'arrays' from comma mutation
        ],
        'single_line_empty_body'           => true,
        'normalize_index_brace'            => true,

        // 3. OPERATOR ALIGNMENT
        // Aligns assignment '=' while leaving '=>' unforced so data providers don't re-wrap
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '='  => 'align_single_space_minimal',
                '=>' => null, // null preserves your existing => alignment without restructuring
            ],
        ],

        // 4. CLEAN-UP HYGIENE
        'no_unused_imports' => true,
        'ordered_imports'   => [
            'sort_algorithm' => 'alpha',
            'imports_order'  => ['class', 'function', 'const'],
        ],
    ])
    ->setFinder($finder);
