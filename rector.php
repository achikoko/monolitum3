<?php

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/packages',
    ])    ->withPreparedSets(deadCode: true, codeQuality: true, privatization: true, naming: true);
//    ->withRules([
//        AddTypeToConstRector::class,
//    ]);
//return RectorConfig::configure()
//    ->withPaths([
//        __DIR__ . '/packages',
//    ])
//    ->withPhpSets();
    // register single rule
//    ->withRules([
//        TypedPropertyFromStrictConstructorRector::class,
//        AddTypeToConstRector::class
//    ])
    // here we can define, what prepared sets of rules will be applied
//    ->withPreparedSets(
//        deadCode: true,
//        codeQuality: true,
//        typeDeclarations: true
//    );
//    ->withDowngradeSets(php71: true);

//return static function (RectorConfig $rectorConfig): void {
//    $rectorConfig->sets([
//        DowngradeLevelSetList::DOWN_TO_PHP_72
//    ]);
//};
