/* global Chart */
(function ($) {
    'use strict';

    var CURRENCY_FORMATS = {
        RUB: function (v) { return v.toFixed(2) + ' руб.'; },
        USD: function (v) { return '$' + v.toFixed(2); },
        EUR: function (v) { return '€' + v.toFixed(2); },
        UAH: function (v) { return v.toFixed(2) + ' грн.'; }
    };

    function SyrrepordersWeekdaysChart(config) {
        this.currency      = config.currency;
        this._weekdayNames = config.weekdayNames;
        this._labels       = config.labels;
        this._chart        = this._createChart(config);
        this._bindEvents();
    }

    SyrrepordersWeekdaysChart.prototype = {

        _fmt: function (value) {
            var fn = CURRENCY_FORMATS[this.currency];
            return fn ? fn(value) : value.toFixed(2);
        },

        _prepare: function (rawData) {
            var self   = this;
            var sorted = (rawData || []).slice().sort(function (a, b) {
                return parseInt(a.dow) - parseInt(b.dow);
            });
            return {
                labels: sorted.map(function (d) { return self._weekdayNames[parseInt(d.dow)]; }),
                totals: sorted.map(function (d) { return parseFloat(d.total); }),
                counts: sorted.map(function (d) { return parseFloat(d.count); })
            };
        },

        _createChart: function (config) {
            var self    = this;
            var prepared = self._prepare(config.data);

            return new Chart(document.getElementById('syr-weekdays-chart'), {
                type: 'bar',
                data: {
                    labels: prepared.labels,
                    datasets: [
                        {
                            label:           self._labels.totals,
                            data:            prepared.totals,
                            backgroundColor: 'rgba(18,157,14,0.7)',
                            borderColor:     '#129d0e',
                            borderWidth:     1,
                            yAxisID:         'y1'
                        },
                        {
                            label:           self._labels.count,
                            data:            prepared.counts,
                            backgroundColor: 'rgba(59,125,192,0.7)',
                            borderColor:     '#3b7dc0',
                            borderWidth:     1,
                            yAxisID:         'y2'
                        }
                    ]
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    animation:           { duration: 500 },
                    plugins: {
                        title: { display: true, text: self._labels.title },
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
                        x:  { grid: { color: '#eeeeee' } },
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

        _updateChart: function (rawData) {
            var prepared = this._prepare(rawData);
            this._chart.data.labels              = prepared.labels;
            this._chart.data.datasets[0].data    = prepared.totals;
            this._chart.data.datasets[1].data    = prepared.counts;
            this._chart.update();
        },

        refresh: function () {
            var self = this;
            var $btn = $('#s-plugin-syrorders-refresh-btn').prop('disabled', true);

            return $.post('?plugin=syrreporders&action=reportweekdaysdata', $('#syrRepOrdersSettingsForm').serialize())
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
        }
    };

    window.SyrrepordersWeekdaysChart = SyrrepordersWeekdaysChart;

}(jQuery));
