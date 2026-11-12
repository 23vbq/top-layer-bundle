<?php

namespace Vbq23\TopLayerBundle\Tests;

use PHPUnit\Framework\TestCase;
use Vbq23\TopLayerBundle\TopLayerBundle;

final class TopLayerBundleTest extends TestCase
{
    public function testPathPointsToProjectRoot(): void
    {
        self::assertSame(\dirname(__DIR__), (new TopLayerBundle())->getPath());
    }
}
