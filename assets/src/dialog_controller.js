import { Controller } from '@hotwired/stimulus';
import './turbo_stream_actions.js';

export default class extends Controller {
    static targets = ['dialog'];

    connect() {
        document.addEventListener('turbo:before-cache', this.closeAll);
    }

    disconnect() {
        document.removeEventListener('turbo:before-cache', this.closeAll);
        this.closeAll();
    }

    open(event) {
        const id = this.#param(event, 'id');
        const src = this.#param(event, 'src');

        const dialog = id
            ? this.dialogTargets.find((candidate) => candidate.id === id)
            : this.dialogTargets[0];

        if (!dialog || dialog.open) {
            return;
        }

        if (src) {
            const frame = dialog.querySelector('turbo-frame');

            if (frame) {
                const url = new URL(src, document.baseURI).href;

                if (frame.getAttribute('src') === url) {
                    frame.reload();
                } else {
                    frame.src = url;
                }
            }
        }

        dialog.showModal();
    }

    close(event) {
        const origin = event?.currentTarget instanceof Element ? event.currentTarget : event?.target;

        if (origin instanceof Element) {
            origin.closest('dialog')?.close();
        }
    }

    // Turbo snapshots the page before disconnect() runs. An open dialog in the
    // snapshot would come back from the cache as a non-modal, inline <dialog open>.
    closeAll = () => {
        this.dialogTargets.forEach((dialog) => dialog.open && dialog.close());
    };

    // Stimulus casts params with JSON.parse, so id="42" or id="1e3" would become
    // numbers. Read the raw attribute to keep ids and URLs as written, and fall
    // back to params for programmatic calls, e.g. open({ params: { id, src } }).
    #param(event, name) {
        const element = event?.currentTarget;
        const attribute = `data-${this.identifier}-${name}-param`;

        if (element instanceof Element && element.hasAttribute(attribute)) {
            return element.getAttribute(attribute) || null;
        }

        const value = event?.params?.[name];

        return value != null && value !== '' ? String(value) : null;
    }
}
