<?php


$header = <<<EOF
This file is part of the "php-ipfs" package.

(c) Robert Schönthal <robert.schoenthal@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('bin')
    ->in(getenv('PWD')); // this may not be correct in a non-docker environment

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'               => true,
        'header_comment'         => ['header' => $header],
        '@PSR2'                  => true,
        '@PSR1'                  => true,
        'array_syntax'           => ['syntax' => 'short'],
        'ordered_imports'        => true,
        'strict_comparison'      => true,
        'strict_param'          => true,
        'phpdoc_order'           => true,
        'no_useless_return'      => true,
        'ereg_to_preg'           => true,
        'concat_space'           => ['spacing' => 'one'],
        'binary_operator_spaces' => ['align_equals' => false, 'align_double_arrow' => true],
    ])
//->setUsingLinter(false)
    ->setFinder($finder)
    ->setUsingCache(false);
