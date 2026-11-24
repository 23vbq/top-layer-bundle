[TopLayerBundle](../README.md) · [Modal and drawer](modal-and-drawer.md) · **Turbo Stream actions** · [Toasts](toasts.md) · [Styling](styling.md)

# Turbo Stream actions

The bundle registers custom [Turbo Stream](https://turbo.hotwired.dev/handbook/streams) actions for closing dialogs and reloading frames from a server response:

| Action | Twig function | Effect |
|---|---|---|
| `close_dialog` | `top_layer_close_dialog(dialogId)` | Closes the `<dialog>` with the given id |
| `reload_frame` | `top_layer_reload_frame(frameId)` | Reloads the `<turbo-frame>` from its current `src` |
| `refresh_frame` | `top_layer_refresh_frame(frameId, url, advance = false)` | Loads `url` into the frame, or reloads it when it already shows that URL. With `advance: true` the URL is also pushed to the browser history |
| `visit_parent_frame` | `top_layer_visit_parent_frame(frameId, url)` | Loads `url` into the closest `<turbo-frame>` enclosing the given frame |

The actions are loaded together with the dialog controller, so there is nothing to configure.

## Form in a modal that closes on success

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

## From PHP

The same markup is available from `TopLayerStream`, e.g. to combine it with the helpers from ux-turbo:

```php
use Symfony\UX\Turbo\Helper\TurboStream;
use Vbq23\TopLayerBundle\Turbo\TopLayerStream;

$stream = TopLayerStream::closeDialog('modal-employee-new')
    .TurboStream::append('flashes', $this->renderView('_flash.html.twig'));
```

## Importing the actions manually

The actions are registered when the dialog controller's module loads. That happens on page load as long as the controller keeps `"fetch": "eager"`. If you switch it to `"lazy"` or disable it in `assets/controllers.json`, a page without a dialog has no actions and Turbo reports an unknown action. In that case import them yourself, e.g. in `assets/app.js`:

```js
// AssetMapper
import '@23vbq/top-layer-bundle/turbo_stream_actions.js';

// Webpack Encore
import '@23vbq/top-layer-bundle/src/turbo_stream_actions.js';
```

Importing them twice is harmless.
