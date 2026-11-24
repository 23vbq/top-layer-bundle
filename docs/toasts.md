[TopLayerBundle](../README.md) · [Modal and drawer](modal-and-drawer.md) · [Turbo Stream actions](turbo-streams.md) · **Toasts** · [Styling](styling.md)

# Toasts

Toasts are built on Symfony flash messages. A toast is added in PHP, stored in the session and rendered as a Turbo Stream that appends it to a container on the page.

Colors, sizes and the toast markup are covered in [Styling](styling.md).

## Setup

Add the container to your base layout:

```twig
{# base.html.twig #}
<body>
    {% block body %}{% endblock %}

    {{ include('@TopLayer/toasts_wrapper.html.twig') }}
</body>
```

The container is a fixed element in the bottom right corner and an `aria-live="polite"` region, so screen readers announce each toast added to it once they finish what they are reading. Right after the container, the template renders the pending flashes with `_flashes.html.twig`, which turns each of them into a `<turbo-stream action="append">`. Turbo runs it as soon as it is in the page, so flashes added before a redirect show up after it. Toasts are marked `data-turbo-temporary`, so they don't come back from Turbo's cache when navigating back.

## Turbo Stream and Turbo Frame responses

Some responses don't show the container's flashes: the Turbo Stream after a [form in a modal](turbo-streams.md#form-in-a-modal-that-closes-on-success) doesn't render the layout, and Turbo keeps only the requested frame from a Turbo Frame response. The bundle adds the pending flashes to them on its own, so `$toaster->success()` before such a response is all it takes:

- **Turbo Stream responses** (`Content-Type: text/vnd.turbo-stream.html`): the toast streams are appended after the response's own streams.
- **Turbo Frame requests** (`Turbo-Frame: <id>` header): the toast streams are inserted right after the opening `<turbo-frame id="<id>">`, streams anywhere else would be dropped. This works for a partial template as well as for a full page rendered in the layout: the container leaves the flashes of a Turbo Frame request alone. The frame is found by its quoted `id` attribute, a response without it is left alone too.

Nothing is added to redirects, streamed and file responses, `HEAD` requests, sub-requests and requests without a session. The flashes stay in the session until a response renders them. A regular Turbo Drive visit gets them from the container in the layout.

The flashes are rendered with `_flashes.html.twig`, which consumes them. A template that still includes the flashes itself keeps working and shows each toast once, since nothing is left to add after it. The include has to be in a Turbo Stream template or inside the requested frame: an include outside the frame consumes the flashes and Turbo drops them with the rest of the page. The layout needs no include, the container takes care of it. A Turbo Stream template, for example:

```twig
{# employee/new_success.stream.html.twig #}
{{ top_layer_close_dialog('modal-employee-new') }}
{{ top_layer_refresh_frame('employee-list', path('employee_list')) }}
{{ include('@TopLayer/_flashes.html.twig') }}
```

Include them yourself to pass `dismissLabel`, or to place the toasts at another spot of the response. With `inject_into_turbo_responses: false` nothing is added and the container renders the flashes of every request, so Turbo Stream and Turbo Frame responses have to include them.

## Adding toasts

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

## Configuration

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
        inject_into_turbo_responses: true  # add pending flashes to Turbo Stream and Turbo Frame responses
```

Toasts with a delay show a progress bar along the bottom edge. Only the four types above are accepted in `delays`.

## Twig functions

| Function | Returns |
|---|---|
| `top_layer_toasts_container_id()` | Configured `container_id` |
| `top_layer_toast_types()` | `['success', 'error', 'warning', 'info']` |
| `top_layer_toast_delay(type)` | Configured delay of the type |
| `top_layer_inject_into_turbo_responses()` | Configured `inject_into_turbo_responses` |

The dismiss button is labelled `Dismiss`. Pass `dismissLabel` to change it, e.g. to translate it:

```twig
{# base.html.twig #}
{{ include('@TopLayer/toasts_wrapper.html.twig', {dismissLabel: 'Dismiss'|trans}) }}

{# a template that includes the flashes itself #}
{{ include('@TopLayer/_flashes.html.twig', {dismissLabel: 'Dismiss'|trans}) }}
```

Toasts added to Turbo Stream and Turbo Frame responses keep the default label.

A single toast can be rendered with `{{ include('@TopLayer/toast.html.twig', {type: 'success', flash: {title: 'Saved', message: null}}) }}`, which accepts `dismissLabel` too. Render it inside the container, the toast itself is not a live region.

## Toasts above modals

A modal opened with `showModal()` sits in the browser's top layer and makes the rest of the page inert. A toast container placed in `<body>` stays below the modal's backdrop no matter its `z-index`, and it is inert along with the rest of the page: the toast is visible but it can't be clicked, dismissed or reached with Tab. That is exactly the case of a form submitted in a modal that answers with an error toast.

The container carries the `23vbq--top-layer-bundle--top-layer` controller, which keeps it above every modal:

1. The container is rendered as a `popover="manual"`, which the controller shows on connect, so the container itself is in the top layer.
2. Whenever a dialog opens or closes, or a toast is added, it moves the container into the topmost open modal, or back to `<body>` when there is none.
3. It then hides and shows the popover again. The element shown last is on top of the top layer, so the container ends up above the modal opened last, and being inside that modal it is no longer inert.

Nothing needs to be configured, the controller comes with the container template and is loaded eagerly. If you override `toasts_wrapper.html.twig`, keep its `popover` and `data-controller` attributes.

Some details:

- **Stacked modals.** The topmost modal is found with `document.elementsFromPoint(0, 0)`, the first element there belonging to a `dialog:modal`. A modal's backdrop covers the whole viewport, so a modal is found even below other top layer elements. A dialog fading out after closing is skipped, so the container moves straight to the modal below it. A backdrop with `pointer-events: none` hides its modal from the controller.
- **Removed dialogs.** If the dialog holding the container is removed from the page, by a `<turbo-stream action="remove">`, a reloaded frame or a morph, the container is put back into `<body>` together with its toasts.
- **Morphing.** The `popover` attribute is part of the template, so a page refresh with `turbo-refresh-method` set to `morph` keeps it. If it is removed anyway, the controller restores it.
- **Toast state.** A toast keeps its remaining time and pause state when the container moves. Browsers with `Element.moveBefore()` also keep its transitions and the keyboard focus. In the others a moved toast replays its enter animation and loses focus.
