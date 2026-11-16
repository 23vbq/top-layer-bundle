import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['progress'];
    static values = { delay: { type: Number, default: 6000 } };

    #animation = null;
    #hovered = false;
    #focused = false;

    connect() {
        if (0 >= this.delayValue || !this.hasProgressTarget) {
            return;
        }

        // scaleX instead of width runs on the compositor, without a layout per frame
        this.#animation = this.progressTarget.animate(
            [{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }],
            { duration: this.delayValue, easing: 'linear', fill: 'forwards' },
        );

        this.#animation.onfinish = () => this.dismiss();
    }

    disconnect() {
        this.#animation?.cancel();
        this.#animation = null;
    }

    pointerEnter() {
        this.#hovered = true;
        this.#update();
    }

    pointerLeave() {
        this.#hovered = false;
        this.#update();
    }

    focusIn() {
        this.#focused = true;
        this.#update();
    }

    focusOut(event) {
        // Focus moving between elements inside the toast keeps it paused
        this.#focused = this.element.contains(event.relatedTarget);
        this.#update();
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

    // The timer runs only while the toast is neither hovered nor focused
    #update() {
        if (this.#hovered || this.#focused) {
            this.#animation?.pause();
        } else {
            this.#animation?.play();
        }
    }
}
