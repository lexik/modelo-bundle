<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('choosit_modelo.http_client', HttpClientInterface::class)
        ->factory([HttpClient::class, 'create'])
        ->args([
            [], // default options
        ]);
};
