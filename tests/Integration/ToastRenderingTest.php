<?php

namespace Vbq23\TopLayerBundle\Tests\Integration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Vbq23\TopLayerBundle\Tests\Fixtures\TestKernel;

final class ToastRenderingTest extends IntegrationTestCase
{
    private const PROGRESS = 'data-23vbq--top-layer-bundle--toast-target="progress"';

    public function testErrorToastHasNoProgressBar(): void
    {
        $html = $this->renderToast('error', ['title' => 'Failed', 'message' => null]);

        self::assertStringContainsString('class="top-layer-toast top-layer-toast--error"', $html);
        self::assertStringContainsString('data-23vbq--top-layer-bundle--toast-delay-value="0"', $html);
        self::assertStringNotContainsString(self::PROGRESS, $html);
    }

    public function testSuccessToastHasProgressBar(): void
    {
        $html = $this->renderToast('success', ['title' => 'Saved', 'message' => null]);

        self::assertStringContainsString('data-23vbq--top-layer-bundle--toast-delay-value="6000"', $html);
        self::assertStringContainsString(self::PROGRESS, $html);
    }

    public function testToastUsesBundleControllerAndIcon(): void
    {
        $html = $this->renderToast('warning', ['title' => 'Careful', 'message' => null]);

        self::assertStringContainsString('data-controller="23vbq--top-layer-bundle--toast"', $html);
        self::assertStringContainsString('data-action="23vbq--top-layer-bundle--toast#dismiss"', $html);
        self::assertStringContainsString('aria-label="Dismiss"', $html);
        self::assertStringContainsString('<path d="M8 1.8L15 13.5H1L8 1.8z" fill="currentColor"/>', $html);
    }

    public function testToastIsAnnouncedByContainerOnly(): void
    {
        $html = $this->renderToast('error', 'Failed');

        self::assertStringNotContainsString('role=', $html);
        self::assertStringNotContainsString('aria-live', $html);
    }

    public function testToastIsLeftOutOfTurboCache(): void
    {
        self::assertStringContainsString('data-turbo-temporary', $this->renderToast('success', 'Saved'));
    }

    public function testTimerPausesOnHoverAndFocus(): void
    {
        self::assertStringContainsString(
            'data-action="pointerenter->23vbq--top-layer-bundle--toast#pointerEnter pointerleave->23vbq--top-layer-bundle--toast#pointerLeave focusin->23vbq--top-layer-bundle--toast#focusIn focusout->23vbq--top-layer-bundle--toast#focusOut"',
            $this->renderToast('success', 'Saved'),
        );
    }

    public function testTitleAndMessageAreRendered(): void
    {
        $html = $this->renderToast('info', ['title' => 'Heads up', 'message' => 'Export <b>ready</b>']);

        self::assertStringContainsString('<div class="top-layer-toast__title">Heads up</div>', $html);
        self::assertStringContainsString('<div class="top-layer-toast__message">Export &lt;b&gt;ready&lt;/b&gt;</div>', $html);
    }

    public function testPlainStringFlashIsRenderedAsTitle(): void
    {
        $html = $this->renderToast('success', 'Saved');

        self::assertStringContainsString('<div class="top-layer-toast__title">Saved</div>', $html);
        self::assertStringNotContainsString('top-layer-toast__message', $html);
    }

    public function testUnknownTypeRendersNothing(): void
    {
        self::assertSame('', trim($this->renderToast('notice', 'Hello')));
    }

    public function testUnknownTypeIsRejectedByTwigFunctions(): void
    {
        try {
            $this->twig()->createTemplate("{{ top_layer_toast_delay('notice') }}")->render();
            self::fail('Expected an exception for an unknown toast type.');
        } catch (RuntimeError $e) {
            $previous = $e->getPrevious();
            self::assertInstanceOf(\InvalidArgumentException::class, $previous);
            self::assertStringContainsString('Unknown toast type "notice"', $previous->getMessage());
        }
    }

    public function testFlashesRenderOnlyToastTypesAndKeepOthersInSession(): void
    {
        $session = $this->startSession();
        $session->getFlashBag()->add('success', ['title' => 'Saved', 'message' => null]);
        $session->getFlashBag()->add('error', 'Failed');
        $session->getFlashBag()->add('notice', 'Hello');

        $html = $this->twig()->render('@TopLayer/_flashes.html.twig');

        self::assertSame(2, substr_count($html, '<turbo-stream action="append" target="toasts">'));
        self::assertStringContainsString('Saved', $html);
        self::assertStringContainsString('Failed', $html);
        self::assertStringNotContainsString('Hello', $html);
        self::assertSame(['notice' => ['Hello']], $session->getFlashBag()->peekAll());
    }

    public function testFlashesPassDismissLabelToToasts(): void
    {
        $this->startSession()->getFlashBag()->add('success', 'Saved');

        $html = $this->twig()->createTemplate("{{ include('@TopLayer/_flashes.html.twig', {dismissLabel: 'Zamknij'}) }}")->render();

        self::assertStringContainsString('aria-label="Zamknij"', $html);
    }

    public function testContainerHasDefaultId(): void
    {
        self::assertSame(
            '<div id="toasts" class="top-layer-toasts" aria-live="polite" aria-atomic="false"></div>',
            trim($this->twig()->render('@TopLayer/toasts_wrapper.html.twig')),
        );
    }

    public function testConfigurationIsPassedToTemplates(): void
    {
        self::bootKernel(['environment' => TestKernel::CUSTOM_TOASTS_ENV]);

        $session = $this->startSession();
        $session->getFlashBag()->add('error', 'Failed');

        self::assertStringContainsString('id="notifications"', $this->twig()->render('@TopLayer/toasts_wrapper.html.twig'));

        $html = $this->twig()->render('@TopLayer/_flashes.html.twig');
        self::assertStringContainsString('<turbo-stream action="append" target="notifications">', $html);
        self::assertStringContainsString('data-23vbq--top-layer-bundle--toast-delay-value="8000"', $html);
        self::assertStringContainsString(self::PROGRESS, $html);
    }

    /**
     * @param array{title: string, message: ?string}|string $flash
     */
    private function renderToast(string $type, array|string $flash): string
    {
        return $this->twig()->render('@TopLayer/toast.html.twig', ['type' => $type, 'flash' => $flash]);
    }

    private function startSession(): Session
    {
        $session = new Session(new MockArraySessionStorage());

        $request = new Request();
        $request->setSession($session);

        $requestStack = self::getContainer()->get('request_stack');
        self::assertInstanceOf(RequestStack::class, $requestStack);
        $requestStack->push($request);

        return $session;
    }

    private function twig(): Environment
    {
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        return $twig;
    }
}
