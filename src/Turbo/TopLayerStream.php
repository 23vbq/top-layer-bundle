<?php

namespace Vbq23\TopLayerBundle\Turbo;

/**
 * Builds <turbo-stream> elements for the custom actions registered by turbo_stream_actions.js.
 */
final class TopLayerStream
{
    private function __construct()
    {
    }

    /**
     * Closes the <dialog> with the given id.
     */
    public static function closeDialog(string $dialogId): string
    {
        return self::stream('close_dialog', ['target' => $dialogId]);
    }

    /**
     * Reloads the <turbo-frame> with the given id from its current src.
     */
    public static function reloadFrame(string $frameId): string
    {
        return self::stream('reload_frame', ['target' => $frameId]);
    }

    /**
     * Loads the URL into the <turbo-frame>, or reloads it when it already shows that URL.
     * With $advance the URL is also pushed to the browser history.
     */
    public static function refreshFrame(string $frameId, string $url, bool $advance = false): string
    {
        $attributes = ['target' => $frameId, 'url' => $url];

        if ($advance) {
            $attributes['advance'] = 'true';
        }

        return self::stream('refresh_frame', $attributes);
    }

    /**
     * Loads the URL into the closest <turbo-frame> enclosing the frame with the given id.
     */
    public static function visitParentFrame(string $frameId, string $url): string
    {
        return self::stream('visit_parent_frame', ['target' => $frameId, 'url' => $url]);
    }

    /**
     * @param array<string, string> $attributes
     */
    private static function stream(string $action, array $attributes): string
    {
        $html = '<turbo-stream action="'.$action.'"';

        foreach ($attributes as $name => $value) {
            $html .= ' '.$name.'="'.htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE).'"';
        }

        return $html.'></turbo-stream>';
    }
}
