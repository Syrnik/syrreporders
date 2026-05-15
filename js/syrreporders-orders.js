/* global Chart */
(function ($) {
    'use strict';

    var STORAGE_KEY = 'syrreporders_orders_prefs';

    var CURRENCY_FORMATS = {
        RUB: function (v) { return v.toFixed(2) + ' руб.'; },
        USD: function (v) { return '$' + v.toFixed(2); },
        EUR: function (v) { return '€' + v.toFixed(2); },
        UAH: function (v) { return v.toFixed(2) + ' грн.'; }
    };

    var SERIES    = ['count', 'totals', 'shipping', 'discount', 'tax', 'avg_ticket_price'];
    var DATA_KEYS = ['count', 'sales',  'shipping', 'discount', 'tax', 'avg_ticket_price'];

    var COLORS = {
        count:           { border: '#3b7dc0', bg: 'rgba(59,125,192,0.1)',  axis: 'y2' },
        totals:          { border: '#129d0e', bg: 'rgba(18,157,14,0.1)',   axis: 'y1' },
        shipping:        { border: '#a38717', bg: 'rgba(163,135,23,0.1)',  axis: 'y1' },
        discount:        { border: '#ac3562', bg: 'rgba(172,53,98,0.1)',   axis: 'y1' },
        tax:             { border: '#1ba17a', bg: 'rgba(27,161,122,0.1)',  axis: 'y1' },
        avg_ticket_price:{ border: '#87469f', bg: 'rgba(135,70,159,0.1)', axis: 'y1' }
    };

    function loadPrefs() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; }
        catch (e) { return {}; }
    }

    function savePrefs(prefs) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs)); }
        catch (e) {}
    }

    function SyrrepordersOrdersChart(config) {
        this.currency = config.currency;
        this.groupBy  = config.groupBy;
        this._labels  = config.labels;

        var prefs = loadPrefs();
        var graph = prefs.graph || {};

        var hidden = {};
        SERIES.forEach(function (key) {
            hidden[key] = graph[key] !== undefined ? !graph[key] : false;
        });

        this._chart = this._createChart(hidden);
        this._restoreCheckboxes(prefs);
        this._applyColumnVisibility();
        this._bindEvents();
        this.refresh();
    }

    SyrrepordersOrdersChart.prototype = {

        _fmt: function (value) {
            var fn = CURRENCY_FORMATS[this.currency];
            return fn ? fn(value) : value.toFixed(2);
        },

        _toPoints: function (arr) {
            if (!Array.isArray(arr)) { return []; }
            return arr.map(function (p) { return { x: p[0], y: p[1] }; });
        },

        _makeDataset: function (key, hidden) {
            var c = COLORS[key];
            return {
                label:           this._labels[key],
                data:            [],
                borderColor:     c.border,
                backgroundColor: c.bg,
                yAxisID:         c.axis,
                borderWidth:     3,
                pointRadius:     2,
                fill:            true,
                hidden:          !!hidden
            };
        },

        _createChart: function (hidden) {
            var self     = this;
            var datasets = SERIES.map(function (key) {
                return self._makeDataset(key, hidden[key]);
            });

            return new Chart(document.getElementById('syr-orders-chart'), {
                type: 'line',
                data: { datasets: datasets },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        title:  { display: true, text: self._labels.title },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    var v = ctx.dataset.yAxisID === 'y2'
                                        ? Math.round(ctx.parsed.y)
                                        : self._fmt(ctx.parsed.y);
                                    return ctx.dataset.label + ': ' + v;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: self.groupBy === 'days' ? 'day' : 'month',
                                displayFormats: { day: 'dd MMM', month: 'MMM yyyy' }
                            },
                            grid:  { color: '#eeeeee' },
                            ticks: { maxTicksLimit: 35 }
                        },
                        y1: {
                            type:     'linear',
                            position: 'left',
                            min:      0,
                            grid:     { color: '#eeeeee' },
                            ticks:    { callback: function (v) { return self._fmt(v); } }
                        },
                        y2: {
                            type:     'linear',
                            position: 'right',
                            min:      0,
                            grid:     { drawOnChartArea: false },
                            ticks:    { callback: function (v) { return Math.round(v); } }
                        }
                    }
                }
            });
        },

        _applyVisibility: function () {
            var chart = this._chart;
            $('[data-serie]').each(function () {
                var idx = parseInt($(this).data('serie'));
                if (!isNaN(idx)) {
                    chart.setDatasetVisibility(idx, $(this).is(':checked'));
                }
            });
        },

        _applyColumnVisibility: function () {
            $('[data-column]').each(function () {
                var col  = $(this).data('column');
                var show = $(this).is(':checked');
                $('.col-' + col).toggle(show);
            });
        },

        _restoreCheckboxes: function (prefs) {
            var graph  = prefs.graph  || {};
            var states = prefs.states || null;

            SERIES.forEach(function (key, i) {
                if (graph[key] !== undefined) {
                    $('[data-serie="' + i + '"]').prop('checked', !!graph[key]);
                }
            });

            if (states) {
                $('[name="orders_state[]"]').each(function () {
                    $(this).prop('checked', states.indexOf($(this).val()) !== -1);
                });
            }
        },

        _collectPrefs: function () {
            var graph = {};
            SERIES.forEach(function (key, i) {
                graph[key] = $('[data-serie="' + i + '"]').is(':checked') ? 1 : 0;
            });
            var states = [];
            $('[name="orders_state[]"]:checked').each(function () {
                states.push($(this).val());
            });
            return { graph: graph, states: states };
        },

        _formatDate: function (dateStr) {
            var p = (dateStr || '').split('-');
            if (p.length < 3) { return dateStr; }
            return this.groupBy === 'months'
                ? p[1] + '.' + p[0]
                : p[2] + '.' + p[1] + '.' + p[0];
        },

        _updateTable: function (rows) {
            var self   = this;
            var $tbody = $('#syrRepOrdersTableValues tbody');
            $tbody.empty();

            (rows || []).forEach(function (row) {
                var count = parseFloat(row.count) || 0;
                if (count <= 0) { return; }
                var total = parseFloat(row.total)    || 0;
                var ship  = parseFloat(row.shipping) || 0;
                var disc  = parseFloat(row.discount) || 0;
                var tax   = parseFloat(row.tax)      || 0;
                var avg   = count > 0 ? total / count : 0;

                $tbody.append(
                    '<tr>'
                    + '<td>' + self._formatDate(row.date) + '</td>'
                    + '<td class="col-count">'            + count            + '</td>'
                    + '<td class="col-totals">'           + self._fmt(total) + '</td>'
                    + '<td class="col-shipping">'         + self._fmt(ship)  + '</td>'
                    + '<td class="col-discount">'         + self._fmt(disc)  + '</td>'
                    + '<td class="col-tax">'              + self._fmt(tax)   + '</td>'
                    + '<td class="col-avg_ticket_price">' + self._fmt(avg)   + '</td>'
                    + '</tr>'
                );
            });

            self._applyColumnVisibility();
        },

        _updateChart: function (data) {
            var self = this;
            DATA_KEYS.forEach(function (key, i) {
                self._chart.data.datasets[i].data = self._toPoints(data[key]);
            });
            self._applyVisibility();
            self._chart.update();
            self._updateTable(data.table);
        },

        refresh: function () {
            var self = this;
            var $btn = $('#s-plugin-syrorders-refresh-btn').prop('disabled', true);

            return $.post('?plugin=syrreporders&action=reportdata', $('#syrRepOrdersSettingsForm').serialize())
                .done(function (resp) {
                    if (resp && resp.status === 'ok') {
                        self._updateChart(resp.data);
                    }
                })
                .always(function () {
                    $btn.prop('disabled', false);
                });
        },

        _bindEvents: function () {
            var self = this;

            $('#s-plugin-syrorders-refresh-btn').on('click', function () {
                self.refresh();
            });

            $('[data-serie]').on('change', function () {
                var idx = parseInt($(this).data('serie'));
                if (!isNaN(idx)) {
                    self._chart.setDatasetVisibility(idx, $(this).is(':checked'));
                    self._chart.update();
                }
                self._applyColumnVisibility();
                savePrefs(self._collectPrefs());
            });

            $('[name="orders_state[]"]').on('change', function () {
                savePrefs(self._collectPrefs());
            });
        }
    };

    window.SyrrepordersOrdersChart = SyrrepordersOrdersChart;

}(jQuery));
