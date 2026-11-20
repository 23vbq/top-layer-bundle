<?php

namespace Vbq23\TopLayerBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Symfony\UX\Turbo\TurboBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Vbq23\TopLayerBundle\Tests\Fixtures\Controller\FlashController;
use Vbq23\TopLayerBundle\TopLayerBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * Environment with non-default toast configuration.
     */
    public const CUSTOM_TOASTS_ENV = 'custom_toasts';

    /**
     * Environment with toasts not injected into Turbo responses.
     */
    public const NO_TURBO_INJECTION_ENV = 'no_turbo_injection';

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new StimulusBundle();
        yield new TurboBundle();
        yield new TwigComponentBundle();
        yield new TopLayerBundle();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/top-layer-bundle/cache/'.self::VERSION.'/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/top-layer-bundle/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            // FrameworkBundle 6.4.0 doesn't enable it on its own, but AssetMapper needs it
            'http_client' => true,
            'asset_mapper' => ['paths' => []],
            'session' => ['storage_factory_id' => 'session.storage.factory.mock_file'],
        ]);
        $container->extension('twig_component', [
            'anonymous_template_directory' => 'components/',
            'defaults' => [],
        ]);
        $container->extension('top_layer', match ($this->environment) {
            self::CUSTOM_TOASTS_ENV => ['toasts' => ['container_id' => 'notifications', 'delays' => ['error' => 8000]]],
            self::NO_TURBO_INJECTION_ENV => ['toasts' => ['inject_into_turbo_responses' => false]],
            default => [],
        });

        $container->services()
            ->set(FlashController::class)
            ->args([service('twig')])
            ->public();
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('frame', '/frame')->controller([FlashController::class, 'frame']);
        $routes->add('stream', '/stream')->controller([FlashController::class, 'stream']);
        $routes->add('page', '/page')->controller([FlashController::class, 'page']);
        $routes->add('redirect', '/redirect')->controller([FlashController::class, 'redirect']);
        $routes->add('frame_with_flashes', '/frame-with-flashes')->controller([FlashController::class, 'frameWithFlashes']);
        $routes->add('page_in_layout', '/page-in-layout')->controller([FlashController::class, 'pageInLayout']);
        $routes->add('frame_without_toast', '/frame-without-toast')->controller([FlashController::class, 'frameWithoutToast']);
    }
}
