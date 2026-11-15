<?php

namespace Vbq23\TopLayerBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vbq23\TopLayerBundle\Turbo\TopLayerStream;

final class TopLayerExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        $options = ['is_safe' => ['html']];

        return [
            new TwigFunction('top_layer_close_dialog', [TopLayerStream::class, 'closeDialog'], $options),
            new TwigFunction('top_layer_reload_frame', [TopLayerStream::class, 'reloadFrame'], $options),
            new TwigFunction('top_layer_refresh_frame', [TopLayerStream::class, 'refreshFrame'], $options),
            new TwigFunction('top_layer_visit_parent_frame', [TopLayerStream::class, 'visitParentFrame'], $options),
        ];
    }
}
