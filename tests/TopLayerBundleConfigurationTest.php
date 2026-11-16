<?php

namespace Vbq23\TopLayerBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Vbq23\TopLayerBundle\TopLayerBundle;

final class TopLayerBundleConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = $this->process([]);

        self::assertSame('toasts', $config['toasts']['container_id']);
        self::assertSame(['error' => 0, 'info' => 6000, 'success' => 6000, 'warning' => 0], $config['toasts']['delays']);
    }

    public function testOverridingOneDelayKeepsOtherDefaults(): void
    {
        $config = $this->process(['toasts' => ['delays' => ['error' => 8000]]]);

        self::assertSame(['error' => 8000, 'info' => 6000, 'success' => 6000, 'warning' => 0], $config['toasts']['delays']);
    }

    public function testContainerIdCanBeChanged(): void
    {
        $config = $this->process(['toasts' => ['container_id' => 'notifications']]);

        self::assertSame('notifications', $config['toasts']['container_id']);
    }

    /**
     * @param array<string, mixed> $toasts
     */
    #[DataProvider('invalidToasts')]
    public function testInvalidConfigurationIsRejected(array $toasts): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process(['toasts' => $toasts]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidToasts(): iterable
    {
        yield 'unknown type in delays' => [['delays' => ['notice' => 1000]]];
        yield 'negative delay' => [['delays' => ['success' => -1]]];
        yield 'non-integer delay' => [['delays' => ['success' => '6s']]];
        yield 'empty container id' => [['container_id' => '']];
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array{toasts: array{container_id: string, delays: array<string, int>}}
     */
    private function process(array $config): array
    {
        $extension = (new TopLayerBundle())->getContainerExtension();
        self::assertInstanceOf(ConfigurationExtensionInterface::class, $extension);

        $configuration = $extension->getConfiguration([], new ContainerBuilder());
        self::assertNotNull($configuration);

        /** @var array{toasts: array{container_id: string, delays: array<string, int>}} $processed */
        $processed = (new Processor())->processConfiguration($configuration, [$config]);
        ksort($processed['toasts']['delays']);

        return $processed;
    }
}
