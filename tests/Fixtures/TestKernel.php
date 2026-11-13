<?php

namespace Vbq23\TopLayerBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Symfony\UX\Turbo\TurboBundle;
use Vbq23\TopLayerBundle\TopLayerBundle;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new StimulusBundle();
        yield new TurboBundle();
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
        ]);
        $container->extension('top_layer', []);
    }
}
