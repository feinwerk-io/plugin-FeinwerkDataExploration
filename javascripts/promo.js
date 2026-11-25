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
 * Version 4 (2026-11-23): a line too short for its sentence drops the sentence entirely and keeps logo mark, link and x
 * (Peter: "Compact: logo, link, x"; never a cut fragment), the link is never cut, the focus moves on after an x, and
 * the admin home banner moves below Matomo's own notices (Peter: "Below Matomo's notices").
 * Version 5 (2026-11-24, Conversions API's UI review): a compact line shows its sentence only when it fits whole, else
 * none (a phone at 390 px showed "UTM-Builder und Nam..."); lines an older copy measured first are measured again
 * (data-fwpromo-fit holds the version); after an x with nothing after it the focus goes to the control before it.
 * Version 6 (2026-11-25, Campaign Manager's UI review; Peter: "Kurzer Linktext"): a link with a short form
 * (.fwpromo-line__link-short) shows it where the full link does not fit even without the sentence
 * (fwpromo-line--short); a phone at 390 px was made wider than the screen by the connector's long link.
 */
(function () {
    'use strict';

    var VERSION = 6;
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

    // after an x the focus moves on to the next control of the page instead of falling back to its start
    var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), '
        + 'textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    function focusAfter(node) {
        if (!document.documentElement.contains(node)) {
            return;
        }
        var all = document.querySelectorAll(FOCUSABLE);
        for (var i = 0; i < all.length; i++) {
            if (!node.contains(all[i]) && (node.compareDocumentPosition(all[i]) & 4) && all[i].offsetParent !== null) {
                all[i].focus();
                return;
            }
        }
        // version 5: nothing after it, so the control before it (never the page start)
        for (var j = all.length - 1; j >= 0; j--) {
            if (!node.contains(all[j]) && (node.compareDocumentPosition(all[j]) & 2) && all[j].offsetParent !== null) {
                all[j].focus();
                return;
            }
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
    // A line that leaves the sentence less than SENTENCE px drops it entirely (fwpromo-line--bare): logo mark, link and x
    // stay, never a cut fragment such as a single letter (UI review 2026-11-23).
    var SENTENCE = 120;
    function fit(line) {
        var logo = line.querySelector('.fwlogo');
        var text = line.querySelector('.fwpromo-line__text');
        if (!logo || !text) {
            return;
        }
        line.classList.remove('fwpromo-line--compact', 'fwpromo-line--bare', 'fwpromo-line--short');
        if (text.clientWidth < 160 || logo.scrollWidth > logo.clientWidth + 1) {
            line.classList.add('fwpromo-line--compact');
        }
        // version 5: measured with the compact logo, a compact line keeps its sentence only when it fits whole
        if (text.clientWidth < SENTENCE
            || (line.classList.contains('fwpromo-line--compact') && text.scrollWidth > text.clientWidth + 1)
        ) {
            line.classList.add('fwpromo-line--bare');
        }
        // version 6: still wider than its place without the sentence: the link's short form, the full one in its title
        if (line.classList.contains('fwpromo-line--bare') && line.querySelector('.fwpromo-line__link-short')
            && line.scrollWidth > line.clientWidth + 1
        ) {
            line.classList.add('fwpromo-line--short');
        }
    }

    // The admin home banner comes through Template.beforeContent, which Matomo renders above its own notices (for
    // example the HTTPS warning). It moves to right before the content, below them, still at the top of the page.
    function placeBanner(notice) {
        var content = document.getElementById('content');
        if (placementOf(notice) !== 'adminHome' || !content || !content.parentNode || content.contains(notice)
            || notice.nextElementSibling === content) {
            return;
        }
        content.parentNode.insertBefore(notice, content);
    }

    var resizeObserver = typeof window.ResizeObserver === 'function'
        ? new window.ResizeObserver(function (entries) {
            entries.forEach(function (entry) {
                fit(entry.target);
            });
        })
        : null;

    function watch(notice) {
        // an older copy that measured the line first set a lower version (1): this copy measures it again and keeps
        // it measured (its observer runs after the older one's, so its result stays)
        if (!notice.classList.contains('fwpromo-line')
            || parseInt(notice.getAttribute('data-fwpromo-fit') || '0', 10) >= VERSION
        ) {
            return;
        }
        notice.setAttribute('data-fwpromo-fit', String(VERSION));
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
            placeBanner(notice);
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
            placeBanner(notice);
            watch(notice);
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest(DISMISS) : null;
        var notice = button ? button.closest(NOTICES) : null;
        // an older copy (version 3 also listens in the capture phase) may have removed the notice already
        if (!notice || !document.documentElement.contains(notice)) {
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
        focusAfter(notice);
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
