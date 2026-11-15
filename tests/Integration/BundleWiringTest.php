<?php

namespace Vbq23\TopLayerBundle\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\AssetMapperRepository;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Vbq23\TopLayerBundle\TopLayerBundle;
use Vbq23\TopLayerBundle\Twig\TopLayerExtension;

final class BundleWiringTest extends IntegrationTestCase
{
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

    public function testTwigExtensionIsRegistered(): void
    {
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        self::assertTrue($twig->hasExtension(TopLayerExtension::class));
    }

    public function testAssetsAreExposedToAssetMapper(): void
    {
        $repository = self::getContainer()->get('asset_mapper.repository');
        self::assertInstanceOf(AssetMapperRepository::class, $repository);

        $directories = $repository->allDirectories();
        self::assertSame('@23vbq/top-layer-bundle', $directories[realpath(__DIR__.'/../../assets/src')] ?? null);
    }

    #[DataProvider('bundleAssets')]
    public function testBundleAssetIsResolvedByAssetMapper(string $logicalPath): void
    {
        $assetMapper = self::getContainer()->get('asset_mapper');
        self::assertInstanceOf(AssetMapperInterface::class, $assetMapper);

        self::assertNotNull($assetMapper->getAsset($logicalPath));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function bundleAssets(): iterable
    {
        yield 'dialog controller' => ['@23vbq/top-layer-bundle/dialog_controller.js'];
        yield 'turbo stream actions' => ['@23vbq/top-layer-bundle/turbo_stream_actions.js'];
        yield 'stylesheet' => ['@23vbq/top-layer-bundle/styles/top-layer.css'];
    }
}
