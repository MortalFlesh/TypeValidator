<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::SKIP,
        [
            NoSuperfluousPhpdocTagsFixer::class => ['tests/TypeValidatorTest.php'],
        ]
    );

    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs.php');
};
