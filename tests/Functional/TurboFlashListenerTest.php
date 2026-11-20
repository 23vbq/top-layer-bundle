<?php

namespace Vbq23\TopLayerBundle\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Vbq23\TopLayerBundle\Tests\Fixtures\Controller\FlashController;
use Vbq23\TopLayerBundle\Tests\Fixtures\TestKernel;
use Vbq23\TopLayerBundle\Tests\Integration\IntegrationTestCase;

final class TurboFlashListenerTest extends IntegrationTestCase
{
    private const LISTENER = 'top_layer.turbo_flash_listener';
    private const TOAST_STREAM = '<turbo-stream action="append" target="toasts">';
    private const FRAME_HEADER = ['HTTP_TURBO_FRAME' => 'demo'];

    public function testToastIsInsertedIntoRequestedFrame(): void
    {
        $content = $this->request($this->createClient(), '/frame', self::FRAME_HEADER);

        self::assertSame(1, substr_count($content, self::TOAST_STREAM));
        self::assertStringContainsString('<turbo-frame id="demo">'.self::TOAST_STREAM, $content);
        self::assertStringEndsWith('<p>Frame content</p></turbo-frame></body></html>', $content);
    }

    public function testToastIsAppendedToTurboStreamResponse(): void
    {
        $client = $this->createClient();
        $content = $this->request($client, '/stream');

        self::assertStringStartsWith('text/vnd.turbo-stream.html', (string) $client->getResponse()->headers->get('Content-Type'));
        self::assertStringStartsWith(FlashController::STREAM.self::TOAST_STREAM, $content);
        self::assertSame(1, substr_count($content, self::TOAST_STREAM));
    }

    public function testPlainHtmlResponseIsLeftUnchanged(): void
    {
        self::assertSame(FlashController::PAGE, $this->request($this->createClient(), '/page'));
    }

    public function testResponseWithoutRequestedFrameIsLeftUnchanged(): void
    {
        self::assertSame(FlashController::FRAME, $this->request($this->createClient(), '/frame', ['HTTP_TURBO_FRAME' => 'other']));
    }

    public function testRedirectIsLeftUnchangedAndToastShowsAfterIt(): void
    {
        $client = $this->createClient();

        $content = $this->request($client, '/redirect', self::FRAME_HEADER);
        self::assertTrue($client->getResponse()->isRedirect('/page'));
        self::assertStringNotContainsString(self::TOAST_STREAM, $content);

        self::assertSame(1, substr_count($this->request($client, '/frame-without-toast', self::FRAME_HEADER), self::TOAST_STREAM));
    }

    public function testToastIsShownOnce(): void
    {
        $client = $this->createClient();
        $this->request($client, '/frame', self::FRAME_HEADER);

        self::assertSame(FlashController::FRAME, $this->request($client, '/frame-without-toast', self::FRAME_HEADER));
    }

    public function testTemplateIncludingFlashesIsNotDuplicated(): void
    {
        $content = $this->request($this->createClient(), '/frame-with-flashes', self::FRAME_HEADER);

        self::assertSame(1, substr_count($content, self::TOAST_STREAM));
    }

    public function testFullPageVisitGetsToastFromContainer(): void
    {
        $content = $this->request($this->createClient(), '/page-in-layout');

        self::assertSame(1, substr_count($content, self::TOAST_STREAM));
        self::assertMatchesRegularExpression('~</turbo-frame><div id="toasts"[^>]*></div>\s*'.preg_quote(self::TOAST_STREAM, '~').'~', $content);
    }

    public function testFrameRequestForFullPageGetsToastInsideFrame(): void
    {
        $content = $this->request($this->createClient(), '/page-in-layout', self::FRAME_HEADER);

        self::assertSame(1, substr_count($content, self::TOAST_STREAM));
        self::assertStringContainsString('<turbo-frame id="demo">'.self::TOAST_STREAM, $content);
    }

    public function testFrameRequestForFullPageGetsToastFromContainerWhenDisabled(): void
    {
        $content = $this->request($this->createClient(TestKernel::NO_TURBO_INJECTION_ENV), '/page-in-layout', self::FRAME_HEADER);

        self::assertSame(1, substr_count($content, self::TOAST_STREAM));
        self::assertStringContainsString('<turbo-frame id="demo"><p>Frame content</p></turbo-frame>', $content);
    }

    #[DataProvider('matchingFrames')]
    public function testFrameIsFoundByItsId(string $frame, string $frameId): void
    {
        $content = $this->request($this->createClient(), '/markup?'.http_build_query(['markup' => $frame.'</turbo-frame>']), ['HTTP_TURBO_FRAME' => $frameId]);

        self::assertStringStartsWith($frame.self::TOAST_STREAM, $content);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function matchingFrames(): iterable
    {
        yield 'single quotes' => ["<turbo-frame id='demo'>", 'demo'];
        yield 'whitespace around equals sign' => ['<turbo-frame id = "demo">', 'demo'];
        yield 'other attributes around id' => ['<turbo-frame class="list" id="demo" src="/list">', 'demo'];
        yield 'id escaped by Twig' => ['<turbo-frame id="a&amp;b&quot;c">', 'a&b"c'];
    }

    #[DataProvider('otherFrames')]
    public function testFrameWithOtherIdIsLeftUnchanged(string $markup): void
    {
        self::assertSame($markup, $this->request($this->createClient(), '/markup?'.http_build_query(['markup' => $markup]), self::FRAME_HEADER));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function otherFrames(): iterable
    {
        yield 'data attribute' => ['<turbo-frame data-id="demo"></turbo-frame>'];
        yield 'id with a prefix' => ['<turbo-frame id="demo-list"></turbo-frame>'];
        yield 'other element' => ['<div id="demo"></div>'];
    }

    public function testHeadRequestLeavesFlashesInSession(): void
    {
        $client = $this->createClient();

        $client->request('HEAD', '/stream');
        self::assertSame('', (string) $client->getResponse()->getContent());

        self::assertSame(1, substr_count($this->request($client, '/frame-without-toast', self::FRAME_HEADER), self::TOAST_STREAM));
    }

    public function testRequestWithoutSessionCookieDoesNotStartSession(): void
    {
        $client = $this->createClient();
        $this->request($client, '/frame-without-toast', self::FRAME_HEADER);

        self::assertSame([], $client->getResponse()->headers->getCookies());
    }

    public function testListenerIsRegisteredByDefault(): void
    {
        self::bootKernel();

        self::assertTrue(self::getContainer()->has(self::LISTENER));
    }

    public function testListenerIsNotRegisteredWhenDisabled(): void
    {
        self::bootKernel(['environment' => TestKernel::NO_TURBO_INJECTION_ENV]);

        self::assertFalse(self::getContainer()->has(self::LISTENER));
    }

    private function createClient(string $environment = 'test'): KernelBrowser
    {
        self::bootKernel(['environment' => $environment]);

        $client = self::getContainer()->get('test.client');
        self::assertInstanceOf(KernelBrowser::class, $client);

        return $client;
    }

    /**
     * @param array<string, string> $server
     */
    private function request(KernelBrowser $client, string $uri, array $server = []): string
    {
        $client->request('GET', $uri, server: $server);

        return (string) $client->getResponse()->getContent();
    }
}
