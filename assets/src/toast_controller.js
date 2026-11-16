import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['progress'];
    static values = { delay: { type: Number, default: 6000 } };

    #animation = null;

    connect() {
        if (0 >= this.delayValue || !this.hasProgressTarget) {
            return;
        }

        this.#animation = this.progressTarget.animate(
            [{ width: '100%' }, { width: '0%' }],
            { duration: this.delayValue, easing: 'linear', fill: 'forwards' },
        );

        this.#animation.onfinish = () => this.dismiss();
    }

    disconnect() {
        this.#animation?.cancel();
        this.#animation = null;
    }

    pause() {
        this.#animation?.pause();
    }

    resume() {
        this.#animation?.play();
    }

    dismiss() {
        if (this.#animation) {
            this.#animation.onfinish = null;
            this.#animation.cancel();
            this.#animation = null;
        }

        this.element.addEventListener('transitionend', () => this.element.remove(), { once: true });
        this.element.setAttribute('data-leaving', '');
    }
}
