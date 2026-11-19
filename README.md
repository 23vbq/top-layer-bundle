# TopLayerBundle

Native `<dialog>` modals, drawers and toasts for Symfony with Turbo and Stimulus.

## Status

Work in progress, not yet released. The API is not stable and may change at any time.

## Why native `<dialog>`

- **Top layer** - modals opened with `showModal()` and popovers are rendered in the browser's top layer, above the rest of the page.
- **No z-index** - the top layer sits above all other content, so there is no z-index stacking to manage.
- **Built-in backdrop** - the `::backdrop` pseudo-element provides an overlay without extra markup.
- **Focus trap and Escape** - modal dialogs make the rest of the page inert, keep focus inside the dialog and close on Escape out of the box.

## Installation

```bash
composer require 23vbq/top-layer-bundle
```

The bundle requires Turbo 8 (`@hotwired/turbo` `^8.0`). With AssetMapper, check the version in `importmap.php`.

## Modal and drawer

Both are Twig components rendering a native `<dialog>` with a header and an empty `<turbo-frame>`. Opening the dialog loads a URL into that frame, so the content is a regular Symfony page wrapped in a matching `<turbo-frame>`.

Place the component and the buttons that open it inside an element with the bundle's Stimulus controller:

```twig
<div data-controller="23vbq--top-layer-bundle--dialog">
    <button type="button"
            data-action="23vbq--top-layer-bundle--dialog#open"
            data-23vbq--top-layer-bundle--dialog-src-param="{{ path('employee_delete', {id: employee.id}) }}">
        Delete
    </button>

    <twig:TopLayer:Modal id="modal-employee-action" frame="employee-action" title="Delete employee" />
</div>
```

The page behind the URL renders its content inside the frame:

```twig
<turbo-frame id="employee-action">
    {# form, confirmation, ... #}
</turbo-frame>
```

A drawer works the same way and slides in from the right:

```twig
<twig:TopLayer:Drawer id="drawer-audit-log" frame="audit-log-entry" title="Audit log entry" />
```

### Props

| Prop | Component | Required | Description |
|---|---|---|---|
| `id` | Modal, Drawer | yes | `id` of the `<dialog>` |
| `frame` | Modal, Drawer | yes | `id` of the `<turbo-frame>` the content is loaded into |
| `title` | Modal, Drawer | no | Heading in the header, also used as the dialog's accessible name |
| `width` | Modal, Drawer | no | Any CSS length, e.g. `600px` or `min(90vw, 1200px)` |
| `height` | Modal | no | Fixed panel height, e.g. `70vh` |
| `frameClass` | Modal, Drawer | no | Extra classes on the `<turbo-frame>` |
| `closeLabel` | Modal, Drawer | no | Accessible label of the close button, `Close` by default |

Any other attribute (`class`, `style`, `data-*`, ...) is added to the `<dialog>` element. Classes are appended to the bundle's own.

Without a `title` the dialog has no accessible name, so give it an `aria-label`. Translate the labels the same way as any other text:

```twig
<twig:TopLayer:Modal id="preview" frame="preview-frame" aria-label="{{ 'Preview'|trans }}" closeLabel="{{ 'Close'|trans }}" />
```

Content taller than the dialog scrolls inside the frame, the header with the close button stays in place.

### Opening and closing

The `open` action accepts two parameters:

- `src` - URL loaded into the dialog's frame. Opening the same URL again reloads it.
- `id` - which dialog to open when the controller element contains more than one. Without it, the first dialog is opened.

```twig
<button type="button"
        data-action="23vbq--top-layer-bundle--dialog#open"
        data-23vbq--top-layer-bundle--dialog-id-param="drawer-audit-log"
        data-23vbq--top-layer-bundle--dialog-src-param="{{ path('audit_log_show', {id: entry.id}) }}">
    Details
</button>
```

The dialog closes with the header's close button, the Escape key, or any element inside it with `data-action="23vbq--top-layer-bundle--dialog#close"`.

## Turbo Stream actions

The bundle registers custom [Turbo Stream](https://turbo.hotwired.dev/handbook/streams) actions for closing dialogs and reloading frames from a server response:

| Action | Twig function | Effect |
|---|---|---|
| `close_dialog` | `top_layer_close_dialog(dialogId)` | Closes the `<dialog>` with the given id |
| `reload_frame` | `top_layer_reload_frame(frameId)` | Reloads the `<turbo-frame>` from its current `src` |
| `refresh_frame` | `top_layer_refresh_frame(frameId, url, advance = false)` | Loads `url` into the frame, or reloads it when it already shows that URL. With `advance: true` the URL is also pushed to the browser history |
| `visit_parent_frame` | `top_layer_visit_parent_frame(frameId, url)` | Loads `url` into the closest `<turbo-frame>` enclosing the given frame |

The actions are loaded together with the dialog controller, so there is nothing to configure.

### Form in a modal that closes on success

The page with the list opens the modal and keeps the list in its own frame:

```twig
<div data-controller="23vbq--top-layer-bundle--dialog">
    <button type="button"
            data-action="23vbq--top-layer-bundle--dialog#open"
            data-23vbq--top-layer-bundle--dialog-src-param="{{ path('employee_new') }}">
        Add employee
    </button>

    <turbo-frame id="employee-list" src="{{ path('employee_list') }}"></turbo-frame>

    <twig:TopLayer:Modal id="modal-employee-new" frame="employee-new" title="Add employee" />
</div>
```

The form is rendered inside the modal's frame:

```twig
{# employee/new.html.twig #}
<turbo-frame id="employee-new">
    {{ form(form) }}
</turbo-frame>
```

On success the controller responds with a Turbo Stream instead of a redirect:

```php
use Symfony\UX\Turbo\TurboBundle;

#[Route('/employees/new', name: 'employee_new')]
public function new(Request $request): Response
{
    $form = $this->createForm(EmployeeType::class)->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // save the employee

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->render('employee/new_success.stream.html.twig');
    }

    return $this->render('employee/new.html.twig', ['form' => $form]);
}
```

```twig
{# employee/new_success.stream.html.twig #}
{{ top_layer_close_dialog('modal-employee-new') }}
{{ top_layer_refresh_frame('employee-list', path('employee_list')) }}
```

The modal closes and the list reloads. When the form is invalid, `render()` responds with `422` and Turbo shows the errors inside the modal.

### From PHP

The same markup is available from `TopLayerStream`, e.g. to combine it with the helpers from ux-turbo:

```php
use Symfony\UX\Turbo\Helper\TurboStream;
use Vbq23\TopLayerBundle\Turbo\TopLayerStream;

$stream = TopLayerStream::closeDialog('modal-employee-new')
    .TurboStream::append('flashes', $this->renderView('_flash.html.twig'));
```

### Importing the actions manually

The actions are registered when the dialog controller's module loads. That happens on page load as long as the controller keeps `"fetch": "eager"`. If you switch it to `"lazy"` or disable it in `assets/controllers.json`, a page without a dialog has no actions and Turbo reports an unknown action. In that case import them yourself, e.g. in `assets/app.js`:

```js
// AssetMapper
import '@23vbq/top-layer-bundle/turbo_stream_actions.js';

// Webpack Encore
import '@23vbq/top-layer-bundle/src/turbo_stream_actions.js';
```

Importing them twice is harmless.

## Toasts

Toasts are built on Symfony flash messages. A toast is added in PHP, stored in the session and rendered as a Turbo Stream that appends it to a container on the page.

### Setup

Add the container and the flashes to your base layout, the container first:

```twig
{# base.html.twig #}
<body>
    {% block body %}{% endblock %}

    {{ include('@TopLayer/toasts_wrapper.html.twig') }}
    {{ include('@TopLayer/_flashes.html.twig') }}
</body>
```

The container is a fixed element in the bottom right corner and an `aria-live="polite"` region, so screen readers announce each toast added to it once they finish what they are reading. `_flashes.html.twig` turns each pending flash into a `<turbo-stream action="append">`, which Turbo runs as soon as it is in the page, so flashes added before a redirect show up after it. Toasts are marked `data-turbo-temporary`, so they don't come back from Turbo's cache when navigating back.

Responses that don't render the layout, like the Turbo Stream after a form in a modal, include the flashes themselves:

```twig
{# employee/new_success.stream.html.twig #}
{{ top_layer_close_dialog('modal-employee-new') }}
{{ top_layer_refresh_frame('employee-list', path('employee_list')) }}
{{ include('@TopLayer/_flashes.html.twig') }}
```

### Adding toasts

Inject the `Toaster` service:

```php
use Vbq23\TopLayerBundle\Toast\Toaster;

public function new(Request $request, Toaster $toaster): Response
{
    // ...
    $toaster->success('Employee created', 'Jane Doe can now sign in.');
    $toaster->error('Export failed');
}
```

| Method | Type | Closes after |
|---|---|---|
| `success(title, message = null)` | `success` | 6 s |
| `info(title, message = null)` | `info` | 6 s |
| `warning(title, message = null)` | `warning` | stays open |
| `error(title, message = null)` | `error` | stays open |

`add(new Toast(ToastType::Success, 'Saved'))` does the same with a `Toast` object. The timer pauses while the toast is hovered or has keyboard focus.

A plain flash of one of the four types is shown as a toast too, with the string as its title:

```php
$this->addFlash('success', 'Saved');
```

Flashes of any other type, like `notice`, are left alone. They stay in the session for your application to render.

### Configuration

```yaml
# config/packages/top_layer.yaml
top_layer:
    toasts:
        container_id: toasts      # id of the container toasts are appended to
        delays:                   # milliseconds, 0 keeps the toast open until dismissed
            success: 6000
            info: 6000
            error: 0
            warning: 0
```

Toasts with a delay show a progress bar along the bottom edge. Only the four types above are accepted in `delays`.

### Twig functions

| Function | Returns |
|---|---|
| `top_layer_toasts_container_id()` | Configured `container_id` |
| `top_layer_toast_types()` | `['success', 'error', 'warning', 'info']` |
| `top_layer_toast_delay(type)` | Configured delay of the type |

The dismiss button is labelled `Dismiss`. Pass `dismissLabel` to change it, e.g. to translate it:

```twig
{{ include('@TopLayer/_flashes.html.twig', {dismissLabel: 'Dismiss'|trans}) }}
```

A single toast can be rendered with `{{ include('@TopLayer/toast.html.twig', {type: 'success', flash: {title: 'Saved', message: null}}) }}`, which accepts `dismissLabel` too. Render it inside the container, the toast itself is not a live region.

### Toasts above modals

A modal opened with `showModal()` sits in the browser's top layer and makes the rest of the page inert. A toast container placed in `<body>` stays below the modal's backdrop no matter its `z-index`, and it is inert along with the rest of the page: the toast is visible but it can't be clicked, dismissed or reached with Tab. That is exactly the case of a form submitted in a modal that answers with an error toast.

The container carries the `23vbq--top-layer-bundle--top-layer` controller, which keeps it above every modal:

1. On connect it turns the container into a `popover="manual"` and shows it, so the container itself is in the top layer.
2. Whenever an `open` attribute changes anywhere on the page (a dialog opens or closes) or a toast is added, it moves the container into the topmost open modal, or back to `<body>` when there is none.
3. It then hides and shows the popover again. The element shown last is on top of the top layer, so the container ends up above the modal opened last, and being inside that modal it is no longer inert.

Nothing needs to be configured, the controller comes with the container template and is loaded eagerly. A toast keeps its remaining time and pause state when the container moves. If you override `toasts_wrapper.html.twig`, keep its `data-controller` attribute.

The topmost modal is found with `document.elementFromPoint(0, 0)`. A modal's backdrop covers the whole viewport, so the top left corner belongs to the modal opened last. Another top layer element covering that corner, like a popover of your own, hides the modal from the controller and the container stays in `<body>`.

## Styling

The bundle ships a small stylesheet with no dependency on Tailwind or any other CSS framework. Every rule lives in the `top-layer` cascade layer, so your own styles can override it without `!important` or higher specificity.

### CSS variables

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

### Dark mode

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

### Per-instance styling

Use the `width`, `height` and `frameClass` props or plain `class` and `style` attributes to change a single dialog:

```twig
<twig:TopLayer:Modal id="confirm" frame="confirm-frame" width="420px" class="is-danger" style="--top-layer-radius: 4px" />
```

The content inside the frame is not styled by the bundle. Padding, typography and colors there are up to your application.

### Overriding classes

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

### Overriding the markup

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

Keep the `data-controller`, `data-action` and `-target` attributes of the `23vbq--top-layer-bundle--toast` controller in your copy of `toast.html.twig`, and the container's `id` from `top_layer_toasts_container_id()` and its `data-controller="23vbq--top-layer-bundle--top-layer"` (see [Toasts above modals](#toasts-above-modals)).
