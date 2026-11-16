<?php

namespace Vbq23\TopLayerBundle\Toast;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

/**
 * Stores toasts as flash messages, rendered by _flashes.html.twig.
 */
final class Toaster
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function success(string $title, ?string $message = null): void
    {
        $this->add(new Toast(ToastType::Success, $title, $message));
    }

    public function error(string $title, ?string $message = null): void
    {
        $this->add(new Toast(ToastType::Error, $title, $message));
    }

    public function warning(string $title, ?string $message = null): void
    {
        $this->add(new Toast(ToastType::Warning, $title, $message));
    }

    public function info(string $title, ?string $message = null): void
    {
        $this->add(new Toast(ToastType::Info, $title, $message));
    }

    /**
     * Stored as a plain array, so no bundle classes end up serialized in the session.
     */
    public function add(Toast $toast): void
    {
        $session = $this->requestStack->getSession();

        if (!$session instanceof FlashBagAwareSessionInterface) {
            throw new \LogicException(\sprintf('The session must implement "%s" to store toasts.', FlashBagAwareSessionInterface::class));
        }

        $session->getFlashBag()->add($toast->type->value, [
            'title' => $toast->title,
            'message' => $toast->message,
        ]);
    }
}
