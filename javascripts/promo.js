/*
 * Feinwerk promotion kit (docs/promo-kit.md; markup in templates/_promo.twig and templates/_promoBox.twig): the tip
 * line, the admin home banner and the context boxes.
 * - "Hide" keeps a message out of this browser for 30 days (localStorage fwpromo:<id>:dismissed holds the time of the
 *   dismissal) and removes every notice of that message on the page.
 * - One notice per placement and page (data-fwpromo-placement; a line without it counts as "line"): when several
 *   reports or several Feinwerk plugins render the same placement, the first one that arrives stays, the others go.
 * Nothing is loaded and nothing is sent. Reports arrive and reload after the page, so new notices are prepared through
 * a MutationObserver and clicks are caught at the document. Plain script without a build and without jQuery. Several
 * Feinwerk plugins ship this file: the first copy on the page does the work (window.fwpromoKit).
 */
(function () {
    'use strict';

    if (window.fwpromoKit) {
        return;
    }
    window.fwpromoKit = 2;

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
        if (!notice.classList.contains('fwpromo-line')) {
            return;
        }
        fit(notice);
        if (resizeObserver) {
            resizeObserver.observe(notice);
        }
    }

    function prepare() {
        var notices = document.querySelectorAll(NOTICES);
        var shown = {};
        Array.prototype.forEach.call(notices, function (notice) {
            if (notice.getAttribute('data-fwpromo-ready')) {
                shown[placementOf(notice)] = true;
            }
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
    });

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
