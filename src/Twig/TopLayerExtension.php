<?php

namespace Vbq23\TopLayerBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vbq23\TopLayerBundle\Toast\ToastType;
use Vbq23\TopLayerBundle\Turbo\TopLayerStream;

final class TopLayerExtension extends AbstractExtension
{
    /**
     * @param array<string, int> $toastDelays milliseconds per toast type, 0 disables auto-dismiss
     */
    public function __construct(
        private readonly string $toastsContainerId,
        private readonly array $toastDelays,
        private readonly bool $injectIntoTurboResponses,
    ) {
    }

    public function getFunctions(): array
    {
        $options = ['is_safe' => ['html']];

        return [
            new TwigFunction('top_layer_close_dialog', [TopLayerStream::class, 'closeDialog'], $options),
            new TwigFunction('top_layer_reload_frame', [TopLayerStream::class, 'reloadFrame'], $options),
            new TwigFunction('top_layer_refresh_frame', [TopLayerStream::class, 'refreshFrame'], $options),
            new TwigFunction('top_layer_visit_parent_frame', [TopLayerStream::class, 'visitParentFrame'], $options),
            new TwigFunction('top_layer_toasts_container_id', $this->toastsContainerId(...)),
            new TwigFunction('top_layer_toast_types', $this->toastTypes(...)),
            new TwigFunction('top_layer_toast_delay', $this->toastDelay(...)),
            new TwigFunction('top_layer_inject_into_turbo_responses', $this->injectIntoTurboResponses(...)),
        ];
    }

    public function toastsContainerId(): string
    {
        return $this->toastsContainerId;
    }

    /**
     * @return list<string>
     */
    public function toastTypes(): array
    {
        return array_column(ToastType::cases(), 'value');
    }

    public function toastDelay(string $type): int
    {
        return $this->toastDelays[$this->toastType($type)->value] ?? 0;
    }

    public function injectIntoTurboResponses(): bool
    {
        return $this->injectIntoTurboResponses;
    }

    private function toastType(string $type): ToastType
    {
        return ToastType::tryFrom($type) ?? throw new \InvalidArgumentException(\sprintf('Unknown toast type "%s", expected one of "%s".', $type, implode('", "', $this->toastTypes())));
    }
}
