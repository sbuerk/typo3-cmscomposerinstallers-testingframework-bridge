<?php

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}

$header = <<<EOF
This file is part of the "sbuerk/typo3-cmscomposerinstallers-testingframework-bridge" composer plugin.

It is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License, either version 2
of the License, or any later version.

For the full copyright and license information, please read the
LICENSE.txt file that was distributed with this source code.
EOF;

return (new \PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PSR2' => true,
        'header_comment' => [
            'header' => $header
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'blank_line_after_opening_tag' => true,
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'binary_operator_spaces' => true,
        'cast_spaces' => [
            'space' => 'none',
        ],
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
        'compact_nullable_typehint' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'declare_equal_normalize' => [
            'space' => 'none',
        ],
        'dir_constant' => true,
        'function_to_constant' => ['functions' => ['get_called_class', 'get_class', 'get_class_this', 'php_sapi_name', 'phpversion', 'pi']],
        'function_typehint_space' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'modernize_types_casting' => true,
        'linebreak_after_opening_tag' => true,
        'lowercase_cast' => true,
        'native_function_casing' => true,
        'new_with_braces' => true,
        'no_alias_functions' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_blank_lines_before_namespace' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'continue',
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
            ],
        ],
        'no_null_property_initialization' => true,
        'no_superfluous_elseif' => true,
        'php_unit_construct' => ['assertions' => ['assertEquals', 'assertSame', 'assertNotEquals', 'assertNotSame']],
        'php_unit_mock_short_will_return' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'phpdoc_no_access' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_trim' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'single_trait_insert_per_statement' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'multiline_whitespace_before_semicolons' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unneeded_control_parentheses' => [
            'statements' => [
                'break',
                'clone',
                'continue',
                'echo_print',
                'return',
                'switch_case',
            ],
        ],
        'no_unreachable_default_argument_value' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'normalize_index_brace' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_no_package' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_types' => true,
        'self_accessor' => true,
        'short_scalar_cast' => true,
        'single_quote' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(dirname(dirname(__DIR__)) . '/')
            ->ignoreVCSIgnored(true)
            ->notPath('/^Build\/php-cs-fixer\/php-cs-fixer.php/')
    );
