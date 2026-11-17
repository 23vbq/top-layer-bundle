import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    #observer = null;

    connect() {
        this.element.popover = 'manual';
        this.#raise();

        this.#observer = new MutationObserver(() => this.#raise());
        this.#observer.observe(this.element, { childList: true });
        this.#observer.observe(document.body, { subtree: true, attributes: true, attributeFilter: ['open'] });
    }

    disconnect() {
        this.#observer?.disconnect();
        this.#observer = null;
    }

    #raise() {
        const host = this.#topmostModal() ?? document.body;

        if (this.element.parentElement !== host) {
            this.#hide();
            host.append(this.element);
        }

        this.#hide();
        this.element.showPopover();
    }

    #topmostModal() {
        const dialog = document.elementFromPoint(0, 0)?.closest('dialog');

        return dialog?.matches(':modal') ? dialog : null;
    }

    #hide() {
        if (this.element.matches(':popover-open')) {
            this.element.hidePopover();
        }
    }
}
