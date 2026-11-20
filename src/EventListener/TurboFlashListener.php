<?php

namespace Vbq23\TopLayerBundle\EventListener;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;

/**
 * Renders pending flashes as toast Turbo Streams into Turbo Stream and Turbo Frame responses,
 * which don't render the layout that includes _flashes.html.twig.
 */
final class TurboFlashListener
{
    private const HTML_CONTENT_TYPE = 'text/html';

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($response->isRedirection() || $response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return;
        }

        if (!$this->hasFlashes($request)) {
            return;
        }

        if ($this->isTurboStream($response)) {
            $this->append($response);

            return;
        }

        $frameId = $request->headers->get('Turbo-Frame');

        if (null !== $frameId && '' !== $frameId && $this->isHtml($response)) {
            $this->insertIntoFrame($response, $frameId);
        }
    }

    /**
     * A request without a session cookie never starts a session here. With a cookie, the session is
     * started to read flashes added before a redirect, as in a Turbo Frame form that redirects.
     */
    private function hasFlashes(Request $request): bool
    {
        if (!$request->hasSession()) {
            return false;
        }

        $session = $request->getSession();

        return ($session->isStarted() || $request->hasPreviousSession())
            && $session instanceof FlashBagAwareSessionInterface
            && [] !== $session->getFlashBag()->peekAll();
    }

    private function isTurboStream(Response $response): bool
    {
        return str_starts_with((string) $response->headers->get('Content-Type'), TurboBundle::STREAM_MEDIA_TYPE);
    }

    private function isHtml(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');

        return null === $contentType || str_starts_with($contentType, self::HTML_CONTENT_TYPE);
    }

    private function append(Response $response): void
    {
        $content = $response->getContent();
        if (false === $content) {
            return;
        }

        $streams = $this->renderFlashes();
        if ('' === $streams) {
            return;
        }

        $this->setContent($response, $content.$streams);
    }

    /**
     * Turbo keeps only the requested frame from the response, streams outside it would be dropped.
     */
    private function insertIntoFrame(Response $response, string $frameId): void
    {
        $content = $response->getContent();
        if (false === $content) {
            return;
        }

        $pattern = '/<turbo-frame\b[^>]*\sid=(["\'])'.preg_quote($frameId, '/').'\1[^>]*>/i';
        if (1 !== preg_match($pattern, $content, $matches, \PREG_OFFSET_CAPTURE)) {
            return;
        }

        $streams = $this->renderFlashes();
        if ('' === $streams) {
            return;
        }

        $offset = $matches[0][1] + \strlen($matches[0][0]);
        $this->setContent($response, substr_replace($content, $streams, $offset, 0));
    }

    /**
     * Consumes the flashes, so a template that still includes _flashes.html.twig leaves nothing to inject.
     * Flashes of other types stay in the session and render nothing.
     */
    private function renderFlashes(): string
    {
        return trim($this->twig->render('@TopLayer/_flashes.html.twig'));
    }

    private function setContent(Response $response, string $content): void
    {
        $response->setContent($content);
        $response->headers->remove('Content-Length');
    }
}
