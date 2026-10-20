/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * "Open in exploration" under standard reports (Reports/OpenInExploration.php): opens the exploration page with the
 * report's dimension, period, date and segment. The action's second class names the dimension
 * (fwde-explore--browser). Matomo 5 renders the action in the report footer, Matomo 6 in the report menu, so the
 * click is handled on the document.
 */
(function ($) {
    'use strict';

    // the hash is rebuilt from these parameters (MatomoUrl.updateHash keeps nothing else, e.g. no compare
    // parameters); the exploration's own parameters start empty
    var STATE_PARAMS = [
        'fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters', 'fwdeView', 'fwdeChart'
    ];

    function dimensionOf(element) {
        var match = /(?:^|\s)fwde-explore--([A-Za-z0-9]+)(?:\s|$)/.exec(element.className || '');
        return match ? match[1] : null;
    }

    /** The condition of a report of one channel (fwde-scope--referrerType-search: referrerType==search), or ''. */
    function scopeOf(element) {
        var match = /(?:^|\s)fwde-scope--([A-Za-z0-9]+)-([A-Za-z0-9]+)(?:\s|$)/.exec(element.className || '');
        return match ? match[1] + '==' + match[2] : '';
    }

    /**
     * The table of the report the action belongs to: Matomo 5 renders the action inside it, Matomo 6 in the report
     * header next to it (full page) or in the widget's header (dashboard). The report is the div.dataTable: the HTML
     * table inside it has the class dataTable too.
     */
    function reportTable(element) {
        var $element = $(element);
        var $table = $element.closest('div.dataTable');
        if ($table.length) {
            return $table;
        }
        var $widget = $element.closest('.widget');
        if ($widget.length) {
            return $widget.find('.dataTable').first();
        }
        var $header = $element.closest('[vue-entry="CoreHome.ReportHeader"]');
        return $header.length ? $header.parent().find('.dataTable').first() : $();
    }

    /**
     * Period, date and segment the report was loaded with (a widget can have its own, e.g. with
     * FeinwerkDashboardSegment); the page's values where the report has none.
     */
    function reportView(element, current) {
        var view = { period: current.period, date: current.date, segment: current.segment || '' };
        var params = {};
        try {
            params = JSON.parse(reportTable(element).attr('data-params') || '{}') || {};
        } catch (e) {
            params = {};
        }
        ['period', 'date', 'segment'].forEach(function (name) {
            // request parameters come HTML-escaped from Matomo
            if (typeof params[name] === 'string' && params[name] !== '') {
                view[name] = piwikHelper.htmlDecode(params[name]);
            }
        });
        // the report keeps the segment as it came in the request, URL-encoded (Matomo's own row actions decode it)
        view.segment = decodeSegment(view.segment);
        return view;
    }

    function decodeSegment(segment) {
        try {
            return decodeURIComponent(segment);
        } catch (e) {
            return segment;
        }
    }

    function explorationParams(dimension, idSite, view) {
        var params = {
            idSite: idSite,
            period: view.period,
            date: view.date,
            segment: view.segment,
            category: 'FeinwerkDataExploration_Explorations',
            subcategory: 'FeinwerkDataExploration_FreeFormTable'
        };
        STATE_PARAMS.forEach(function (name) {
            params[name] = '';
        });
        params.fwdeDims = dimension;
        return params;
    }

    /** Opens the exploration page: on the reporting page by its hash, elsewhere by a new page. */
    function openExploration(MatomoUrl, params, current, view) {
        if (piwikHelper.isReportingPage()) {
            MatomoUrl.updateHash(params);
            return;
        }
        window.location.href = 'index.php?' + $.param({
            module: 'CoreHome', action: 'index', idSite: current.idSite, period: view.period, date: view.date
        }) + '#?' + $.param(params);
    }

    $(document).on('click', '.dataTableAction.fwde-explore', function (event) {
        var dimension = dimensionOf(this);
        var MatomoUrl = window.CoreHome && window.CoreHome.MatomoUrl;
        if (!dimension || !MatomoUrl || !window.piwikHelper) {
            return;
        }
        event.preventDefault();

        var current = MatomoUrl.parsed.value;
        var view = reportView(this, current);
        var scope = scopeOf(this);
        if (scope) {
            view.segment = view.segment ? view.segment + ';' + scope : scope;
        }
        openExploration(MatomoUrl, explorationParams(dimension, current.idSite, view), current, view);
    });

    /**
     * The dimension of the report a row belongs to, from its "Open in exploration" action: Matomo 5 renders the action
     * in the table, Matomo 6 keeps it in the table's config for the report menu. Null on reports without it (and on
     * subtables, which have their own table and no action).
     */
    function reportDimension(tr) {
        var $table = $(tr).closest('div.dataTable');
        var $action = $table.find('.dataTableAction.fwde-explore').first();
        if ($action.length) {
            return dimensionOf($action[0]);
        }
        var config = $table.find('.reportActionsConfig').first().data('reportActions');
        var actions = (config && config.dataTableActions) || [];
        for (var i = 0; i < actions.length; i++) {
            var match = /(?:^|\s)fwde-explore--([A-Za-z0-9]+)(?:\s|$)/.exec(actions[i].id || '');
            if (match) {
                return match[1];
            }
        }
        return null;
    }

    /**
     * "Explore this row": the exploration of the report's dimension on the visits of the row (its segment added to the
     * report's), with the second-dimension picker open to break the row down.
     */
    function exploreRow(tr) {
        var $tr = $(tr);
        var dimension = reportDimension(tr);
        var rowSegment = $tr.attr('data-segment-filter');
        var MatomoUrl = window.CoreHome && window.CoreHome.MatomoUrl;
        if (!dimension || !rowSegment || !MatomoUrl || !window.piwikHelper) {
            return;
        }
        var current = MatomoUrl.parsed.value;
        var view = reportView(tr, current);
        // in comparison mode a row belongs to one series: its segment, period and date (as Matomo's segmented visits
        // log reads them), and the value's segment from the report row it compares
        var override = $tr.data('param-override');
        if (override && typeof override === 'object') {
            view.segment = override.segment || '';
            view.period = override.period || view.period;
            view.date = override.date || view.date;
            rowSegment = $tr.prevAll('tr.parentComparisonRow').first().attr('data-segment-filter') || rowSegment;
        }
        var params = explorationParams(dimension, current.idSite, view);
        params.segment = view.segment ? view.segment + ';' + rowSegment : rowSegment;
        params.fwdeBreakdown = '1';
        openExploration(MatomoUrl, params, current, view);
    }

    var ROW_ACTION = 'FeinwerkDataExplorationRow';

    function ExploreRow(dataTable) {
        this.dataTable = dataTable;
        this.actionName = ROW_ACTION;
    }

    if (typeof DataTable_RowAction !== 'undefined' && typeof DataTable_RowActions_Registry !== 'undefined') {
        ExploreRow.prototype = new DataTable_RowAction();

        ExploreRow.prototype.onClick = function (actionElement, tr) {
            exploreRow(tr);
            return false;
        };

        DataTable_RowActions_Registry.register({
            name: ROW_ACTION,
            dataTableIcon: 'icon-lab',
            order: 55,
            dataTableIconTooltip: [
                _pk_translate('FeinwerkDataExploration_ExploreRow'),
                _pk_translate('FeinwerkDataExploration_ExploreRowHelp')
            ],
            isAvailableOnReport: function () {
                return true;
            },
            isAvailableOnRow: function (dataTableParams, tr) {
                return !!$(tr).attr('data-segment-filter') && !!reportDimension(tr);
            },
            createInstance: function (dataTable) {
                return new ExploreRow(dataTable);
            }
        });
    }
}(jQuery));
