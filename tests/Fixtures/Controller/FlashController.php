<?php

namespace Vbq23\TopLayerBundle\Tests\Fixtures\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;

/**
 * Responses for the functional tests of TurboFlashListener, each one adding a toast unless noted.
 */
final class FlashController
{
    public const FRAME = '<html><body><h1>Page</h1><turbo-frame id="demo"><p>Frame content</p></turbo-frame></body></html>';
    public const STREAM = '<turbo-stream action="remove" target="row-1"></turbo-stream>';
    public const PAGE = '<html><body><h1>Page</h1></body></html>';

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * The page the frame is extracted from, as rendered for a Turbo-Frame request.
     */
    public function frame(Request $request): Response
    {
        $this->addToast($request);

        return new Response(self::FRAME);
    }

    /**
     * Content-Type comes from the request format, as with a *.stream.html.twig template.
     */
    public function stream(Request $request): Response
    {
        $this->addToast($request);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return new Response(self::STREAM);
    }

    public function page(Request $request): Response
    {
        $this->addToast($request);

        return new Response(self::PAGE);
    }

    public function redirect(Request $request): Response
    {
        $this->addToast($request);

        return new RedirectResponse('/page');
    }

    /**
     * A template that still includes the flashes inside the frame itself.
     */
    public function frameWithFlashes(Request $request): Response
    {
        $this->addToast($request);

        return new Response($this->twig->createTemplate(
            "<turbo-frame id=\"demo\">{{ include('@TopLayer/_flashes.html.twig') }}<p>Frame content</p></turbo-frame>",
        )->render());
    }

    /**
     * A full page rendered in a layout with the toasts container, as a frame request usually gets it.
     */
    public function pageInLayout(Request $request): Response
    {
        $this->addToast($request);

        return new Response($this->twig->render('page_with_frame.html.twig'));
    }

    /**
     * Responds with the markup given in the "markup" query parameter, for checking how the frame is found.
     */
    public function markup(Request $request): Response
    {
        $this->addToast($request);

        return new Response($request->query->getString('markup'));
    }

    /**
     * Adds no toast, for checking that a toast is shown once.
     */
    public function frameWithoutToast(): Response
    {
        return new Response(self::FRAME);
    }

    private function addToast(Request $request): void
    {
        $session = $request->getSession();
        \assert($session instanceof FlashBagAwareSessionInterface);

        $session->getFlashBag()->add('success', 'Saved');
    }
}
