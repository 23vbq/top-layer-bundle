<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vbq23\TopLayerBundle\Toast\Toaster;
use Vbq23\TopLayerBundle\Twig\TopLayerExtension;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->private();

    $services->set('top_layer.twig_extension', TopLayerExtension::class)
        ->tag('twig.extension');

    $services->set('top_layer.toaster', Toaster::class)
        ->args([service('request_stack')]);

    $services->alias(Toaster::class, 'top_layer.toaster');
};
