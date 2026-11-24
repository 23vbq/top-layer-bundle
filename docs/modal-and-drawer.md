[TopLayerBundle](../README.md) · **Modal and drawer** · [Turbo Stream actions](turbo-streams.md) · [Toasts](toasts.md) · [Styling](styling.md)

# Modal and drawer

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

## Props

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

## Opening and closing

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

The dialog closes with the header's close button, the Escape key, or any element inside it with `data-action="23vbq--top-layer-bundle--dialog#close"`. To close it from a server response, e.g. after a form submitted in the dialog, use the [Turbo Stream actions](turbo-streams.md).

To change how the dialogs look, see [Styling](styling.md).
