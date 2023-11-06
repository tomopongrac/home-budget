<?php

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__.'/src', __DIR__.'/tests'])
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_summary' => false,
        'yoda_style' => true,
    ])
    ->setFinder($finder)
;
