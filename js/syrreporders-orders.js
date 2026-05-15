/* global Chart */
(function ($) {
    'use strict';

    var CURRENCY_FORMATS = {
        RUB: function (v) { return v.toFixed(2) + ' руб.'; },
        USD: function (v) { return '$' + v.toFixed(2); },
        EUR: function (v) { return '€' + v.toFixed(2); },
        UAH: function (v) { return v.toFixed(2) + ' грн.'; }
    };

    var SERIES = ['count', 'totals', 'shipping', 'discount', 'tax', 'avg_ticket_price'];

    var DATA_KEYS = ['count', 'sales', 'shipping', 'discount', 'tax', 'avg_ticket_price'];

    var COLORS = {
        count:           { border: '#3b7dc0', bg: 'rgba(59,125,192,0.1)',  axis: 'y2' },
        totals:          { border: '#129d0e', bg: 'rgba(18,157,14,0.1)',   axis: 'y1' },
        shipping:        { border: '#a38717', bg: 'rgba(163,135,23,0.1)',  axis: 'y1' },
        discount:        { border: '#ac3562', bg: 'rgba(172,53,98,0.1)',   axis: 'y1' },
        tax:             { border: '#1ba17a', bg: 'rgba(27,161,122,0.1)',  axis: 'y1' },
        avg_ticket_price:{ border: '#87469f', bg: 'rgba(135,70,159,0.1)', axis: 'y1' }
    };

    function SyrrepordersOrdersChart(config) {
        this.currency = config.currency;
        this.groupBy  = config.groupBy;
        this._labels  = config.labels;
        this._chart   = this._createChart(config);
        this._bindEvents();
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

        _makeDataset: function (key, data, hidden) {
            var c = COLORS[key];
            return {
                label:           this._labels[key],
                data:            this._toPoints(data),
                borderColor:     c.border,
                backgroundColor: c.bg,
                yAxisID:         c.axis,
                borderWidth:     3,
                pointRadius:     2,
                fill:            true,
                hidden:          !!hidden
            };
        },

        _createChart: function (config) {
            var self    = this;
            var data    = config.data    || {};
            var hidden  = config.hidden  || {};
            var datasets = SERIES.map(function (key, i) {
                return self._makeDataset(key, data[DATA_KEYS[i]], hidden[key]);
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
                var col = $(this).data('column');
                var show = $(this).is(':checked');
                $('.col-' + col).toggle(show);
            });
        },

        _updateChart: function (data) {
            var self = this;
            DATA_KEYS.forEach(function (key, i) {
                self._chart.data.datasets[i].data = self._toPoints(data[key]);
            });
            self._applyVisibility();
            self._chart.update();
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
            });
        }
    };

    window.SyrrepordersOrdersChart = SyrrepordersOrdersChart;

}(jQuery));
