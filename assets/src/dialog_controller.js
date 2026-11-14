import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dialog'];

    open({ params }) {
        const dialog = params.id
            ? this.dialogTargets.find((candidate) => candidate.id === params.id)
            : this.dialogTargets[0];

        if (!dialog || dialog.open) {
            return;
        }

        if (params.src) {
            const frame = dialog.querySelector('turbo-frame');

            if (frame) {
                const url = new URL(params.src, document.baseURI).href;

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
        event.target.closest('dialog')?.close();
    }

    disconnect() {
        this.dialogTargets.forEach((dialog) => dialog.open && dialog.close());
    }
}
