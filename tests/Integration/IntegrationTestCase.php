<?php

namespace Vbq23\TopLayerBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Vbq23\TopLayerBundle\Tests\Fixtures\TestKernel;

abstract class IntegrationTestCase extends KernelTestCase
{
    private mixed $previousExceptionHandler = null;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousExceptionHandler = self::currentExceptionHandler();
    }

    /**
     * symfony/error-handler before 6.4.44 / 7.4.17 / 8.1.5 leaks an exception handler
     * when the kernel boots, which PHPUnit reports as a risky test.
     *
     * @see https://github.com/symfony/error-handler/commit/8fa59eb915a14b2881993565a122c9dd1b0f8a19
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        while (null !== ($handler = self::currentExceptionHandler()) && $handler !== $this->previousExceptionHandler) {
            restore_exception_handler();
        }
    }

    private static function currentExceptionHandler(): ?callable
    {
        $handler = set_exception_handler(null);
        restore_exception_handler();

        return $handler;
    }
}
