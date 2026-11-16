<?php

namespace Vbq23\TopLayerBundle\Tests\Toast;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Vbq23\TopLayerBundle\Toast\ToastType;

final class ToastTypeTest extends TestCase
{
    #[DataProvider('roles')]
    public function testRole(ToastType $type, string $role): void
    {
        self::assertSame($role, $type->role());
    }

    /**
     * @return iterable<string, array{ToastType, string}>
     */
    public static function roles(): iterable
    {
        yield 'success' => [ToastType::Success, 'status'];
        yield 'info' => [ToastType::Info, 'status'];
        yield 'error' => [ToastType::Error, 'alert'];
        yield 'warning' => [ToastType::Warning, 'alert'];
    }
}
