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
 *
 * localStorage fwpromo:<id>:dismissed holds the time of a click; blocked storage means hidden on this page only.
 * Nothing is loaded and nothing is sent. Widgets arrive after the page: new elements are prepared through a
 * MutationObserver, clicks are caught at the document. Plain script without a build and without jQuery.
 *
 * Several Feinwerk plugins ship this file, in different versions. window.fwpromoCardKit holds the version that runs:
 * an equal or newer copy leaves the page to it; this copy also runs after an older one (version 1, Campaign Score
 * 5.2.0 and 6.0.0-beta.4, single cards only, which never sees a template's items and leaves ready cards alone) and takes
 * the clicks on its items first (capture phase), so the order in which the plugins' files load does not matter.
 */
(function () {
    'use strict';

    var VERSION = 3;
    if ((window.fwpromoCardKit || 0) >= VERSION) {
        return;
    }
    window.fwpromoCardKit = VERSION;

    var DAY = 86400000;
    var GROUP = '[data-fwpromo-rotation]';
    var ITEM = '[data-fwpromo-id]';
    var CLOSE = '.fwpromo-card__close, [data-fwpromo-close]';

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
            waiting.setAttribute('data-fwpromo-ready', '1');
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
            card.setAttribute('data-fwpromo-ready', '1');
        }
    }

    function prepare() {
        Array.prototype.forEach.call(document.querySelectorAll(GROUP + ':not([data-fwpromo-started])'), startGroup);
        Array.prototype.forEach.call(document.querySelectorAll('.fwpromo-card:not([data-fwpromo-ready])'), startCard);
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
        if (!item || !(item.closest(GROUP) || item.classList.contains('fwpromo-card'))) {
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
        remove(item);
        if (group) {
            advance(group);
        }
    }, true);

    if (typeof window.MutationObserver === 'function') {
        new window.MutationObserver(prepare).observe(document.documentElement, {childList: true, subtree: true});
    }
    document.addEventListener('DOMContentLoaded', prepare);
    prepare();
})();
