<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (
    ContainerConfigurator $containerConfigurator
): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(Option::FILE_EXTENSIONS, ['php', 'yml']);
    $parameters->set(Option::CACHE_DIRECTORY, '.ecs_cache');

    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMFONY);
    $containerConfigurator->import(SetList::CLEAN_CODE);

    $services = $containerConfigurator->services();

    $services->set(ArraySyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]]);
};
