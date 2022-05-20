<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Choosit\ModeloBundle\Tests\Integration\Stubs\SimpleWiredStub;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->defaults()
        ->public()
        ->autoconfigure()
        ->autowire()
    ;

    $services->set(SimpleWiredStub::class);
};
