# Styling

The bundle ships a small stylesheet with no dependency on Tailwind or any other CSS framework. Every rule lives in the `top-layer` cascade layer, so your own styles can override it without `!important` or higher specificity.

## CSS variables

The quickest way to adjust the look is to override the custom properties:

| Variable | Default | Used for |
|---|---|---|
| `--top-layer-bg` | `#fff` | Modal and drawer background |
| `--top-layer-text` | `#182026` | Title, close button on hover |
| `--top-layer-muted` | `#8a97a3` | Close button |
| `--top-layer-hover` | `#f2f6f7` | Close button background on hover |
| `--top-layer-border` | `#e2e7eb` | Outer border, header separator |
| `--top-layer-radius` | `10px` | Modal corner radius |
| `--top-layer-shadow` | `0 8px 30px rgba(16, 32, 38, .08)` | Modal shadow |
| `--top-layer-drawer-shadow` | `-12px 0 32px rgba(16, 32, 38, .12)` | Drawer shadow |
| `--top-layer-backdrop` | `rgb(16 32 38 / .38)` | Modal backdrop |
| `--top-layer-backdrop-soft` | `rgb(16 32 38 / .18)` | Drawer backdrop |
| `--top-layer-width` | `min(94vw, 900px)` | Modal width |
| `--top-layer-height` | `auto` | Modal panel height (capped at `86vh`) |
| `--top-layer-drawer-width` | `380px` | Drawer width (capped at `92vw`) |

```css
:root {
    --top-layer-radius: 4px;
    --top-layer-backdrop: rgb(0 0 0 / .5);
}
```

Variables can also be scoped to a single dialog, e.g. `#confirm-delete { --top-layer-width: 420px; }`.

Toasts have their own variables. Each type has five colors, `--top-layer-toast-{type}-{part}` with `type` one of `success`, `error`, `warning`, `info`:

| Part | Used for |
|---|---|
| `bg` | Background, also the glyph cut out of the icon |
| `border` | Border |
| `title` | Title |
| `text` | Message and dismiss button |
| `bar` | Icon and progress bar |

| Variable | Default | Used for |
|---|---|---|
| `--top-layer-toast-radius` | `9px` | Toast corner radius |
| `--top-layer-toast-shadow` | `0 4px 14px rgba(24, 32, 38, .08)` | Toast shadow |
| `--top-layer-toast-glyph` | toast background | Glyph inside the icon |
| `--top-layer-toasts-width` | `min(92vw, 420px)` | Container width |
| `--top-layer-toasts-offset` | `20px` | Distance from the bottom right corner |
| `--top-layer-toasts-gap` | `12px` | Space between toasts |

```css
:root {
    --top-layer-toast-info-bg: var(--color-brand-surface);
    --top-layer-toast-info-bar: var(--color-brand);
}
```

## Dark mode

The bundle does not include a dark theme, so it never conflicts with how your application switches themes. Override the variables wherever your dark theme is defined:

```css
.dark { /* or @media (prefers-color-scheme: dark) { :root { ... } } */
    --top-layer-bg: #151c21;
    --top-layer-text: #e4eaed;
    --top-layer-muted: #8794a0;
    --top-layer-hover: #1e282e;
    --top-layer-border: #2a363d;
    --top-layer-shadow: 0 8px 30px rgba(0, 0, 0, .45);
    --top-layer-drawer-shadow: -12px 0 32px rgba(0, 0, 0, .5);
    --top-layer-backdrop: rgb(0 0 0 / .55);
    --top-layer-backdrop-soft: rgb(0 0 0 / .35);

    --top-layer-toast-shadow: 0 4px 14px rgba(0, 0, 0, .4);
    --top-layer-toast-success-bg: #12301f;
    --top-layer-toast-success-border: #1f4a30;
    --top-layer-toast-success-title: #86efac;
    --top-layer-toast-success-text: #6fcf97;
    --top-layer-toast-success-bar: #4ade80;
    /* ... and the same for error, warning and info */
}
```

If your theme already exposes design tokens, map them instead of repeating values: `--top-layer-bg: var(--color-panel);`.

## Per-instance styling

Use the `width`, `height` and `frameClass` props or plain `class` and `style` attributes to change a single dialog:

```twig
<twig:TopLayer:Modal id="confirm" frame="confirm-frame" width="420px" class="is-danger" style="--top-layer-radius: 4px" />
```

The content inside the frame is not styled by the bundle. Padding, typography and colors there are up to your application.

## Overriding classes

For changes the variables do not cover, target the bundle's classes directly:

| Class | Element |
|---|---|
| `.top-layer-modal` | Modal `<dialog>` |
| `.top-layer-modal__panel` | Inner panel of the modal (animated on open) |
| `.top-layer-drawer` | Drawer `<dialog>` |
| `.top-layer-drawer__panel` | Inner panel of the drawer |
| `.top-layer-header` | Header bar with title and close button |
| `.top-layer-title` | `<h2>` title |
| `.top-layer-close` | Close button |
| `.top-layer-close__icon` | Close icon wrapper |
| `.top-layer-frame` | `<turbo-frame>` with the content |
| `.top-layer-toasts` | Toast container |
| `.top-layer-toast` | Toast, with `.top-layer-toast--{type}` |
| `.top-layer-toast__icon` | Icon wrapper |
| `.top-layer-toast__body` | Title and message wrapper |
| `.top-layer-toast__title` | Title |
| `.top-layer-toast__message` | Message |
| `.top-layer-toast__dismiss` | Dismiss button |
| `.top-layer-toast__progress` | Progress bar |

```css
.top-layer-header {
    height: 64px;
    padding-inline: 24px;
}
```

Unlayered CSS always wins over the `top-layer` layer. If your own styles are also in cascade layers (for example Tailwind's `base`, `components` and `utilities`), the layer declared last wins. Declare the order before any of the stylesheets load, placing `top-layer` after resets (so a reset like `* { padding: 0 }` does not wipe the bundle's spacing) and before the layers that should override it. With Tailwind v4:

```css
@layer theme, base, top-layer, components, utilities;
```

To replace the styles completely, don't load the bundle's stylesheet: disable its `autoimport` entries for the `dialog`, `toast` and `top-layer` controllers in `assets/controllers.json` and style the classes above yourself.

## Overriding the markup

To change the markup itself, create a template with the same name in your application. It takes precedence over the bundle's one:

```
templates/components/TopLayer/Modal.html.twig
templates/components/TopLayer/Drawer.html.twig
```

Copy the original from the bundle's `templates/components/` directory as a starting point and keep the `data-23vbq--top-layer-bundle--dialog-target="dialog"` attribute on the `<dialog>`.

The toast templates are overridden through Symfony's usual bundle override directory:

```
templates/bundles/TopLayerBundle/toast.html.twig
templates/bundles/TopLayerBundle/toasts_wrapper.html.twig
templates/bundles/TopLayerBundle/_flashes.html.twig
```

Keep the `data-controller`, `data-action` and `-target` attributes of the `23vbq--top-layer-bundle--toast` controller in your copy of `toast.html.twig`, and the container's `id` from `top_layer_toasts_container_id()` and its `data-controller="23vbq--top-layer-bundle--top-layer"` (see [Toasts above modals](toasts.md#toasts-above-modals)).
