<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
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
