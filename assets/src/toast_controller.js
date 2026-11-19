import { Controller } from '@hotwired/stimulus';

const TIMER = 'top-layer-toast-timer';

export default class extends Controller {
    static targets = ['progress'];
    static values = { delay: { type: Number, default: 6000 } };

    #animation = null;
    #hovered = false;
    #focused = false;

    connect() {
        if (0 >= this.delayValue || !this.hasProgressTarget || this.element.hasAttribute('data-leaving')) {
            return;
        }

        // The top-layer controller moves the container into the topmost modal and back.
        // Stimulus sees a move as removal and insertion, so the toast is disconnected and
        // connected again. Picking up the running timer keeps its remaining time and pause.
        this.#animation = this.progressTarget.getAnimations().find((animation) => animation.id === TIMER)
            // scaleX instead of width runs on the compositor, without a layout per frame
            ?? this.progressTarget.animate(
                [{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }],
                { id: TIMER, duration: this.delayValue, easing: 'linear', fill: 'forwards' },
            );

        this.#animation.onfinish = () => this.dismiss();
    }

    disconnect() {
        // Still in the document: moved, not removed, connect() follows right after
        if (this.element.isConnected) {
            return;
        }

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

        this.element.setAttribute('data-leaving', '');

        // Waits only for the toast's own leave transitions: none (transition: none, no bundle CSS,
        // display: none) removes it right away, transitions of its children are not included
        // and other CSS animations, which may never end, are ignored.
        const remove = () => this.element.remove();
        const transitions = this.element.getAnimations().filter((animation) => animation instanceof CSSTransition);

        Promise.all(transitions.map((transition) => transition.finished)).then(remove, remove);
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
