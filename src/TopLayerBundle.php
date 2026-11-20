<?php

namespace Vbq23\TopLayerBundle;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\KernelEvents;
use Vbq23\TopLayerBundle\EventListener\TurboFlashListener;
use Vbq23\TopLayerBundle\Toast\ToastType;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class TopLayerBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('toasts')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('container_id')
                            ->info('Id of the element the toasts are appended to.')
                            ->defaultValue('toasts')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('delays')
                            ->info('Milliseconds before a toast closes itself, 0 keeps it open until dismissed.')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode(ToastType::Success->value)->min(0)->defaultValue(6000)->end()
                                ->integerNode(ToastType::Info->value)->min(0)->defaultValue(6000)->end()
                                ->integerNode(ToastType::Error->value)->min(0)->defaultValue(0)->end()
                                ->integerNode(ToastType::Warning->value)->min(0)->defaultValue(0)->end()
                            ->end()
                        ->end()
                        ->booleanNode('inject_into_turbo_responses')
                            ->info('Render pending flashes into Turbo Stream and Turbo Frame responses, which don\'t render the layout.')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        /** @var array{container_id: string, delays: array<string, int>, inject_into_turbo_responses: bool} $toasts */
        $toasts = $config['toasts'];

        $services = $container->services();

        $services->get('top_layer.twig_extension')
            ->args([$toasts['container_id'], $toasts['delays']]);

        if ($toasts['inject_into_turbo_responses']) {
            // After ResponseListener (0), which sets the Turbo Stream Content-Type,
            // and before SessionListener (-1000), which saves the consumed flashes.
            $services->set('top_layer.turbo_flash_listener', TurboFlashListener::class)
                ->private()
                ->args([service('twig')])
                ->tag('kernel.event_listener', ['event' => KernelEvents::RESPONSE, 'priority' => -10]);
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!$this->isAssetMapperAvailable($builder)) {
            return;
        }

        $builder->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    $this->getPath().'/assets/src' => '@23vbq/top-layer-bundle',
                ],
            ],
        ]);
    }

    /**
     * Same check as symfony/stimulus-bundle and symfony/ux-turbo.
     */
    private function isAssetMapperAvailable(ContainerBuilder $builder): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        $bundlesMetadata = $builder->getParameter('kernel.bundles_metadata');
        if (!\is_array($bundlesMetadata) || !\is_array($bundlesMetadata['FrameworkBundle'] ?? null)) {
            return false;
        }

        $frameworkBundlePath = $bundlesMetadata['FrameworkBundle']['path'] ?? null;

        return \is_string($frameworkBundlePath) && is_file($frameworkBundlePath.'/Resources/config/asset_mapper.php');
    }
}
