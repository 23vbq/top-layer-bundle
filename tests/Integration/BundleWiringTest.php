<?php

namespace Vbq23\TopLayerBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\AssetMapper\AssetMapperRepository;
use Twig\Loader\FilesystemLoader;
use Vbq23\TopLayerBundle\Tests\Fixtures\TestKernel;
use Vbq23\TopLayerBundle\TopLayerBundle;

final class BundleWiringTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testBundleIsRegistered(): void
    {
        self::bootKernel();

        self::assertInstanceOf(TopLayerBundle::class, self::$kernel?->getBundle('TopLayerBundle'));
    }

    public function testTemplatesNamespaceIsRegistered(): void
    {
        $loader = self::getContainer()->get('twig.loader.native_filesystem');
        self::assertInstanceOf(FilesystemLoader::class, $loader);

        self::assertSame(
            [realpath(__DIR__.'/../../templates')],
            array_map('realpath', $loader->getPaths('TopLayer')),
        );
    }

    public function testAssetsAreExposedToAssetMapper(): void
    {
        $repository = self::getContainer()->get('asset_mapper.repository');
        self::assertInstanceOf(AssetMapperRepository::class, $repository);

        self::assertContains('@23vbq/top-layer-bundle', $repository->allDirectories());
    }
}
