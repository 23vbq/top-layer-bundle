// The src attribute is usually relative (as rendered by Twig), so compare absolute
// URLs. reload() keeps the frame's refresh="morph", setting src does not.
export function loadFrame(frame, url) {
    const href = new URL(url, document.baseURI).href;
    const current = frame.getAttribute('src');

    if (current && new URL(current, document.baseURI).href === href) {
        frame.reload();
    } else {
        frame.src = href;
    }
}
