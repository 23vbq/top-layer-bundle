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

## Quick start

Place a modal and the button that opens it inside an element with the bundle's Stimulus controller:

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

Clicking the button loads the URL into the modal. The page behind it renders its content inside a matching `<turbo-frame>`:

```twig
<turbo-frame id="employee-action">
    {# form, confirmation, ... #}
</turbo-frame>
```

## Documentation

- [Modal and drawer](docs/modal-and-drawer.md) - components, props, opening and closing
- [Turbo Stream actions](docs/turbo-streams.md) - closing dialogs and reloading frames from a server response
- [Toasts](docs/toasts.md) - flash-based notifications, configuration, toasts above modals
- [Styling](docs/styling.md) - CSS variables, dark mode, overriding classes and templates
