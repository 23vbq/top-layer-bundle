import { Controller } from '@hotwired/stimulus';

// A modal dialog makes everything outside it inert, popovers included. The toast
// container is therefore moved into the topmost modal and shown again as a popover,
// so it sits above that modal's backdrop and stays clickable.
export default class extends Controller {
    #observer = null;
    #body = null;

    connect() {
        this.#body = document.body;
        this.#raise();

        this.#observer = new MutationObserver((records) => {
            if (records.some((record) => this.#affectsStacking(record))) {
                this.#raise();
            }
        });
        // A toast added after something else entered the top layer, and the popover
        // attribute removed by a Turbo morph, which syncs attributes with the server HTML
        this.#observer.observe(this.element, { childList: true, attributes: true, attributeFilter: ['popover'] });
        this.#observer.observe(document.body, { subtree: true, attributes: true, attributeFilter: ['open'] });
    }

    disconnect() {
        this.#observer?.disconnect();
        this.#observer = null;

        // Removed together with the dialog it was moved into (turbo-stream remove or replace,
        // a reloaded frame, a morph): the container is still the dialog's child, only in a
        // detached subtree. Put it back so later toasts still find their target. A container
        // removed on its own has no parent and is left out. A Turbo Drive visit swaps the
        // whole body, the new page brings its own container.
        if (
            !this.element.isConnected
            && this.element.parentElement instanceof HTMLDialogElement
            && document.body === this.#body
            && !document.getElementById(this.element.id)
        ) {
            this.#body.append(this.element);
        }
    }

    #affectsStacking(record) {
        if (record.target === this.element) {
            return 'attributes' === record.type || 0 < record.addedNodes.length;
        }

        return record.target instanceof HTMLDialogElement;
    }

    #raise() {
        if ('manual' !== this.element.popover) {
            this.element.popover = 'manual';
        }

        const host = this.#topmostModal() ?? this.#body;

        this.#hide();

        if (this.element.parentElement !== host) {
            this.#move(host);
        }

        // The popover shown last is on top of the top layer
        this.element.showPopover();
    }

    // Hit testing follows the top layer order and a modal's backdrop covers the viewport.
    // A dialog fading out after close() stays in the top layer until its exit transition
    // ends, but is no longer :modal, so it is skipped in favour of the modal below it.
    #topmostModal() {
        return document.elementsFromPoint(0, 0)
            .map((element) => element.closest('dialog'))
            .find((dialog) => dialog?.matches(':modal')) ?? null;
    }

    // moveBefore() keeps the toasts' transitions, @starting-style state and focus,
    // append() resets them in browsers that don't support it yet
    #move(host) {
        if ('function' === typeof host.moveBefore) {
            try {
                host.moveBefore(this.element, null);

                return;
            } catch {
                // fall back to a regular move
            }
        }

        host.append(this.element);
    }

    #hide() {
        if (this.element.matches(':popover-open')) {
            this.element.hidePopover();
        }
    }
}
