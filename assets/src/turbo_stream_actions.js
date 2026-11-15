import { navigator, StreamActions } from '@hotwired/turbo';

StreamActions.refresh_frame = function () {
    const frame = document.getElementById(this.target);

    if (!frame) {
        return;
    }

    const url = new URL(this.getAttribute('url'), document.baseURI);

    if (frame.getAttribute('src') === url.href) {
        frame.reload();
    } else {
        frame.src = url.href;
    }

    if ('true' === this.getAttribute('advance') && url.href !== window.location.href) {
        navigator.history.push(url);
    }
};

StreamActions.visit_parent_frame = function () {
    const parent = document.getElementById(this.target)?.parentElement?.closest('turbo-frame');

    if (parent) {
        parent.src = new URL(this.getAttribute('url'), document.baseURI).href;
    }
};

StreamActions.reload_frame = function () {
    document.getElementById(this.target)?.reload();
};

StreamActions.close_dialog = function () {
    document.getElementById(this.target)?.close();
};
