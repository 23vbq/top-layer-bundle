import { StreamActions, visit } from '@hotwired/turbo';
import { loadFrame } from './frame.js';

StreamActions.refresh_frame = function () {
    const frame = findElement(this, isFrame, '<turbo-frame>');
    const url = readUrl(this);

    if (!frame || !url) {
        return;
    }

    if ('true' === this.getAttribute('advance') && url.href !== window.location.href) {
        // Turbo pushes the history entry only once the frame has loaded
        visit(url.href, { frame: frame.id, action: 'advance' });
    } else {
        loadFrame(frame, url);
    }
};

StreamActions.visit_parent_frame = function () {
    const element = findElement(this, () => true, 'element');
    const url = readUrl(this);

    if (!element || !url) {
        return;
    }

    const parent = element.parentElement?.closest('turbo-frame');

    if (!parent) {
        warn(this, `#${this.target} is not inside a <turbo-frame>`);

        return;
    }

    loadFrame(parent, url);
};

StreamActions.reload_frame = function () {
    findElement(this, isFrame, '<turbo-frame>')?.reload();
};

StreamActions.close_dialog = function () {
    findElement(this, (element) => element instanceof HTMLDialogElement, '<dialog>')?.close();
};

function isFrame(element) {
    return 'turbo-frame' === element.localName;
}

function findElement(stream, matches, expected) {
    const element = document.getElementById(stream.target);

    if (!element) {
        warn(stream, `no element with id "${stream.target}"`);

        return null;
    }

    if (!matches(element)) {
        warn(stream, `#${stream.target} is a <${element.localName}>, expected ${expected}`);

        return null;
    }

    return element;
}

function readUrl(stream) {
    const url = stream.getAttribute('url');

    if (!url) {
        warn(stream, 'missing url attribute');

        return null;
    }

    return new URL(url, document.baseURI);
}

function warn(stream, message) {
    console.warn(`[top-layer] ${stream.action}: ${message}`);
}
