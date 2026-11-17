/*
 * Feinwerk promotion kit (docs/promo-kit.md; markup in templates/_promo.twig and templates/_promoBox.twig): the tip
 * line, the admin home banner and the context boxes.
 * - "Hide" keeps a message out of this browser for 30 days (localStorage fwpromo:<id>:dismissed holds the time of the
 *   dismissal) and removes every notice of that message on the page.
 * - One notice per placement and page (data-fwpromo-placement; a line without it counts as "line"): when several
 *   reports or several Feinwerk plugins render the same placement, the first one that arrives stays, the others go.
 * Nothing is loaded and nothing is sent. Reports arrive and reload after the page, so new notices are prepared through
 * a MutationObserver and clicks are caught at the document. Plain script without a build and without jQuery.
 * Several Feinwerk plugins ship this file, in different versions. window.fwpromoKit holds the version that runs: an equal
 * or newer copy leaves the page to it. This copy (version 3) also runs after an older one (version 2: "the first copy
 * wins", without the compact logo or session-only hiding in demo), fits the lines that copy showed, takes out demo
 * notices hidden this session, and takes the clicks on the x first (capture phase), so no load order loses them
 * (tests/js/run-promo-check.sh).
 */
(function () {
    'use strict';

    var VERSION = 3;
    if ((window.fwpromoKit || 0) >= VERSION) {
        return;
    }
    window.fwpromoKit = VERSION;

    var DAY = 86400000;
    var NOTICES = '.fwpromo-line, .fwpromo-box';
    var DISMISS = '.fwpromo-dismiss, .fwpromo-line__dismiss';

    function key(id) {
        return 'fwpromo:' + id + ':dismissed';
    }

    function placementOf(notice) {
        return notice.getAttribute('data-fwpromo-placement') || 'line';
    }

    // demo mode (data-fwpromo-demo, test instances): a hidden notice stays away for the browser session only
    function storage(notice) {
        return notice.hasAttribute('data-fwpromo-demo') ? window.sessionStorage : window.localStorage;
    }

    function dismissed(notice) {
        try {
            var at = parseInt(storage(notice).getItem(key(notice.getAttribute('data-fwpromo-id'))) || '0', 10);
            if (notice.hasAttribute('data-fwpromo-demo')) {
                return at > 0;
            }
            var days = parseInt(notice.getAttribute('data-fwpromo-days') || '30', 10);
            return at > 0 && Date.now() - at < days * DAY;
        } catch (e) {
            return false;
        }
    }

    function remove(notice) {
        // a context box that is the only content of its Matomo widget (data-fwpromo-widget="hide"): hide that widget as
        // well, so no gap stays behind; a box inside a report widget never takes the report with it
        var widget = notice.getAttribute('data-fwpromo-widget') === 'hide' && notice.closest
            ? notice.closest('.matomo-widget') : null;
        if (widget) {
            widget.style.display = 'none';
        }
        if (notice.parentNode) {
            notice.parentNode.removeChild(notice);
        }
    }

    // The tip line starts with the Feinwerk logo. A line too short for the logo's word mark next to the sentence, its
    // link and the x shows only the round mark (class fwpromo-line--compact); measured when the line shows and whenever
    // its width changes, so a narrow dashboard widget gets it as well as a phone.
    function fit(line) {
        var logo = line.querySelector('.fwlogo');
        var text = line.querySelector('.fwpromo-line__text');
        var link = line.querySelector('.fwpromo-line__link');
        if (!logo || !text) {
            return;
        }
        line.classList.remove('fwpromo-line--compact');
        if (text.clientWidth < 80 || logo.scrollWidth > logo.clientWidth + 1 || (link && link.scrollWidth > link.clientWidth + 1)) {
            line.classList.add('fwpromo-line--compact');
        }
    }

    var resizeObserver = typeof window.ResizeObserver === 'function'
        ? new window.ResizeObserver(function (entries) {
            entries.forEach(function (entry) {
                fit(entry.target);
            });
        })
        : null;

    function watch(notice) {
        if (!notice.classList.contains('fwpromo-line') || notice.hasAttribute('data-fwpromo-fit')) {
            return;
        }
        notice.setAttribute('data-fwpromo-fit', '1');
        fit(notice);
        if (resizeObserver) {
            resizeObserver.observe(notice);
        }
    }

    function prepare() {
        var notices = document.querySelectorAll(NOTICES);
        var shown = {};
        Array.prototype.forEach.call(notices, function (notice) {
            if (!notice.getAttribute('data-fwpromo-ready')) {
                return;
            }
            // shown by an older copy: a demo notice hidden this session goes, the others get the compact logo
            if (notice.hasAttribute('data-fwpromo-demo') && dismissed(notice)) {
                remove(notice);
                return;
            }
            shown[placementOf(notice)] = true;
            watch(notice);
        });
        Array.prototype.forEach.call(notices, function (notice) {
            if (notice.getAttribute('data-fwpromo-ready')) {
                return;
            }
            var placement = placementOf(notice);
            if (shown[placement] || dismissed(notice)) {
                remove(notice);
                return;
            }
            notice.setAttribute('data-fwpromo-ready', '1');
            shown[placement] = true;
            watch(notice);
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest(DISMISS) : null;
        var notice = button ? button.closest(NOTICES) : null;
        if (!notice) {
            return;
        }
        event.preventDefault();
        // an older copy's handler (bubble phase) does not run as well
        event.stopPropagation();
        var id = notice.getAttribute('data-fwpromo-id') || '';
        try {
            storage(notice).setItem(key(id), String(Date.now()));
        } catch (e) {
            // private mode or blocked storage: hidden on this page only
        }
        Array.prototype.forEach.call(document.querySelectorAll(NOTICES), function (other) {
            if (other.getAttribute('data-fwpromo-id') === id) {
                remove(other);
            }
        });
    }, true);

    function start() {
        prepare();
        if (!window.MutationObserver) {
            return;
        }
        var scheduled = false;
        new window.MutationObserver(function () {
            if (scheduled) {
                return;
            }
            scheduled = true;
            window.requestAnimationFrame(function () {
                scheduled = false;
                prepare();
            });
        }).observe(document.body, {childList: true, subtree: true});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
