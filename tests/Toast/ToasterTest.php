<?php

namespace Vbq23\TopLayerBundle\Tests\Toast;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Vbq23\TopLayerBundle\Toast\Toast;
use Vbq23\TopLayerBundle\Toast\Toaster;
use Vbq23\TopLayerBundle\Toast\ToastType;

final class ToasterTest extends TestCase
{
    private Session $session;
    private Toaster $toaster;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());

        $request = new Request();
        $request->setSession($this->session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->toaster = new Toaster($requestStack);
    }

    #[DataProvider('shortcuts')]
    public function testShortcutStoresFlashUnderTypeKey(string $method, string $key): void
    {
        $this->toaster->{$method}('Saved', 'Employee was created.');

        self::assertSame(
            [$key => [['title' => 'Saved', 'message' => 'Employee was created.']]],
            $this->session->getFlashBag()->peekAll(),
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function shortcuts(): iterable
    {
        yield 'success' => ['success', 'success'];
        yield 'error' => ['error', 'error'];
        yield 'warning' => ['warning', 'warning'];
        yield 'info' => ['info', 'info'];
    }

    public function testMessageDefaultsToNull(): void
    {
        $this->toaster->success('Saved');

        self::assertSame([['title' => 'Saved', 'message' => null]], $this->session->getFlashBag()->peek('success'));
    }

    public function testAddKeepsOrderOfToasts(): void
    {
        $this->toaster->add(new Toast(ToastType::Error, 'First'));
        $this->toaster->add(new Toast(ToastType::Error, 'Second', 'Details'));

        self::assertSame(
            [
                ['title' => 'First', 'message' => null],
                ['title' => 'Second', 'message' => 'Details'],
            ],
            $this->session->getFlashBag()->peek('error'),
        );
    }
}
