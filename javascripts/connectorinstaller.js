/*
 * Feinwerk kit: the connector installer page. One click only: the button turns into a progress label and ignores
 * further clicks while Matomo downloads and activates the connector (a plain link otherwise starts a second run).
 *
 * Every Feinwerk plugin ships this file, in different versions, and Matomo merges all copies (Peter, 2026-11-24: "Es kann
 * generell viele gleichzeitig aktive plugins geben, die den Kit tragen. Das muss funktionieren, selbst wenn das der fall
 * ist."). So one click is one action, whatever the number and versions of the copies:
 * - window.fwciKit: a copy binds only when no equal or newer copy did;
 * - a capture listener notes on the click whether the button was already busy before any copy's handler ran
 *   (fwciWasBusy); the decision uses that, never the attribute an earlier handler of the same click just set (version 1
 *   read it and cancelled the navigation when a second copy had bound);
 * - the first handler marks the click (fwciHandled); later handlers of the same click leave it alone;
 * - the busy state is the element property fwciBusy at once; the attribute, class and label follow after this click's
 *   dispatch, so an older copy that runs later in the same click (version 1, or the unguarded one in fe32b50) still
 *   finds the button free and does not cancel the navigation.
 * Version 3 (2026-11-24, Conversions API's UI review), whichever copy handles the click: the capture listener keeps
 * the button's label (data-fwci-label) and fills the status region ([data-fwci-status], role=status) with the progress
 * label for screen readers; a page restored from the browser's back-forward cache (pageshow, persisted) gets its
 * button back instead of a stuck progress label.
 * Check: tests/js/run-connectorinstaller-check.sh (1 to 20 copies, mixed with older and newer versions).
 */
(function ($) {
    'use strict';
    var VERSION = 3;
    if ((window.fwciKit || 0) >= VERSION) {
        return;
    }
    window.fwciKit = VERSION;

    function runOf(target) {
        return target && target.closest ? target.closest('.fwci__run') : null;
    }

    function busy(run) {
        return run.fwciBusy === true || run.getAttribute('aria-disabled') === 'true';
    }

    document.addEventListener('click', function (ev) {
        var run = runOf(ev.target);
        if (!run) {
            return;
        }
        if (typeof ev.fwciWasBusy !== 'boolean') {
            ev.fwciWasBusy = busy(run);
        }
        // version 3: before any copy's handler changes it, the label for a reset; after them, the status region
        if (!run.hasAttribute('data-fwci-label')) {
            run.setAttribute('data-fwci-label', $.trim($(run).text()));
        }
        window.setTimeout(function () {
            if (busy(run)) {
                $(run).closest('.fwci').find('[data-fwci-status]').text(run.getAttribute('data-fwci-busy') || '');
            }
        }, 0);
    }, true);

    // back from the back-forward cache: the page was left mid-run, its button works again
    window.addEventListener('pageshow', function (ev) {
        if (!ev.persisted) {
            return;
        }
        $('.fwci__run').each(function () {
            var label = this.getAttribute('data-fwci-label');
            this.fwciBusy = false;
            $(this).removeAttr('aria-disabled').removeClass('fwci__run--busy');
            if (label) {
                $(this).text(label);
            }
            $(this).closest('.fwci').removeAttr('aria-busy').find('[data-fwci-status]').text('');
        });
    });

    $(document).on('click', '.fwci__run', function (ev) {
        var run = this;
        var click = ev.originalEvent || {};
        if (click.fwciHandled) {
            return;
        }
        click.fwciHandled = true;
        var wasBusy = typeof click.fwciWasBusy === 'boolean'
            ? click.fwciWasBusy
            : run.fwciBusy === true || run.getAttribute('aria-disabled') === 'true';
        if (wasBusy) {
            ev.preventDefault();
            return;
        }
        run.fwciBusy = true;
        window.setTimeout(function () {
            var $run = $(run);
            var busy = $run.attr('data-fwci-busy');
            $run.attr('aria-disabled', 'true').addClass('fwci__run--busy');
            if (busy) {
                $run.text(busy);
            }
            $run.closest('.fwci').attr('aria-busy', 'true');
        }, 0);
    });
})(jQuery);
