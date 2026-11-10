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
