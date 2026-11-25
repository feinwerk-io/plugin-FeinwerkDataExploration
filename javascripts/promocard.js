/*
 * Feinwerk promotion kit: the card in dashboard widgets and rotating promotions (docs/footer.md, docs/promo-kit.md).
 *
 * - A single card (.fwpromo-card outside a rotation group, Campaign Score's first form): shown unless its x was clicked
 *   in the last data-fwpromo-days days; one card per page, the first that arrives stays.
 * - A rotation group ([data-fwpromo-rotation="card"|"banner"|...]) holds several items ([data-fwpromo-id]) in order,
 *   either in the page or inside an inert <template>: the first item not hidden shows, the next one only after its x
 *   ([data-fwpromo-close] or .fwpromo-card__close) hid it for its days. Items in a template come out one at a time, so
 *   no other script ever sees the waiting ones. One group of a kind per page (a card group also counts as a card).
 *   When no item is left the group goes, and with data-fwpromo-widget="hide" the Matomo widget around it too (only for
 *   a widget that holds nothing else; a card inside a report widget never takes the report with it).
 * - data-fwpromo-only-in="dashboard": the group shows only inside a dashboard (#dashboardWidgetsArea), so the same
 *   report on its own page shows nothing.
 * - A pausable place (version 5, Peter 2026-11-24: "Ein × blendet alles 30 Tage aus", for Page Filter Report "Ja,
 *   Kachel und Karten"): the group comes as data-fwpromo-pausable="<kind>" instead of data-fwpromo-rotation, with
 *   data-fwpromo-pause="<place>" and data-fwpromo-pause-days. Any x in it empties the place for the pause days (its
 *   item stays away for its own, longer data-fwpromo-days); after the pause the next item shows. Cards use the place
 *   "card" (a dashboard has one card for all plugins), a banner tile its own name. Demo mode sends no pause.
 *
 * localStorage fwpromo:<id>:dismissed holds the time of a click, fwpromo:place:<place>:paused the start of a pause;
 * blocked storage means hidden on this page only.
 * Nothing is loaded and nothing is sent. Widgets arrive after the page: new elements are prepared through a
 * MutationObserver, clicks are caught at the document. Plain script without a build and without jQuery.
 *
 * Several Feinwerk plugins ship this file, in different versions. window.fwpromoCardKit holds the version that runs:
 * an equal or newer copy leaves the page to it; this copy also runs after an older one (version 1, Campaign Score
 * 5.2.0 and 6.0.0-beta.4, single cards only, which never sees a template's items and leaves ready cards alone) and takes
 * the clicks on its items first (capture phase), so the order in which the plugins' files load does not matter.
 * Older copies (up to version 4) know no pause and never see a pausable group: it carries no data-fwpromo-rotation
 * until this copy has checked the pause, and an x in it is handled at the window, before any copy's handler at the
 * document, and goes no further (Peter, 2026-11-24: "Es kann generell viele gleichzeitig aktive plugins geben, die
 * den Kit tragen. Das muss funktionieren, selbst wenn das der fall ist.").
 */
(function () {
    'use strict';

    var VERSION = 5;
    if ((window.fwpromoCardKit || 0) >= VERSION) {
        return;
    }
    window.fwpromoCardKit = VERSION;

    var DAY = 86400000;
    var GROUP = '[data-fwpromo-rotation]';
    var ITEM = '[data-fwpromo-id]';
    var CLOSE = '.fwpromo-card__close, [data-fwpromo-close]';
    var PAUSABLE = '[data-fwpromo-pausable]';
    var PAUSED = '[data-fwpromo-pause-days]';

    function key(item) {
        return 'fwpromo:' + item.getAttribute('data-fwpromo-id') + ':dismissed';
    }

    // demo mode (data-fwpromo-demo, test instances, version 3): a hidden item stays away for the browser session only;
    // the page sends data-fwpromo-days="0" with it, so older copies hide it on the page only
    function storage(item) {
        return item.hasAttribute('data-fwpromo-demo') ? window.sessionStorage : window.localStorage;
    }

    function dismissed(item) {
        try {
            var at = parseInt(storage(item).getItem(key(item)) || '0', 10);
            if (item.hasAttribute('data-fwpromo-demo')) {
                return at > 0;
            }
            var days = parseInt(item.getAttribute('data-fwpromo-days') || '30', 10);
            return at > 0 && Date.now() - at < days * DAY;
        } catch (e) {
            return false;
        }
    }

    // Version 4 (2026-11-23): a card narrower than NARROW px (a dashboard column at 768 px is about 232 px) wraps: no
    // logo, the link under the text, the sentence on up to three lines (fwpromo-card--narrow); after an x the focus
    // moves to the next card's link, or to the next control of the page.
    var NARROW = 300;
    function fit(card) {
        card.classList.toggle('fwpromo-card--narrow', card.clientWidth > 0 && card.clientWidth < NARROW);
    }
    var resizeObserver = typeof window.ResizeObserver === 'function'
        ? new window.ResizeObserver(function (entries) {
            entries.forEach(function (entry) {
                fit(entry.target);
            });
        })
        : null;
    // measured once shown and whenever its width changes; also a card an older copy showed first (data-fwpromo-fit)
    function watch(card) {
        if (card.hasAttribute('data-fwpromo-fit')) {
            return;
        }
        card.setAttribute('data-fwpromo-fit', '1');
        fit(card);
        if (resizeObserver) {
            resizeObserver.observe(card);
        }
    }
    function show(item) {
        item.setAttribute('data-fwpromo-ready', '1');
        if (item.classList.contains('fwpromo-card')) {
            watch(item);
        }
    }
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
    }

    function remove(element) {
        if (element && element.parentNode) {
            element.parentNode.removeChild(element);
        }
    }

    function kindOf(group) {
        return group.getAttribute('data-fwpromo-rotation') || 'card';
    }

    /** the items of a group in the page (not those still in its template) */
    function itemsOf(group) {
        return Array.prototype.filter.call(group.querySelectorAll(ITEM), function (item) {
            return item.closest(GROUP) === group;
        });
    }

    /** a shown item of this kind somewhere else on the page (cards: also a single card) */
    function shownElsewhere(group) {
        var kind = kindOf(group);
        var shown = document.querySelectorAll(kind === 'card'
            ? '.fwpromo-card[data-fwpromo-ready], ' + GROUP + '[data-fwpromo-rotation="card"] [data-fwpromo-ready]'
            : GROUP + '[data-fwpromo-rotation="' + kind + '"] [data-fwpromo-ready]');

        return Array.prototype.some.call(shown, function (element) {
            return !group.contains(element);
        });
    }

    /** the group is empty: it goes, and the widget around it when it holds nothing else */
    function finish(group) {
        if (group.getAttribute('data-fwpromo-widget') === 'hide' && group.closest) {
            var widget = group.closest('.matomo-widget');
            if (widget) {
                widget.style.display = 'none';
            }
        }
        remove(group);
    }

    // Version 5: pausable places. A place paused on this page while storage is blocked.
    var pausedOnPage = {};

    function placeOf(group) {
        return group.getAttribute('data-fwpromo-pause') || group.getAttribute('data-fwpromo-pausable') || kindOf(group);
    }

    function pauseDays(group) {
        var days = parseInt(group.getAttribute('data-fwpromo-pause-days') || '0', 10);

        return days > 0 ? days : 0;
    }

    function pauseKey(place) {
        return 'fwpromo:place:' + place + ':paused';
    }

    function paused(group) {
        var days = pauseDays(group);
        if (!days) {
            return false;
        }
        var place = placeOf(group);
        if (pausedOnPage[place]) {
            return true;
        }
        try {
            var at = parseInt(window.localStorage.getItem(pauseKey(place)) || '0', 10);

            return at > 0 && Date.now() - at < days * DAY;
        } catch (e) {
            return false;
        }
    }

    /** a pausable group: during its place's pause it goes (with its widget), else it becomes a rotation group */
    function openPausable(group) {
        var kind = group.getAttribute('data-fwpromo-pausable') || 'card';
        group.removeAttribute('data-fwpromo-pausable');
        if (paused(group)) {
            finish(group);
            return;
        }
        group.setAttribute('data-fwpromo-rotation', kind);
    }

    /** the next item not hidden from the group's template, ready before it enters the page; null when none is left */
    function fromTemplate(group) {
        var template = group.querySelector('template');
        if (!template || !template.content) {
            return null;
        }
        var nodes = template.content.querySelectorAll(ITEM);
        for (var i = 0; i < nodes.length; i++) {
            var node = nodes[i];
            remove(node);
            if (!dismissed(node)) {
                var item = document.importNode(node, true);
                item.setAttribute('data-fwpromo-ready', '1');
                group.appendChild(item);

                return item;
            }
        }

        return null;
    }

    /** shows the first item not hidden (unless one shows already); hidden ones go */
    function advance(group) {
        var items = itemsOf(group);
        var waiting = null;
        for (var i = 0; i < items.length; i++) {
            if (items[i].getAttribute('data-fwpromo-ready')) {
                return;
            }
            if (dismissed(items[i])) {
                remove(items[i]);
            } else if (!waiting) {
                waiting = items[i];
            }
        }
        if (waiting) {
            show(waiting);
        } else if (!fromTemplate(group)) {
            finish(group);
        }
    }

    function startGroup(group) {
        if (group.getAttribute('data-fwpromo-started')) {
            return;
        }
        group.setAttribute('data-fwpromo-started', '1');
        if (group.getAttribute('data-fwpromo-only-in') === 'dashboard'
            && (!group.closest || !group.closest('#dashboardWidgetsArea'))
        ) {
            remove(group);
            return;
        }
        if (shownElsewhere(group)) {
            remove(group);
            return;
        }
        advance(group);
    }

    function startCard(card) {
        if (card.closest && card.closest(GROUP)) {
            return;
        }
        if (dismissed(card) || document.querySelector('.fwpromo-card[data-fwpromo-ready]')) {
            remove(card);
        } else {
            show(card);
        }
    }

    function prepare() {
        Array.prototype.forEach.call(document.querySelectorAll(PAUSABLE), openPausable);
        Array.prototype.forEach.call(document.querySelectorAll(GROUP + ':not([data-fwpromo-started])'), startGroup);
        Array.prototype.forEach.call(document.querySelectorAll('.fwpromo-card:not([data-fwpromo-ready])'), startCard);
        Array.prototype.forEach.call(document.querySelectorAll('.fwpromo-card[data-fwpromo-ready]:not([data-fwpromo-fit])'), watch);
        // demo: an older copy that came first knows no session hiding and may have shown a card hidden this session
        Array.prototype.forEach.call(document.querySelectorAll('.fwpromo-card[data-fwpromo-demo][data-fwpromo-ready]'), function (card) {
            if (dismissed(card)) {
                remove(card);
            }
        });
    }

    // capture phase: this copy handles the x of its items before an older copy's handler could
    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest(CLOSE) : null;
        var item = button ? button.closest(ITEM) : null;
        // an older copy (version 3 also listens in the capture phase) may have removed the item already
        if (!item || !document.documentElement.contains(item)
            || !(item.closest(GROUP) || item.classList.contains('fwpromo-card'))
        ) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        try {
            storage(item).setItem(key(item), String(Date.now()));
        } catch (e) {
            // storage blocked: hidden on this page only
        }
        var group = item.closest(GROUP);
        // where the focus goes once the item is gone: the next item's link, else the next control after it. The anchor
        // sits after the group, which finish() may remove with its last item.
        var anchor = document.createElement('span');
        var after = group || item;
        after.parentNode.insertBefore(anchor, after.nextSibling);
        remove(item);
        if (group) {
            advance(group);
        }
        var next = group && document.documentElement.contains(group) ? group.querySelector('[data-fwpromo-ready] a[href]') : null;
        if (next) {
            next.focus();
        } else if (document.documentElement.contains(anchor)) {
            focusAfter(anchor);
        }
        remove(anchor);
    }, true);

    // version 5: an x in a pausable place, at the window in the capture phase, before every copy's handler at the
    // document; the event goes no further, so no copy shows the next item. A newer copy listening here too sees
    // fwpromoPaused and leaves the click alone.
    window.addEventListener('click', function (event) {
        if (event.fwpromoPaused) {
            return;
        }
        var button = event.target && event.target.closest ? event.target.closest(CLOSE) : null;
        var item = button ? button.closest(ITEM) : null;
        var group = item ? item.closest(GROUP) : null;
        if (!group || !pauseDays(group) || item.hasAttribute('data-fwpromo-demo')
            || !document.documentElement.contains(item)
        ) {
            return;
        }
        event.fwpromoPaused = true;
        event.preventDefault();
        event.stopPropagation();
        var place = placeOf(group);
        var now = String(Date.now());
        pausedOnPage[place] = true;
        try {
            storage(item).setItem(key(item), now);
            window.localStorage.setItem(pauseKey(place), now);
        } catch (e) {
            // storage blocked: paused on this page only
        }
        // the focus goes to the next control after the place, never to the page start
        var anchor = document.createElement('span');
        group.parentNode.insertBefore(anchor, group.nextSibling);
        Array.prototype.forEach.call(document.querySelectorAll(PAUSED), function (other) {
            if (placeOf(other) === place) {
                finish(other);
            }
        });
        focusAfter(anchor);
        remove(anchor);
    }, true);

    if (typeof window.MutationObserver === 'function') {
        new window.MutationObserver(prepare).observe(document.documentElement, {childList: true, subtree: true});
    }
    document.addEventListener('DOMContentLoaded', prepare);
    prepare();
})();
