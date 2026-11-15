<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vbq23\TopLayerBundle\Twig\TopLayerExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->private();

    $services->set('top_layer.twig_extension', TopLayerExtension::class)
        ->tag('twig.extension');
};
