<?php

// Инициализация Finder (поиск файлов)
$finder = (new PhpCsFixer\Finder())
    ->exclude('vendor')
    ->exclude('tests')
    ->in(__DIR__)
;

// Инициализация Config
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => false,
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'single_quote' => true
    ])
    ->setLineEnding(PHP_EOL)
    ->setFinder($finder)
    ;
