import Chart from 'chart.js/auto'
import 'chartjs-adapter-date-fns'

const STORAGE_KEY = 'syrreporders_orders_prefs'

type SeriesKey = 'count' | 'totals' | 'shipping' | 'discount' | 'tax' | 'avg_ticket_price'
type DataKey   = 'count' | 'sales'  | 'shipping' | 'discount' | 'tax' | 'avg_ticket_price'

const SERIES: SeriesKey[]  = ['count', 'totals', 'shipping', 'discount', 'tax', 'avg_ticket_price']
const DATA_KEYS: DataKey[] = ['count', 'sales',  'shipping', 'discount', 'tax', 'avg_ticket_price']

interface ColorDef {
  border: string
  bg: string
  axis: 'y1' | 'y2'
}

const COLORS: Record<SeriesKey, ColorDef> = {
  count:            { border: '#3b7dc0', bg: 'rgba(59,125,192,0.1)',  axis: 'y2' },
  totals:           { border: '#129d0e', bg: 'rgba(18,157,14,0.1)',   axis: 'y1' },
  shipping:         { border: '#a38717', bg: 'rgba(163,135,23,0.1)',  axis: 'y1' },
  discount:         { border: '#ac3562', bg: 'rgba(172,53,98,0.1)',   axis: 'y1' },
  tax:              { border: '#1ba17a', bg: 'rgba(27,161,122,0.1)',  axis: 'y1' },
  avg_ticket_price: { border: '#87469f', bg: 'rgba(135,70,159,0.1)', axis: 'y1' },
}

interface OrdersPrefs {
  graph?: Record<string, number>
  states?: string[]
}

interface InitialTimeframe {
  timeframe: string
  groupby: string
  from?: string
  to?: string
}

interface OrdersConfig {
  currency: string
  groupBy: 'days' | 'months'
  initialTimeframe: InitialTimeframe
  labels: Record<SeriesKey | 'title', string>
}

interface TableRow {
  date: string
  count: string | number
  total: string | number
  shipping: string | number
  discount: string | number
  tax: string | number
}

interface ChartData {
  count?: [string, number][]
  sales?: [string, number][]
  shipping?: [string, number][]
  discount?: [string, number][]
  tax?: [string, number][]
  avg_ticket_price?: [string, number][]
  table?: TableRow[]
}

interface AjaxResponse {
  status: 'ok' | 'fail'
  data: ChartData
}

function loadPrefs(): OrdersPrefs {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? 'null') ?? {} }
  catch { return {} }
}

function savePrefs(prefs: OrdersPrefs): void {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs)) }
  catch { /* storage unavailable */ }
}

class SyrrepordersOrdersChart {
  private readonly currency: string
  private readonly groupBy: 'days' | 'months'
  private readonly labels: Record<SeriesKey | 'title', string>
  private readonly chart: Chart
  private readonly initialTimeframe: InitialTimeframe

  constructor(config: OrdersConfig) {
    this.currency         = config.currency
    this.groupBy          = config.groupBy
    this.labels           = config.labels
    this.initialTimeframe = config.initialTimeframe

    const prefs = loadPrefs()
    const graph = prefs.graph ?? {}
    const hidden: Partial<Record<SeriesKey, boolean>> = {}
    SERIES.forEach(key => {
      hidden[key] = graph[key] !== undefined ? !graph[key] : false
    })

    this.chart = this.createChart(hidden)
    this.restoreCheckboxes(prefs)
    this.applyColumnVisibility()
    this.bindEvents()
    $(document).ready(() => this.refresh())
  }

  private fmt(value: number): string {
    try {
      return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: this.currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value)
    } catch {
      return value.toFixed(2)
    }
  }

  private toPoints(arr: [string, number][] | undefined): { x: string; y: number }[] {
    if (!Array.isArray(arr)) return []
    return arr.map(p => ({ x: p[0], y: p[1] }))
  }

  private makeDataset(key: SeriesKey, hidden: boolean) {
    const c = COLORS[key]
    return {
      label:           this.labels[key],
      data:            [] as { x: string; y: number }[],
      borderColor:     c.border,
      backgroundColor: c.bg,
      yAxisID:         c.axis,
      borderWidth:     3,
      pointRadius:     2,
      fill:            true,
      hidden,
    }
  }

  private createChart(hidden: Partial<Record<SeriesKey, boolean>>): Chart {
    const datasets = SERIES.map(key => this.makeDataset(key, !!hidden[key]))

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return new Chart(document.getElementById('syr-orders-chart') as HTMLCanvasElement, {
      type: 'line',
      data: { datasets },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          title:  { display: true, text: this.labels.title },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const ds = ctx.dataset as { yAxisID?: string; label?: string }
                const v  = ds.yAxisID === 'y2'
                  ? Math.round(ctx.parsed.y)
                  : this.fmt(ctx.parsed.y)
                return `${ds.label ?? ''}: ${v}`
              },
            },
          },
        },
        scales: {
          x: {
            type: 'time',
            time: {
              unit:           this.groupBy === 'days' ? 'day' : 'month',
              displayFormats: { day: 'dd MMM', month: 'MMM yyyy' },
            },
            grid:  { color: '#eeeeee' },
            ticks: { maxTicksLimit: 35 },
          },
          y1: {
            type:     'linear',
            position: 'left',
            min:      0,
            grid:     { color: '#eeeeee' },
            ticks:    { callback: (v) => this.fmt(Number(v)) },
          },
          y2: {
            type:     'linear',
            position: 'right',
            min:      0,
            grid:     { drawOnChartArea: false },
            ticks:    { callback: (v) => Math.round(Number(v)) },
          },
        },
      },
    } as any) // Chart.js scale options use complex discriminated unions
  }

  private applyVisibility(): void {
    const chart = this.chart
    $('[data-serie]').each(function () {
      const idx = parseInt($(this).data('serie'))
      if (!isNaN(idx)) chart.setDatasetVisibility(idx, $(this).is(':checked'))
    })
  }

  private applyColumnVisibility(): void {
    $('[data-column]').each(function () {
      $('.col-' + String($(this).data('column'))).toggle($(this).is(':checked'))
    })
  }

  private restoreCheckboxes(prefs: OrdersPrefs): void {
    const graph  = prefs.graph  ?? {}
    const states = prefs.states ?? null

    SERIES.forEach((key, i) => {
      if (graph[key] !== undefined) {
        $(`[data-serie="${i}"]`).prop('checked', !!graph[key])
      }
    })

    if (states) {
      $('[name="orders_state[]"]').each(function () {
        $(this).prop('checked', states.includes(String($(this).val())))
      })
    }
  }

  private collectPrefs(): OrdersPrefs {
    const graph: Record<string, number> = {}
    SERIES.forEach((key, i) => {
      graph[key] = $(`[data-serie="${i}"]`).is(':checked') ? 1 : 0
    })
    const states: string[] = []
    $('[name="orders_state[]"]:checked').each(function () {
      states.push(String($(this).val()))
    })
    return { graph, states }
  }

  private formatDate(dateStr: string): string {
    const p = (dateStr ?? '').split('-')
    if (p.length < 3) return dateStr
    return this.groupBy === 'months'
      ? `${p[1]}.${p[0]}`
      : `${p[2]}.${p[1]}.${p[0]}`
  }

  private updateTable(rows: TableRow[] | undefined): void {
    const $tbody = $('#syrRepOrdersTableValues tbody')
    $tbody.empty()

    for (const row of rows ?? []) {
      const count = parseFloat(String(row.count)) || 0
      if (count <= 0) continue
      const total = parseFloat(String(row.total))    || 0
      const ship  = parseFloat(String(row.shipping)) || 0
      const disc  = parseFloat(String(row.discount)) || 0
      const tax   = parseFloat(String(row.tax))      || 0
      const avg   = total / count

      $tbody.append(
        `<tr>`
        + `<td>${this.formatDate(row.date)}</td>`
        + `<td class="col-count">${count}</td>`
        + `<td class="col-totals">${this.fmt(total)}</td>`
        + `<td class="col-shipping">${this.fmt(ship)}</td>`
        + `<td class="col-discount">${this.fmt(disc)}</td>`
        + `<td class="col-tax">${this.fmt(tax)}</td>`
        + `<td class="col-avg_ticket_price">${this.fmt(avg)}</td>`
        + `</tr>`,
      )
    }

    this.applyColumnVisibility()
  }

  private updateChart(data: ChartData): void {
    DATA_KEYS.forEach((key, i) => {
      this.chart.data.datasets[i].data = this.toPoints(data[key as keyof ChartData] as [string, number][])
    })
    this.applyVisibility()
    this.chart.update()
    this.updateTable(data.table)
  }

  private buildTimeframeData(): string {
    const $active = $('.js-reports-timeframe-dropdown li.selected, .js-reports-timeframe-dropdown li.active').first()
    if ($active.length) {
      const timeframe = String($active.data('timeframe') || '30')
      const groupby   = String($active.data('groupby')   || 'days')
      const p: Record<string, string> = { timeframe, groupby }
      if (timeframe === 'custom') {
        const from = String($('.js-custom-timeframe [name="from"]').val() || '')
        const to   = String($('.js-custom-timeframe [name="to"]').val()   || '')
        if (from) p.from = from
        if (to)   p.to   = to
      }
      return $.param(p)
    }
    // Legacy UI: use server-provided initial timeframe
    const tf = this.initialTimeframe
    const p: Record<string, string> = { timeframe: tf.timeframe, groupby: tf.groupby }
    if (tf.from) p.from = tf.from
    if (tf.to)   p.to   = tf.to
    return $.param(p)
  }

  refresh(): JQuery.jqXHR<AjaxResponse> {
    const $btn = $('#s-plugin-syrorders-refresh-btn').prop('disabled', true)
    $btn.find('.js-syrorders-refresh-spinner').show()

    return $.post(
      '?plugin=syrreporders&action=reportdata',
      $('#syrRepOrdersSettingsForm').serialize() + '&' + this.buildTimeframeData(),
    )
      .done((resp: AjaxResponse) => {
        if (resp?.status === 'ok') this.updateChart(resp.data)
      })
      .always(() => {
        $btn.prop('disabled', false)
        $btn.find('.js-syrorders-refresh-spinner').hide()
      })
  }

  private bindEvents(): void {
    $('#s-plugin-syrorders-refresh-btn').on('click', () => this.refresh())

    $('[data-serie]').on('change', (e) => {
      const idx = parseInt($(e.currentTarget).data('serie'))
      if (!isNaN(idx)) {
        this.chart.setDatasetVisibility(idx, $(e.currentTarget).is(':checked'))
        this.chart.update()
      }
      this.applyColumnVisibility()
      savePrefs(this.collectPrefs())
    })

    $('[name="orders_state[]"]').on('change', () => {
      savePrefs(this.collectPrefs())
    })
  }
}

declare global {
  interface Window {
    SyrrepordersOrdersChart: typeof SyrrepordersOrdersChart
  }
}

window.SyrrepordersOrdersChart = SyrrepordersOrdersChart
