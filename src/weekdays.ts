import Chart from 'chart.js/auto'
import 'chartjs-adapter-date-fns'

const STORAGE_KEY = 'syrreporders_weekdays_prefs'

interface WeekdaysPrefs {
  states?: string[]
}

interface WeekdaysConfig {
  currency: string
  weekdayNames: string[]
  labels: {
    title: string
    totals: string
    count: string
  }
}

interface DowRow {
  dow: string | number
  total: string | number
  count: string | number
}

interface TopData {
  count?: DowRow[]
  sum?: DowRow[]
}

interface WeekdaysResponse {
  dow: DowRow[]
  top: TopData
}

interface AjaxResponse {
  status: 'ok' | 'fail'
  data: WeekdaysResponse
}

interface PreparedData {
  labels: string[]
  totals: number[]
  counts: number[]
}

function loadPrefs(): WeekdaysPrefs {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? 'null') ?? {} }
  catch { return {} }
}

function savePrefs(prefs: WeekdaysPrefs): void {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs)) }
  catch { /* storage unavailable */ }
}

class SyrrepordersWeekdaysChart {
  private readonly currency: string
  private readonly weekdayNames: string[]
  private readonly labels: WeekdaysConfig['labels']
  private readonly chart: Chart

  constructor(config: WeekdaysConfig) {
    this.currency     = config.currency
    this.weekdayNames = config.weekdayNames
    this.labels       = config.labels
    this.chart        = this.createChart()

    const prefs = loadPrefs()
    this.restoreCheckboxes(prefs.states ?? null)
    this.bindEvents()
    this.refresh()
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

  private prepare(rawData: DowRow[]): PreparedData {
    const sorted = (rawData ?? []).slice().sort(
      (a, b) => parseInt(String(a.dow)) - parseInt(String(b.dow)),
    )
    return {
      labels: sorted.map(d => this.weekdayNames[parseInt(String(d.dow))]),
      totals: sorted.map(d => parseFloat(String(d.total))),
      counts: sorted.map(d => parseFloat(String(d.count))),
    }
  }

  private createChart(): Chart {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return new Chart(document.getElementById('syr-weekdays-chart') as HTMLCanvasElement, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [
          {
            label:           this.labels.totals,
            data:            [],
            backgroundColor: 'rgba(18,157,14,0.7)',
            borderColor:     '#129d0e',
            borderWidth:     1,
            yAxisID:         'y1',
          },
          {
            label:           this.labels.count,
            data:            [],
            backgroundColor: 'rgba(59,125,192,0.7)',
            borderColor:     '#3b7dc0',
            borderWidth:     1,
            yAxisID:         'y2',
          },
        ],
      },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        animation:           { duration: 500 },
        plugins: {
          title: { display: true, text: this.labels.title },
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
          x:  { grid: { color: '#eeeeee' } },
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

  private updateChart(rawData: DowRow[]): void {
    const prepared = this.prepare(rawData)
    this.chart.data.labels           = prepared.labels
    this.chart.data.datasets[0].data = prepared.totals
    this.chart.data.datasets[1].data = prepared.counts
    this.chart.update()
  }

  private updateTop(top: TopData): void {
    const names = (arr: DowRow[] | undefined): string =>
      (arr ?? []).map(d => this.weekdayNames[parseInt(String(d.dow))]).join(', ') || '—'

    $('#syr-weekdays-top-count').text(names(top?.count))
    $('#syr-weekdays-top-sum').text(names(top?.sum))
  }

  private updateTable(rawData: DowRow[]): void {
    const prepared = this.prepare(rawData)
    const $tbody   = $('#syrRepWeekdaysTableBody')
    $tbody.empty()

    prepared.labels.forEach((label, i) => {
      $tbody.append(
        `<tr>`
        + `<td>${label}</td>`
        + `<td>${Math.round(prepared.counts[i])}</td>`
        + `<td>${this.fmt(prepared.totals[i])}</td>`
        + `</tr>`,
      )
    })
  }

  private restoreCheckboxes(states: string[] | null): void {
    if (!states) return
    $('[name="weekdays_state[]"]').each(function () {
      $(this).prop('checked', states.includes(String($(this).val())))
    })
  }

  private collectPrefs(): WeekdaysPrefs {
    const states: string[] = []
    $('[name="weekdays_state[]"]:checked').each(function () {
      states.push(String($(this).val()))
    })
    return { states }
  }

  refresh(): JQuery.jqXHR<AjaxResponse> {
    const $btn = $('#s-plugin-syrorders-refresh-btn').prop('disabled', true)

    return $.post('?plugin=syrreporders&action=reportweekdaysdata', $('#syrRepOrdersSettingsForm').serialize())
      .done((resp: AjaxResponse) => {
        if (resp?.status === 'ok') {
          this.updateChart(resp.data.dow)
          this.updateTop(resp.data.top)
          this.updateTable(resp.data.dow)
        }
      })
      .always(() => { $btn.prop('disabled', false) })
  }

  private bindEvents(): void {
    $('#s-plugin-syrorders-refresh-btn').on('click', () => this.refresh())

    $('[name="weekdays_state[]"]').on('change', () => {
      savePrefs(this.collectPrefs())
    })
  }
}

declare global {
  interface Window {
    SyrrepordersWeekdaysChart: typeof SyrrepordersWeekdaysChart
  }
}

window.SyrrepordersWeekdaysChart = SyrrepordersWeekdaysChart
